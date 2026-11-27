<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Kasir POS - {{ $cafeName }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body class="bg-[#F7F5F0] text-[#1C1917] antialiased">
        <div
            x-data="pos(@js($products), @js(route('pos.store')))"
            class="flex min-h-screen flex-col"
        >
            <header class="flex min-h-16 flex-wrap items-center justify-between gap-3 border-b border-[#E2DDD5] bg-white px-4 py-2 sm:h-16 sm:flex-nowrap sm:py-0">
                <h1 class="truncate text-lg font-semibold text-[#1C1917]">{{ $cafeName }}</h1>
                <nav aria-label="Navigasi Kasir" class="flex flex-wrap items-center gap-3">
                    @if (auth()->user()->role === 'admin')
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="inline-flex h-11 items-center rounded-md border border-[#E2DDD5] bg-white px-4 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                        >
                            Dashboard Admin
                        </a>
                    @endif
                    <a
                        href="{{ route('orders.queue') }}"
                        class="inline-flex h-11 items-center rounded-md border border-[#E2DDD5] bg-white px-4 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                    >
                        Antrean Pesanan
                    </a>
                    <span class="inline-flex h-11 max-w-[180px] items-center rounded-md border border-[#E2DDD5] bg-[#EFECE6] px-3 text-sm font-semibold text-[#1C1917]">
                        <span class="truncate">{{ auth()->user()->name }}</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="inline-flex items-center m-0">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex h-11 items-center rounded-md border border-[#E2DDD5] bg-white px-4 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                        >
                            Keluar
                        </button>
                    </form>
                </nav>
            </header>

            <main class="flex flex-1 flex-col lg:flex-row">
                {{-- Kiri: katalog --}}
                <section class="min-w-0 p-4 lg:w-[65%]" aria-label="Katalog Menu">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#E2DDD5] pb-4">
                        <div class="relative w-full sm:w-64 lg:w-72">
                            <label for="pos-search" class="sr-only">Cari menu</label>
                            <svg
                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#57534E]"
                                viewBox="0 0 20 20"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.75"
                                aria-hidden="true"
                            >
                                <circle cx="9" cy="9" r="5.5" />
                                <path stroke-linecap="round" d="m13.5 13.5 3.5 3.5" />
                            </svg>
                            <input
                                id="pos-search"
                                type="search"
                                x-model="search"
                                placeholder="Cari menu..."
                                aria-label="Cari menu"
                                class="h-11 w-full rounded-md border border-[#E2DDD5] bg-white pl-9 pr-3 text-sm text-[#1C1917] placeholder:text-[#57534E] focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                            >
                        </div>

                        <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Kategori menu">
                            <button
                                type="button"
                                role="tab"
                                @click="category = null"
                                :aria-selected="category === null"
                                :class="category === null ? 'bg-[#9A3412] text-white border-[#9A3412]' : 'bg-white text-[#1C1917] border-[#E2DDD5] hover:bg-[#EFECE6]'"
                                class="inline-flex h-11 shrink-0 items-center rounded-md border px-4 text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                            >
                                Semua
                            </button>
                            @foreach ($categories as $cat)
                                <button
                                    type="button"
                                    role="tab"
                                    @click="category = {{ $cat->id }}"
                                    :aria-selected="category === {{ $cat->id }}"
                                    :class="category === {{ $cat->id }} ? 'bg-[#9A3412] text-white border-[#9A3412]' : 'bg-white text-[#1C1917] border-[#E2DDD5] hover:bg-[#EFECE6]'"
                                    class="inline-flex h-11 shrink-0 items-center rounded-md border px-4 text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                                >
                                    {{ $cat->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                        <template x-for="p in filtered" :key="p.id">
                            <button
                                type="button"
                                :disabled="!p.is_available"
                                @click="add(p)"
                                :aria-label="p.is_available ? 'Tambah ' + p.name : p.name + ', stok habis'"
                                class="group relative flex flex-col overflow-hidden rounded-lg border border-[#E2DDD5] bg-white text-left transition-colors hover:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2 disabled:opacity-60 disabled:hover:border-[#E2DDD5]"
                                style="box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);"
                            >
                                <div class="relative flex aspect-[4/3] w-full items-center justify-center overflow-hidden bg-[#EFECE6]">
                                    <template x-if="p.image_url">
                                        <img :src="p.image_url" :alt="p.name" width="400" height="300" class="h-full w-full object-cover" loading="lazy">
                                    </template>
                                    <template x-if="!p.image_url">
                                        <svg class="h-10 w-10 text-[#57534E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h1.5a2.5 2.5 0 0 1 0 5H17M5 8h12v6a5 5 0 0 1-5 5h-2a5 5 0 0 1-5-5V8Z" />
                                            <path stroke-linecap="round" d="M4 21h14" />
                                        </svg>
                                    </template>
                                    <span x-show="!p.is_available" class="absolute left-2 top-2 rounded-md bg-[#FEE2E2] px-2 py-0.5 text-xs font-semibold text-[#B91C1C]">Stok Habis</span>
                                </div>
                                <div class="flex flex-1 flex-col justify-between p-3">
                                    <div>
                                        <span
                                            x-show="p.category_name"
                                            x-text="p.category_name"
                                            class="inline-block w-fit rounded bg-[#EFECE6] px-2 py-0.5 text-[11px] font-medium text-[#57534E]"
                                        ></span>
                                        <p class="mt-1.5 text-sm font-semibold leading-snug text-[#1C1917]" x-text="p.name"></p>
                                    </div>
                                    <p class="mt-2 text-sm font-bold tabular-nums text-[#1C1917]" x-text="rp(p.price)"></p>
                                </div>
                            </button>
                        </template>
                    </div>

                    <div
                        x-show="filtered.length === 0"
                        x-cloak
                        class="mt-4 rounded-lg border border-[#E2DDD5] bg-white px-6 py-10 text-center"
                    >
                        <p class="text-sm font-semibold text-[#1C1917]">Menu tidak ditemukan</p>
                        <p class="mt-1 text-sm text-[#57534E]">Ubah kata kunci pencarian atau pilih kategori lain.</p>
                        <button
                            type="button"
                            x-show="search.trim() !== '' || category !== null"
                            @click="search = ''; category = null"
                            class="mt-4 inline-flex h-11 items-center rounded-md border border-[#E2DDD5] bg-white px-4 text-sm font-semibold text-[#1C1917] transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2"
                        >
                            Reset Filter
                        </button>
                    </div>
                </section>

                {{-- Kanan: keranjang --}}
                <aside class="border-t border-[#E2DDD5] bg-white lg:sticky lg:top-0 lg:h-dvh lg:w-[35%] lg:border-l lg:border-t-0" aria-label="Keranjang Pesanan">
                    <div class="flex h-full flex-col">
                        <div class="flex min-h-[56px] items-center justify-between gap-2 border-b border-[#E2DDD5] px-4 py-2">
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-semibold text-[#1C1917]">Pesanan Aktif</h2>
                                <span
                                    class="rounded-md bg-[#EFECE6] px-2 py-0.5 text-xs font-semibold tabular-nums text-[#1C1917]"
                                    x-text="itemCount + ' Item'"
                                ></span>
                            </div>
                            <button
                                type="button"
                                x-show="cart.length > 0"
                                x-cloak
                                @click="clearCart()"
                                class="inline-flex min-h-[44px] items-center rounded-md px-2.5 text-xs font-semibold text-[#B91C1C] transition-colors hover:bg-[#FEE2E2] focus:outline-none focus:ring-2 focus:ring-[#B91C1C] focus:ring-offset-1"
                            >
                                Kosongkan
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto">
                            <p x-show="cart.length === 0" class="px-4 py-6 text-sm text-[#57534E]">Keranjang masih kosong. Pilih menu di sebelah kiri.</p>
                            <ul>
                                <template x-for="(item, i) in cart" :key="item.id">
                                    <li class="border-b border-[#E2DDD5] px-4 py-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-sm font-semibold text-[#1C1917]" x-text="item.name"></p>
                                            <p class="text-sm font-bold tabular-nums text-[#1C1917]" x-text="rp(item.price * item.quantity)"></p>
                                        </div>
                                        <p class="text-xs tabular-nums text-[#57534E]" x-text="rp(item.price)"></p>
                                        <div class="mt-2 flex items-center gap-2">
                                            <button
                                                type="button"
                                                @click="dec(i)"
                                                aria-label="Kurangi"
                                                class="h-11 w-11 rounded-md border border-[#E2DDD5] text-lg font-semibold transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412]"
                                            >&minus;</button>
                                            <span class="w-8 text-center text-sm font-semibold tabular-nums" x-text="item.quantity"></span>
                                            <button
                                                type="button"
                                                @click="inc(i)"
                                                aria-label="Tambah"
                                                class="h-11 w-11 rounded-md border border-[#E2DDD5] text-lg font-semibold transition-colors hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412]"
                                            >+</button>
                                            <input
                                                type="text"
                                                x-model="item.note"
                                                maxlength="100"
                                                placeholder="Catatan, mis. Tanpa Gula"
                                                aria-label="Catatan item"
                                                class="min-h-[44px] min-w-0 flex-1 rounded-md border border-[#E2DDD5] px-3 text-sm text-[#1C1917] placeholder:text-[#57534E] focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                                            >
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        <div class="space-y-3 border-t border-[#E2DDD5] px-4 py-3">
                            <div class="grid grid-cols-2 gap-2">
                                <template x-for="t in [['dine_in','Dine-in'],['takeaway','Takeaway']]" :key="t[0]">
                                    <button
                                        type="button"
                                        @click="orderType = t[0]"
                                        :aria-pressed="orderType === t[0]"
                                        :class="orderType === t[0] ? 'bg-[#9A3412] text-white border-[#9A3412]' : 'bg-white text-[#1C1917] border-[#E2DDD5] hover:bg-[#EFECE6]'"
                                        class="h-11 rounded-md border text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-1"
                                        x-text="t[1]"
                                    ></button>
                                </template>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div :class="orderType === 'takeaway' ? 'col-span-2' : ''">
                                    <label for="pos-customer" class="mb-1 block text-xs font-semibold text-[#57534E]">Nama Pelanggan</label>
                                    <input
                                        id="pos-customer"
                                        type="text"
                                        x-model="customer"
                                        maxlength="100"
                                        placeholder="Nama pelanggan"
                                        class="h-11 w-full rounded-md border border-[#E2DDD5] px-3 text-sm text-[#1C1917] placeholder:text-[#57534E] focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                                    >
                                </div>

                                <div x-show="orderType === 'dine_in'">
                                    <label for="pos-table" class="mb-1 block text-xs font-semibold text-[#57534E]">No. Meja</label>
                                    <input
                                        id="pos-table"
                                        type="text"
                                        x-model="table"
                                        :disabled="orderType === 'takeaway'"
                                        maxlength="20"
                                        placeholder="Mis. 04"
                                        class="h-11 w-full rounded-md border border-[#E2DDD5] px-3 text-sm text-[#1C1917] placeholder:text-[#57534E] focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25 disabled:bg-[#EFECE6]"
                                    >
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <template x-for="m in [['cash','Cash'],['qris','QRIS']]" :key="m[0]">
                                    <button
                                        type="button"
                                        @click="method = m[0]"
                                        :aria-pressed="method === m[0]"
                                        :class="method === m[0] ? 'bg-[#9A3412] text-white border-[#9A3412]' : 'bg-white text-[#1C1917] border-[#E2DDD5] hover:bg-[#EFECE6]'"
                                        class="h-11 rounded-md border text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-1"
                                        x-text="m[1]"
                                    ></button>
                                </template>
                            </div>

                            <div x-show="method === 'cash'" class="space-y-2">
                                <div>
                                    <label for="pos-pay" class="mb-1 block text-xs font-semibold text-[#57534E]">Nominal Bayar</label>
                                    <input
                                        id="pos-pay"
                                        type="text"
                                        inputmode="numeric"
                                        x-model="payInput"
                                        placeholder="Nominal bayar"
                                        class="h-11 w-full rounded-md border border-[#E2DDD5] px-3 text-sm tabular-nums text-[#1C1917] placeholder:text-[#57534E] focus:border-[#9A3412] focus:outline-none focus:ring-2 focus:ring-[#9A3412]/25"
                                    >
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-[#57534E]">Kembalian</span>
                                    <span
                                        class="font-semibold tabular-nums"
                                        :class="pay < total ? 'text-[#B91C1C]' : 'text-[#15803D]'"
                                        x-text="pay >= total && total > 0 ? rp(pay - total) : rp(0)"
                                    ></span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-[#E2DDD5] pt-3">
                                <span class="text-sm font-semibold text-[#1C1917]">Total</span>
                                <span class="text-lg font-bold tabular-nums text-[#1C1917]" x-text="rp(total)"></span>
                            </div>

                            <p x-show="error" x-cloak x-text="error" class="rounded-md bg-[#FEE2E2] px-3 py-2 text-sm text-[#B91C1C]" role="alert"></p>

                            <button
                                type="button"
                                @click="startPay()"
                                :disabled="busy || cart.length === 0"
                                class="min-h-[52px] w-full rounded-md bg-[#9A3412] text-base font-semibold text-white transition-colors hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] focus:ring-offset-2 disabled:bg-[#EFECE6] disabled:text-[#57534E]"
                                x-text="busy ? 'Memproses...' : 'Bayar Sekarang'"
                            ></button>
                        </div>
                    </div>
                </aside>
            </main>

            {{-- Modal QRIS --}}
            <div x-show="qrisOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C1917]/60 p-4" @keydown.escape.window="qrisOpen = false" role="dialog" aria-modal="true" aria-label="Pembayaran QRIS">
                <div class="w-full max-w-sm rounded-lg border border-[#E2DDD5] bg-white p-5" @click.outside="qrisOpen = false">
                    <h3 class="text-base font-semibold">Pembayaran QRIS</h3>
                    <p class="mt-1 text-sm text-[#57534E]">Minta pelanggan memindai kode berikut.</p>
                    <svg viewBox="0 0 29 29" class="mx-auto mt-4 h-56 w-56 border border-[#E2DDD5] bg-white p-2" shape-rendering="crispEdges" role="img" aria-label="Kode QR contoh">
                        <rect width="29" height="29" fill="#fff"/>
                        <template x-for="c in qrCells" :key="c[0] + '-' + c[1]"><rect :x="c[0]" :y="c[1]" width="1" height="1" fill="#1C1917"/></template>
                    </svg>
                    <p class="mt-3 text-center text-lg font-bold tabular-nums" x-text="rp(total)"></p>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <button type="button" @click="qrisOpen = false" class="min-h-[44px] rounded-md border border-[#E2DDD5] text-sm font-semibold hover:bg-[#EFECE6] focus:outline-none focus:ring-2 focus:ring-[#9A3412]">Batal</button>
                        <button type="button" @click="submit()" :disabled="busy" class="min-h-[44px] rounded-md bg-[#9A3412] text-sm font-semibold text-white hover:bg-[#7C2D12] focus:outline-none focus:ring-2 focus:ring-[#9A3412] disabled:bg-[#EFECE6] disabled:text-[#57534E]">Konfirmasi Lunas</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('pos', (products, storeUrl) => ({
                    products, storeUrl,
                    search: '', category: null,
                    cart: [], customer: '', orderType: 'dine_in', table: '',
                    method: 'cash', payInput: '', qrisOpen: false, busy: false, error: '',

                    rp: (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID'),
                    get filtered() {
                        const q = this.search.trim().toLowerCase();
                        return this.products.filter(p =>
                            (this.category === null || p.category_id === this.category) &&
                            (q === '' || p.name.toLowerCase().includes(q)));
                    },
                    get itemCount() { return this.cart.reduce((s, i) => s + i.quantity, 0); },
                    get total() { return this.cart.reduce((s, i) => s + i.price * i.quantity, 0); },
                    get pay() { return parseInt(String(this.payInput).replace(/\D/g, '')) || 0; },
                    get qrCells() {
                        const cells = [], finder = (ox, oy) => {
                            for (let i = 0; i < 7; i++) for (let j = 0; j < 7; j++) {
                                const edge = i === 0 || j === 0 || i === 6 || j === 6;
                                const core = i >= 2 && i <= 4 && j >= 2 && j <= 4;
                                if (edge || core) cells.push([ox + i, oy + j]);
                            }
                        };
                        finder(1, 1); finder(21, 1); finder(1, 21);
                        let seed = 7;
                        for (let y = 0; y < 29; y++) for (let x = 0; x < 29; x++) {
                            const inFinder = (x < 9 && y < 9) || (x > 19 && y < 9) || (x < 9 && y > 19);
                            if (inFinder || x === 0 || y === 0 || x === 28 || y === 28) continue;
                            seed = (seed * 1103515245 + 12345) & 0x7fffffff;
                            if ((seed >> 16) % 2 === 0) cells.push([x, y]);
                        }
                        return cells;
                    },

                    add(p) {
                        if (!p.is_available) return;
                        const found = this.cart.find(i => i.id === p.id);
                        if (found) { if (found.quantity < 99) found.quantity++; }
                        else this.cart.push({ id: p.id, name: p.name, price: p.price, quantity: 1, note: '' });
                    },
                    inc(i) { if (this.cart[i].quantity < 99) this.cart[i].quantity++; },
                    dec(i) { this.cart[i].quantity > 1 ? this.cart[i].quantity-- : this.cart.splice(i, 1); },
                    clearCart() { this.cart = []; this.error = ''; },

                    startPay() {
                        this.error = '';
                        if (!this.customer.trim()) return this.error = 'Nama pelanggan wajib diisi.';
                        if (this.orderType === 'dine_in' && !this.table.trim()) return this.error = 'Nomor meja wajib diisi untuk Dine-in.';
                        if (this.method === 'qris') { this.qrisOpen = true; return; }
                        if (this.pay < this.total) return this.error = 'Nominal bayar kurang dari total.';
                        this.submit();
                    },
                    async submit() {
                        this.busy = true; this.error = '';
                        try {
                            const res = await fetch(this.storeUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json', 'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                },
                                body: JSON.stringify({
                                    customer_name: this.customer.trim(),
                                    order_type: this.orderType,
                                    table_number: this.orderType === 'dine_in' ? this.table.trim() : null,
                                    payment_method: this.method,
                                    pay_amount: this.method === 'qris' ? this.total : this.pay,
                                    items: this.cart.map(i => ({ product_id: i.id, quantity: i.quantity, note: i.note.trim() })),
                                }),
                            });
                            const data = await res.json();
                            if (!res.ok) {
                                this.qrisOpen = false;
                                this.error = data.message || 'Transaksi gagal disimpan.';
                                return;
                            }
                            window.location.href = data.redirect;
                        } catch (e) {
                            this.qrisOpen = false;
                            this.error = 'Koneksi bermasalah. Coba lagi.';
                        } finally { this.busy = false; }
                    },
                }));
            });
        </script>
    </body>
</html>
