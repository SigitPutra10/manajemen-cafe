<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Antrean Pesanan - {{ $cafeName }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }</style>
    </head>
    <body class="bg-[#F7F5F0] text-[#1C1917] antialiased">
        <header class="flex items-center justify-between gap-3 border-b border-[#E2DDD5] bg-white px-4 py-3">
            <h1 class="truncate text-lg font-semibold">Antrean Pesanan <span class="font-normal text-[#57534E]">· {{ $cafeName }}</span></h1>
            <nav class="flex items-center gap-2">
                <a href="{{ route('pos.index') }}" class="inline-flex min-h-[44px] items-center rounded-md border border-[#E2DDD5] bg-white px-4 text-sm font-semibold hover:bg-[#EFECE6]">Kasir</a>
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-[44px] items-center rounded-md border border-[#E2DDD5] bg-white px-4 text-sm font-semibold hover:bg-[#EFECE6]">Dashboard Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex min-h-[44px] items-center rounded-md border border-[#E2DDD5] bg-white px-4 text-sm font-semibold hover:bg-[#EFECE6]">Keluar</button>
                </form>
            </nav>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6">
            @if (session('status'))
                <p class="mb-4 rounded-md border border-[#E2DDD5] bg-[#DCFCE7] px-4 py-3 text-sm font-semibold text-[#15803D]" role="status">{{ session('status') }}</p>
            @endif

            <p class="mb-4 text-sm text-[#57534E]"><span class="font-semibold tabular-nums text-[#1C1917]">{{ $orders->count() }}</span> pesanan sedang diproses</p>

            @forelse ($orders as $order)
                @if ($loop->first)
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @endif
                <article class="flex flex-col rounded-lg border border-[#E2DDD5] bg-white" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
                    <div class="flex items-start justify-between gap-3 border-b border-[#E2DDD5] p-4">
                        <div class="min-w-0">
                            <p class="truncate text-base font-semibold tabular-nums">{{ $order->invoice_number }}</p>
                            <p class="truncate text-sm text-[#57534E]">{{ $order->customer_name }}</p>
                        </div>
                        <span class="shrink-0 rounded-md bg-[#FEF3C7] px-2 py-1 text-xs font-semibold text-[#B45309]">Diproses</span>
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 border-b border-[#E2DDD5] px-4 py-2 text-sm">
                        <span class="font-semibold">{{ $order->order_type === 'dine_in' ? 'Dine-in' : 'Takeaway' }}</span>
                        @if ($order->table_number)
                            <span>Meja <span class="tabular-nums">{{ $order->table_number }}</span></span>
                        @endif
                        <span class="tabular-nums text-[#57534E]">{{ $order->created_at->format('H:i') }}</span>
                    </div>
                    <ul class="flex-1 divide-y divide-[#E2DDD5]">
                        @foreach ($order->orderItems as $item)
                            <li class="flex gap-3 px-4 py-3">
                                <span class="w-8 shrink-0 text-sm font-bold tabular-nums">{{ $item->quantity }}x</span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold">{{ $item->product?->name ?? 'Menu dihapus' }}</p>
                                    @if ($item->note)
                                        <p class="text-sm text-[#B45309]">Catatan: {{ $item->note }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <form method="POST" action="{{ route('orders.complete', $order) }}" class="border-t border-[#E2DDD5] p-3">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex min-h-[44px] w-full items-center justify-center rounded-md bg-[#9A3412] px-4 text-sm font-semibold text-white hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2">Selesai Dibuat</button>
                    </form>
                </article>
                @if ($loop->last)
                    </div>
                @endif
            @empty
                <div class="rounded-lg border border-[#E2DDD5] bg-white p-8 text-center">
                    <p class="text-base font-semibold">Tidak ada antrean</p>
                    <p class="mt-1 text-sm text-[#57534E]">Semua pesanan sudah selesai dibuat.</p>
                </div>
            @endforelse
        </main>
    </body>
</html>
