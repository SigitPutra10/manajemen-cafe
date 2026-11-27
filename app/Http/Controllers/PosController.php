<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        $products = Product::query()
            ->with('category:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name ?? '',
                'name' => $product->name,
                'price' => $product->price,
                'image_url' => $product->image ? asset('storage/'.$product->image) : null,
                'is_available' => $product->is_available,
            ])
            ->values();

        return view('pos.index', [
            'cafeName' => $this->setting()->cafe_name,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'order_type' => ['required', Rule::in(['dine_in', 'takeaway'])],
            'table_number' => ['nullable', 'required_if:order_type,dine_in', 'string', 'max:20'],
            'payment_method' => ['required', Rule::in(['cash', 'qris'])],
            'pay_amount' => ['nullable', 'integer', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.note' => ['nullable', 'string', 'max:100'],
        ], [
            'customer_name.required' => 'Nama pelanggan wajib diisi.',
            'table_number.required_if' => 'Nomor meja wajib diisi untuk Dine-in.',
            'items.required' => 'Keranjang masih kosong.',
            'items.min' => 'Keranjang masih kosong.',
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $products = Product::query()
                ->whereIn('id', collect($data['items'])->pluck('product_id'))
                ->get()
                ->keyBy('id');

            $lines = [];
            $total = 0;

            foreach ($data['items'] as $item) {
                $product = $products[$item['product_id']];

                if (! $product->is_available) {
                    abort(response()->json([
                        'message' => $product->name.' stok habis.',
                        'errors' => ['items' => [$product->name.' stok habis.']],
                    ], 422));
                }

                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $lines[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                    'note' => ($item['note'] ?? '') === '' ? null : $item['note'],
                ];
            }

            if ($data['payment_method'] === 'qris') {
                $pay = $total;
            } else {
                $pay = (int) ($data['pay_amount'] ?? 0);

                if ($pay < $total) {
                    abort(response()->json([
                        'message' => 'Nominal bayar kurang dari total.',
                        'errors' => ['pay_amount' => ['Nominal bayar kurang dari total.']],
                    ], 422));
                }
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'invoice_number' => $this->nextInvoiceNumber(),
                'customer_name' => $data['customer_name'],
                'table_number' => $data['order_type'] === 'dine_in' ? $data['table_number'] : null,
                'order_type' => $data['order_type'],
                'total_price' => $total,
                'pay_amount' => $pay,
                'change_amount' => $pay - $total,
                'payment_method' => $data['payment_method'],
                'status' => 'processing',
            ]);

            $order->orderItems()->createMany($lines);

            return $order;
        });

        return response()->json([
            'redirect' => route('orders.receipt', $order),
        ]);
    }

    public function receipt(Order $order): View
    {
        $order->load('orderItems.product');

        return view('orders.receipt', [
            'order' => $order,
            'setting' => $this->setting(),
        ]);
    }

    private function nextInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ymd').'-';

        $last = Order::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function setting(): Setting
    {
        return Setting::query()->first() ?? new Setting([
            'cafe_name' => config('app.name'),
        ]);
    }
}
