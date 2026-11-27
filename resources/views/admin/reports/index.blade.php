@php $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.'); @endphp
<x-admin-layout title="Laporan">
    <div
        x-data="{
            open: false,
            d: null,
            deleteOpen: false,
            deleteAction: '',
            deleteInvoice: '',
            deleteCustomer: '',
            deleting: false,
            rows: @js($detail),
            show(id) {
                this.d = this.rows[id];
                this.open = true;
            },
            confirmDelete(invoice, customer, actionUrl) {
                this.deleting = false;
                this.deleteInvoice = invoice;
                this.deleteCustomer = customer;
                this.deleteAction = actionUrl;
                this.deleteOpen = true;
                this.$nextTick(() => this.$refs.cancelDeleteBtn?.focus());
            },
            closeModals() {
                this.open = false;
                this.deleteOpen = false;
                this.deleting = false;
            },
            rp(n) {
                return 'Rp ' + Number(n).toLocaleString('id-ID');
            }
        }"
        @keydown.escape.window="closeModals()"
    >
        @if (session('status'))
            <div
                role="status"
                class="mb-4 flex items-center justify-between gap-3 rounded-lg border border-[#15803D]/25 bg-[#DCFCE7] px-4 py-3 text-sm font-medium text-[#15803D]"
            >
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-end gap-3 rounded-lg border border-[#E2DDD5] bg-white p-4">
            <div>
                <label for="start_date" class="block text-sm font-semibold">Dari Tanggal</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="mt-1 min-h-[44px] rounded-md border border-[#E2DDD5] bg-white px-3 text-sm focus:border-[#9A3412] focus:ring-[#9A3412]">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-semibold">Sampai Tanggal</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="mt-1 min-h-[44px] rounded-md border border-[#E2DDD5] bg-white px-3 text-sm focus:border-[#9A3412] focus:ring-[#9A3412]">
            </div>
            <button type="submit" class="inline-flex min-h-[44px] items-center rounded-md bg-[#9A3412] px-4 text-sm font-semibold text-white hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2">Terapkan Filter</button>
            <a href="{{ route('admin.reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="inline-flex min-h-[44px] items-center rounded-md border border-[#E2DDD5] bg-white px-4 text-sm font-semibold hover:bg-[#EFECE6]">Export CSV</a>
            @error('end_date')
                <p class="w-full text-sm text-[#B91C1C]">Tanggal akhir tidak boleh sebelum tanggal awal.</p>
            @enderror
        </form>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="rounded-lg border border-[#E2DDD5] bg-white p-4">
                <p class="text-sm text-[#57534E]">Total Pendapatan</p>
                <p class="mt-1 text-2xl font-bold tabular-nums">{{ $rp($revenue) }}</p>
            </div>
            <div class="rounded-lg border border-[#E2DDD5] bg-white p-4">
                <p class="text-sm text-[#57534E]">Jumlah Transaksi</p>
                <p class="mt-1 text-2xl font-bold tabular-nums">{{ $transactionCount }}</p>
            </div>
        </div>

        <section class="mt-4 rounded-lg border border-[#E2DDD5] bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-[#E2DDD5] text-[#57534E]">
                        <tr>
                            <th class="px-4 py-2 font-semibold">Invoice</th>
                            <th class="px-4 py-2 font-semibold">Tanggal</th>
                            <th class="px-4 py-2 font-semibold">Pelanggan</th>
                            <th class="px-4 py-2 font-semibold">Tipe</th>
                            <th class="px-4 py-2 font-semibold">Status</th>
                            <th class="px-4 py-2 text-right font-semibold">Total</th>
                            <th class="px-4 py-2"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2DDD5]">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-4 py-3 font-semibold tabular-nums">{{ $order->invoice_number }}</td>
                                <td class="px-4 py-3 tabular-nums">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $order->customer_name }}</td>
                                <td class="px-4 py-3">{{ $order->order_type === 'dine_in' ? 'Dine-in' : 'Takeaway' }}</td>
                                <td class="px-4 py-3">
                                    @if ($order->status === 'completed')
                                        <span class="rounded-md bg-[#DCFCE7] px-2 py-1 text-xs font-semibold text-[#15803D]">Selesai</span>
                                    @else
                                        <span class="rounded-md bg-[#FEF3C7] px-2 py-1 text-xs font-semibold text-[#B45309]">Diproses</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-bold tabular-nums">{{ $rp($order->total_price) }}</td>
                                <td class="px-4 py-2 text-right">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            @click="show({{ $order->id }})"
                                            class="inline-flex min-h-[44px] items-center rounded-md border border-[#E2DDD5] bg-white px-3 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                                        >
                                            Detail
                                        </button>
                                        <button
                                            type="button"
                                            @click="confirmDelete(@js($order->invoice_number), @js($order->customer_name), @js(route('admin.reports.destroy', $order)))"
                                            class="inline-flex min-h-[44px] items-center rounded-md border border-[#B91C1C]/30 bg-white px-3 text-sm font-semibold text-[#B91C1C] transition-colors hover:bg-[#FEE2E2] focus:outline-none focus:ring-2 focus:ring-[#B91C1C] focus:ring-offset-2"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-[#57534E]">Tidak ada transaksi pada rentang tanggal ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($orders->hasPages())
                <div class="border-t border-[#E2DDD5] px-4 py-3">{{ $orders->links() }}</div>
            @endif
        </section>

        {{-- Modal Detail Transaksi --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C1917]/40 p-4" @click.self="closeModals()" style="display: none;">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg border border-[#E2DDD5] bg-white" role="dialog" aria-modal="true" aria-labelledby="detail-title" x-show="d">
                <template x-if="d">
                    <div>
                        <div class="flex items-start justify-between gap-3 border-b border-[#E2DDD5] p-4">
                            <div>
                                <h2 id="detail-title" class="text-base font-semibold tabular-nums" x-text="d.invoice"></h2>
                                <p class="text-sm text-[#57534E]"><span x-text="d.customer"></span> · <span x-text="d.type"></span><template x-if="d.table"><span> · Meja <span class="tabular-nums" x-text="d.table"></span></span></template></p>
                                <p class="text-sm tabular-nums text-[#57534E]"><span x-text="d.date"></span> · <span x-text="d.method"></span></p>
                            </div>
                            <button type="button" @click="closeModals()" class="inline-flex min-h-[44px] items-center rounded-md border border-[#E2DDD5] bg-white px-3 text-sm font-semibold hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2">Tutup</button>
                        </div>
                        <ul class="divide-y divide-[#E2DDD5]">
                            <template x-for="item in d.items" :key="item.name + item.price + (item.note || '')">
                                <li class="flex justify-between gap-3 px-4 py-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold"><span class="tabular-nums" x-text="item.qty + 'x'"></span> <span x-text="item.name"></span></p>
                                        <p class="text-xs tabular-nums text-[#57534E]" x-text="rp(item.price)"></p>
                                        <p class="text-sm text-[#B45309]" x-show="item.note" x-text="'Catatan: ' + item.note"></p>
                                    </div>
                                    <p class="text-sm font-bold tabular-nums" x-text="rp(item.subtotal)"></p>
                                </li>
                            </template>
                        </ul>
                        <dl class="space-y-1 border-t border-[#E2DDD5] p-4 text-sm tabular-nums">
                            <div class="flex justify-between"><dt>Total</dt><dd class="font-bold" x-text="rp(d.total)"></dd></div>
                            <div class="flex justify-between text-[#57534E]"><dt>Dibayar</dt><dd x-text="rp(d.pay)"></dd></div>
                            <div class="flex justify-between text-[#57534E]"><dt>Kembalian</dt><dd x-text="rp(d.change)"></dd></div>
                        </dl>
                    </div>
                </template>
            </div>
        </div>

        {{-- Modal Konfirmasi Hapus Transaksi --}}
        <div
            x-show="deleteOpen"
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C1917]/40 p-4"
            style="display: none;"
            @click.self="closeModals()"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-order-title"
        >
            <div
                class="w-full max-w-md rounded-lg border border-[#E2DDD5] bg-white p-6"
                style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);"
            >
                <div class="flex items-center justify-between gap-4 border-b border-[#E2DDD5] pb-3">
                    <h2 id="delete-order-title" class="text-base font-semibold text-[#1C1917]">
                        Hapus Transaksi
                    </h2>
                    <button
                        type="button"
                        @click="closeModals()"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-md text-[#57534E] transition-colors hover:bg-[#EFECE6] hover:text-[#1C1917] focus:outline-none focus:ring-2 focus:ring-[#9A3412]"
                        aria-label="Tutup modal konfirmasi hapus"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 5l10 10M15 5L5 15" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4">
                    <p class="text-sm text-[#1C1917]">
                        Yakin ingin menghapus transaksi <span class="font-semibold tabular-nums" x-text="deleteInvoice"></span> atas nama <span class="font-semibold" x-text="deleteCustomer"></span>? Data pesanan beserta seluruh item di dalamnya akan dihapus permanen.
                    </p>
                </div>

                <form
                    method="POST"
                    :action="deleteAction"
                    @submit="deleting = true"
                    class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"
                >
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                    <input type="hidden" name="end_date" value="{{ $endDate }}">
                    <button
                        type="button"
                        x-ref="cancelDeleteBtn"
                        @click="closeModals()"
                        class="inline-flex min-h-[44px] items-center justify-center rounded-md border border-[#E2DDD5] bg-white px-4 py-2 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="deleting"
                        class="inline-flex min-h-[44px] items-center justify-center rounded-md bg-[#B91C1C] px-4 py-2 text-sm font-semibold text-white transition-opacity hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#B91C1C] focus:ring-offset-2 disabled:opacity-60"
                    >
                        <span x-text="deleting ? 'Menghapus...' : 'Hapus Transaksi'">Hapus Transaksi</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
