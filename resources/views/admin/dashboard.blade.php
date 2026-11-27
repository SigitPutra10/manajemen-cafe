@php $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.'); @endphp
<x-admin-layout title="Dashboard">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-[#E2DDD5] bg-white p-4" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
            <p class="text-sm text-[#57534E]">Total Pendapatan Hari Ini</p>
            <p class="mt-1 text-2xl font-bold tabular-nums">{{ $rp($revenue) }}</p>
        </div>
        <div class="rounded-lg border border-[#E2DDD5] bg-white p-4" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
            <p class="text-sm text-[#57534E]">Total Transaksi Hari Ini</p>
            <p class="mt-1 text-2xl font-bold tabular-nums">{{ $transactionCount }}</p>
        </div>
        <div class="rounded-lg border border-[#E2DDD5] bg-white p-4" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
            <p class="text-sm text-[#57534E]">Total Menu Aktif</p>
            <p class="mt-1 text-2xl font-bold tabular-nums">{{ $activeMenuCount }}</p>
        </div>
        <div class="rounded-lg border border-[#E2DDD5] bg-white p-4" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
            <p class="text-sm text-[#57534E]">Menu Terlaris</p>
            @if ($topProduct)
                <p class="mt-1 truncate text-lg font-semibold" title="{{ $topProduct }}">{{ $topProduct }}</p>
                <p class="text-sm tabular-nums text-[#57534E]">{{ $topProductQty }} terjual hari ini</p>
            @else
                <p class="mt-1 text-lg font-semibold text-[#57534E]">Belum ada penjualan</p>
            @endif
        </div>
    </div>

    <section
        x-data="revenueChart(@js($revenueChart))"
        class="mt-6 rounded-lg border border-[#E2DDD5] bg-white p-4 sm:p-5"
        style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);"
        aria-labelledby="revenue-chart-heading"
    >
        <div class="flex flex-col gap-3 border-b border-[#E2DDD5] pb-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 id="revenue-chart-heading" class="text-base font-semibold text-[#1C1917]">Tren Pendapatan</h2>
                <p class="mt-0.5 text-sm text-[#57534E]" x-text="activeSeries.range_label">{{ $revenueChart['day']['range_label'] }}</p>
            </div>
            <div
                role="tablist"
                aria-label="Filter periode grafik pendapatan"
                class="grid grid-cols-2 gap-2 sm:inline-flex sm:flex-wrap"
            >
                @foreach (['day' => 'Hari', 'week' => 'Minggu', 'month' => 'Bulan', 'year' => 'Tahun'] as $key => $label)
                    <button
                        type="button"
                        role="tab"
                        x-ref="tab_{{ $key }}"
                        @click="selectPeriod('{{ $key }}')"
                        @keydown="onTabKeydown($event, {{ $loop->index }})"
                        :aria-selected="(period === '{{ $key }}').toString()"
                        :tabindex="period === '{{ $key }}' ? 0 : -1"
                        :class="period === '{{ $key }}'
                            ? 'border-[#9A3412] bg-[#9A3412] text-white hover:bg-[#7C2D12]'
                            : 'border-[#E2DDD5] bg-white text-[#1C1917] hover:bg-[#EFECE6]'"
                        class="inline-flex min-h-[44px] items-center justify-center rounded-md border px-4 py-2 text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2 {{ $key === 'day' ? 'border-[#9A3412] bg-[#9A3412] text-white' : 'border-[#E2DDD5] bg-white text-[#1C1917]' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-baseline gap-x-6 gap-y-2 text-sm">
            <div>
                <span class="text-[#57534E]">Total Periode:</span>
                <span class="font-bold text-[#1C1917] tabular-nums" x-text="formatRupiah(activeSeries.total)">{{ $rp($revenueChart['day']['total']) }}</span>
            </div>
            <div>
                <span class="text-[#57534E]">Transaksi:</span>
                <span class="font-semibold text-[#1C1917] tabular-nums" x-text="activeSeries.transactions">{{ $revenueChart['day']['transactions'] }}</span>
            </div>
            <div x-show="activeSeries.peak_label !== null" style="display: none;">
                <span class="text-[#57534E]">Tertinggi:</span>
                <span class="font-semibold text-[#1C1917] tabular-nums" x-text="activeSeries.peak_label + ' (' + formatRupiah(activeSeries.peak_value) + ')'"></span>
            </div>
        </div>

        <div class="relative mt-4 h-64 w-full sm:h-72">
            <canvas
                x-ref="canvas"
                aria-label="Grafik batang tren pendapatan"
                role="img"
                :class="(!ready || chartError !== '' || activeSeries.total === 0) ? 'opacity-0 pointer-events-none' : 'opacity-100'"
                class="h-full w-full transition-opacity duration-150 motion-reduce:transition-none"
            ></canvas>

            <div
                x-show="!ready"
                role="status"
                class="absolute inset-0 flex items-center justify-center rounded-md border border-[#E2DDD5] bg-[#F7F5F0]/60 px-4 text-center text-sm font-medium text-[#57534E]"
            >
                Memuat grafik pendapatan...
            </div>

            <div
                x-show="chartError !== ''"
                style="display: none;"
                role="alert"
                class="absolute inset-0 flex flex-col items-center justify-center rounded-md border border-[#B91C1C]/25 bg-[#FEE2E2] px-4 text-center"
            >
                <p class="text-sm font-medium text-[#B91C1C]" x-text="chartError"></p>
                <button
                    type="button"
                    @click="window.location.reload()"
                    class="mt-3 inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#B91C1C]/30 bg-white px-4 py-2 text-sm font-semibold text-[#B91C1C] transition-colors hover:bg-[#FEE2E2] focus:outline-none focus:ring-2 focus:ring-[#B91C1C] focus:ring-offset-2"
                >
                    Muat Ulang Halaman
                </button>
            </div>

            <div
                x-show="ready && chartError === '' && activeSeries.total === 0"
                style="display: none;"
                class="absolute inset-0 flex flex-col items-center justify-center rounded-md border border-[#E2DDD5] bg-[#F7F5F0]/50 px-4 text-center"
            >
                <p class="text-sm font-semibold text-[#1C1917]">Belum ada pendapatan pada periode ini.</p>
                <p class="mt-1 max-w-md text-xs text-[#57534E]">Transaksi yang selesai dibayar di kasir akan muncul otomatis pada grafik.</p>
                <a
                    href="{{ route('pos.index') }}"
                    class="mt-3 inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-4 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                >
                    Buka POS Kasir
                </a>
            </div>
        </div>
    </section>

    <section class="mt-6 rounded-lg border border-[#E2DDD5] bg-white" style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);">
        <div class="flex items-center justify-between gap-3 border-b border-[#E2DDD5] px-4 py-3">
            <h2 class="text-base font-semibold">5 Transaksi Terbaru Hari Ini</h2>
            <a href="{{ route('admin.reports.index') }}" class="inline-flex min-h-[44px] items-center text-sm font-semibold text-[#9A3412] hover:text-[#7C2D12]">Lihat Laporan</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-[#E2DDD5] text-[#57534E]">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Invoice</th>
                        <th class="px-4 py-2 font-semibold">Jam</th>
                        <th class="px-4 py-2 font-semibold">Pelanggan</th>
                        <th class="px-4 py-2 font-semibold">Tipe</th>
                        <th class="px-4 py-2 text-right font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2DDD5]">
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td class="px-4 py-3 font-semibold tabular-nums">{{ $order->invoice_number }}</td>
                            <td class="px-4 py-3 tabular-nums">{{ $order->created_at->format('H:i') }}</td>
                            <td class="px-4 py-3">{{ $order->customer_name }}</td>
                            <td class="px-4 py-3">{{ $order->order_type === 'dine_in' ? 'Dine-in' : 'Takeaway' }}</td>
                            <td class="px-4 py-3 text-right font-bold tabular-nums">{{ $rp($order->total_price) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-[#57534E]">Belum ada transaksi hari ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
