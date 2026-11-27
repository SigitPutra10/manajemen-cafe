<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderQueueController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->where('status', 'processing')
            ->with('orderItems.product:id,name')
            ->oldest()
            ->get();

        return view('orders.queue', [
            'cafeName' => Setting::query()->value('cafe_name') ?: config('app.name'),
            'orders' => $orders,
        ]);
    }

    public function complete(Order $order): RedirectResponse
    {
        if ($order->status === 'processing') {
            $order->update(['status' => 'completed']);
        }

        return redirect()
            ->route('orders.queue')
            ->with('status', 'Pesanan '.$order->invoice_number.' selesai dibuat.');
    }
}
