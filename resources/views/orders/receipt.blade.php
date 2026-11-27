<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Struk {{ $order->invoice_number }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            @page { size: 80mm auto; margin: 0; }
            @media print {
                body { background: #fff !important; }
                .no-print { display: none !important; }
                .receipt { border: 0 !important; margin: 0 !important; }
            }
        </style>
    </head>
    @php
        $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');
    @endphp
    <body class="min-h-screen bg-[#F7F5F0] py-6 text-[#1C1917] antialiased">
        <main>
            <div class="receipt mx-auto w-[80mm] border border-[#E2DDD5] bg-white px-4 py-4 text-xs text-[#1C1917]">
                <div class="text-center">
                    <h1 class="text-base font-semibold">{{ $setting->cafe_name }}</h1>
                    @if ($setting->address)<p class="mt-0.5 text-[#57534E]">{{ $setting->address }}</p>@endif
                    @if ($setting->phone)<p class="text-[#57534E]">Telp {{ $setting->phone }}</p>@endif
                </div>

                <div class="my-3 border-t border-dashed border-[#1C1917]"></div>

                <dl class="space-y-0.5">
                    <div class="flex justify-between"><dt>No. Invoice</dt><dd class="tabular-nums">{{ $order->invoice_number }}</dd></div>
                    <div class="flex justify-between"><dt>Tanggal</dt><dd class="tabular-nums">{{ $order->created_at->format('d/m/Y H:i') }}</dd></div>
                    <div class="flex justify-between"><dt>Kasir</dt><dd>{{ $order->user->name ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt>Pelanggan</dt><dd>{{ $order->customer_name }}</dd></div>
                    <div class="flex justify-between"><dt>Tipe</dt><dd>{{ $order->order_type === 'dine_in' ? 'Dine-in' : 'Takeaway' }}@if ($order->table_number) · Meja <span class="tabular-nums">{{ $order->table_number }}</span>@endif</dd></div>
                </dl>

                <div class="my-3 border-t border-dashed border-[#1C1917]"></div>

                <ul class="space-y-2">
                    @foreach ($order->orderItems as $item)
                        <li>
                            <p class="font-semibold">{{ $item->product->name ?? 'Produk dihapus' }}</p>
                            <div class="flex justify-between tabular-nums">
                                <span>{{ $item->quantity }} x {{ $rp($item->price) }}</span>
                                <span>{{ $rp($item->subtotal) }}</span>
                            </div>
                            @if ($item->note)<p class="text-[#57534E]">Catatan: {{ $item->note }}</p>@endif
                        </li>
                    @endforeach
                </ul>

                <div class="my-3 border-t border-dashed border-[#1C1917]"></div>

                <dl class="space-y-0.5 tabular-nums">
                    <div class="flex justify-between text-sm font-bold"><dt>Total Bayar</dt><dd>{{ $rp($order->total_price) }}</dd></div>
                    <div class="flex justify-between"><dt>Metode</dt><dd>{{ $order->payment_method === 'qris' ? 'QRIS' : 'Cash' }}</dd></div>
                    <div class="flex justify-between"><dt>Dibayar</dt><dd>{{ $rp($order->pay_amount) }}</dd></div>
                    <div class="flex justify-between font-semibold"><dt>Kembalian</dt><dd>{{ $rp($order->change_amount) }}</dd></div>
                </dl>

                <div class="my-3 border-t border-dashed border-[#1C1917]"></div>
                <p class="text-center text-[#57534E]">Terima kasih sudah berkunjung.</p>
            </div>

            <div class="no-print mx-auto mt-4 flex w-[80mm] gap-2">
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-md bg-[#9A3412] px-4 text-sm font-semibold text-white transition-colors hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                >
                    Cetak Struk
                </button>
                <a
                    href="{{ route('pos.index') }}"
                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-4 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                >
                    Kembali ke POS
                </a>
            </div>
        </main>
    </body>
</html>
