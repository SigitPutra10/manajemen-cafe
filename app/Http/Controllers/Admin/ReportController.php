<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function dashboard(): View
    {
        return app(DashboardController::class)->index();
    }

    public function index(Request $request): View
    {
        [$start, $end] = $this->range($request);
        $query = $this->filtered($start, $end);

        $orders = (clone $query)
            ->with('orderItems.product:id,name')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $detail = $orders->getCollection()->mapWithKeys(fn (Order $o) => [
            $o->id => [
                'invoice' => $o->invoice_number,
                'customer' => $o->customer_name,
                'type' => $o->order_type === 'dine_in' ? 'Dine-in' : 'Takeaway',
                'table' => $o->table_number,
                'method' => strtoupper($o->payment_method),
                'date' => $o->created_at->format('d/m/Y H:i'),
                'total' => $o->total_price,
                'pay' => $o->pay_amount,
                'change' => $o->change_amount,
                'items' => $o->orderItems->map(fn (OrderItem $i) => [
                    'name' => $i->product?->name ?? 'Menu dihapus',
                    'qty' => $i->quantity,
                    'price' => $i->price,
                    'subtotal' => $i->subtotal,
                    'note' => $i->note,
                ])->values(),
            ],
        ]);

        return view('admin.reports.index', [
            'orders' => $orders,
            'detail' => $detail,
            'startDate' => $start,
            'endDate' => $end,
            'revenue' => (int) (clone $query)->sum('total_price'),
            'transactionCount' => (clone $query)->count(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$start, $end] = $this->range($request);
        $query = $this->filtered($start, $end)->with('orderItems.product:id,name')->latest();

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Invoice', 'Tanggal', 'Pelanggan', 'Tipe', 'Meja', 'Pembayaran', 'Status', 'Menu', 'Total'], ';');

            $query->chunk(200, function ($chunk) use ($out) {
                foreach ($chunk as $o) {
                    fputcsv($out, [
                        $o->invoice_number,
                        $o->created_at->format('Y-m-d H:i'),
                        $o->customer_name,
                        $o->order_type === 'dine_in' ? 'Dine-in' : 'Takeaway',
                        $o->table_number,
                        strtoupper($o->payment_method),
                        $o->status === 'completed' ? 'Selesai' : 'Diproses',
                        $o->orderItems->map(fn ($i) => ($i->product?->name ?? 'Menu dihapus').' x'.$i->quantity)->implode(', '),
                        $o->total_price,
                    ], ';');
                }
            });

            fclose($out);
        }, 'laporan-transaksi-'.$start.'-sd-'.$end.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function destroy(Order $order): RedirectResponse
    {
        $invoice = $order->invoice_number;

        DB::transaction(function () use ($order) {
            $order->orderItems()->delete();
            $order->delete();
        });

        return redirect()
            ->route('admin.reports.index', request()->only(['start_date', 'end_date']))
            ->with('status', "Transaksi {$invoice} berhasil dihapus.");
    }

    /** @return array{0: string, 1: string} */
    private function range(Request $request): array
    {
        $data = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        return [
            $data['start_date'] ?? now()->startOfMonth()->toDateString(),
            $data['end_date'] ?? now()->toDateString(),
        ];
    }

    private function filtered(string $start, string $end): Builder
    {
        return Order::query()
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);
    }
}
