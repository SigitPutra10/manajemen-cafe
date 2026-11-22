@extends('layouts.app')

@section('title', 'Pemesanan')

@section('content')
    <div class="page-header">
        <div>
            <h2>Pemesanan Baru</h2>
            <p>Pilih menu, masukkan jumlah, dan buat pesanan pelanggan.</p>
        </div>
    </div>

    <form id="form-pemesanan" method="POST" action="{{ route('pemesanan.store') }}">
        @csrf

        <!-- Grid 2 kolom: kiri = pelanggan + pilih menu, kanan = ringkasan -->
        <div class="order-layout">
            <div class="order-layout__main">

                <!-- Pelanggan Info -->
                <div class="card">
                    <h3 class="section-title">Informasi Pelanggan</h3>
                    <div class="field-row">
                        <div class="field">
                            <label class="label" for="nama_pelanggan">Nama Pelanggan</label>
                            <input class="input" type="text" id="nama_pelanggan" name="nama_pelanggan" placeholder="Kosongkan untuk pelanggan umum">
                        </div>
                        <div class="field">
                            <label class="label" for="catatan">Catatan</label>
                            <input class="input" type="text" id="catatan" name="catatan" placeholder="Contoh: Kurang manis, tambah es">
                        </div>
                    </div>
                </div>

                <!-- Menu Items -->
                <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 class="section-title" style="margin:0;">Pilih Menu</h3>
                <div style="display:flex; gap:8px;">
                    <input class="input" type="text" id="search-menu" placeholder="Cari menu..." style="width:250px;">
                    <select class="select" id="filter-kategori" style="width:180px;">
                        <option value="">Semua Kategori</option>
                        @foreach($semuaKategori as $item)
                            <option value="{{ $item->id }}">{{ $item->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="menu-grid" class="menu-grid">
                @foreach($menus as $menu)
                    <div class="menu-card" data-kategori="{{ $menu->kategori_id }}" data-nama="{{ strtolower($menu->nama_menu) }}">
                        <img src="{{ $menu->gambarUrl() }}" alt="{{ $menu->nama_menu }}" class="menu-card__img">
                        <div class="menu-card__body">
                            <div class="menu-card__name">{{ $menu->nama_menu }}</div>
                            <div class="menu-card__meta">
                                <span class="badge badge-neutral">{{ $menu->kategori->nama }}</span>
                                <span class="badge {{ $menu->stokBadgeClass() }}">{{ $menu->stokLabel() }}</span>
                            </div>
                            <div class="menu-card__price">Rp {{ number_format($menu->harga, 0, ',', '.') }}</div>
                            <div class="qty-row">
                                <label class="qty-row__label" for="qty-{{ $menu->id }}">Qty</label>
                                <div class="qty-stepper">
                                    <button type="button" class="qty-btn" data-qty="-1" aria-label="Kurangi jumlah {{ $menu->nama_menu }}">&minus;</button>
                                    <input type="number" id="qty-{{ $menu->id }}" name="items[{{ $menu->id }}][jumlah]" value="0" min="0" max="{{ $menu->stok }}" class="input qty-input" data-harga="{{ $menu->harga }}" data-stok="{{ $menu->stok }}" data-nama="{{ $menu->nama_menu }}">
                                    <button type="button" class="qty-btn" data-qty="1" aria-label="Tambah jumlah {{ $menu->nama_menu }}">+</button>
                                </div>
                                <input type="hidden" name="items[{{ $menu->id }}][menu_id]" value="{{ $menu->id }}">
                                <input type="hidden" name="items[{{ $menu->id }}][harga_satuan]" value="{{ $menu->harga }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($menus->isEmpty())
                <p class="empty" style="text-align:center; padding:40px;">Tidak ada menu Event/Limited yang tersedia. Aktifkan menu di halaman Data Menu.</p>
            @endif
                </div><!-- /Pilih Menu -->

            </div><!-- /.order-layout__main -->

            <!-- Ringkasan Pesanan (kolom kanan) -->
            <div class="card order-layout__summary">
            <h3 class="section-title">Ringkasan Pesanan</h3>
            <div id="order-summary" style="margin-bottom:16px;">
                <p class="text-muted" style="text-align:center; padding:20px;">Belum ada item dipilih</p>
            </div>
            <div style="border-top:1px solid #F0EBE3; padding-top:16px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                    <span>Subtotal</span>
                    <strong id="subtotal">Rp 0</strong>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:18px; color:var(--primary-accent);">
                    <span>Total</span>
                    <strong id="total">Rp 0</strong>
                </div>
            </div>
            <div class="form-actions" style="margin-top:20px; justify-content:flex-end;">
                <button type="submit" class="btn btn-primary" id="btn-submit" disabled style="width:100%;">Buat Pesanan</button>
            </div>
        </div>
        </div><!-- /.order-layout -->
    </form>

    <!-- ===== Pesanan Terbaru ===== -->
    <div class="card card--flush" style="margin-top:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border-bottom:1px solid #F0EBE3;">
            <div>
                <h3 class="section-title">Pesanan Terbaru</h3>
            </div>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Menu</th>
                        <th>Status</th>
                        <th class="num">Total</th>
                        <th class="num" style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pemesananTerakhir as $pesanan)
                        <tr>
                            <td>{{ $pesanan->kode_pesanan }}</td>
                            <td class="text-muted">{{ $pesanan->tanggal->format('d M Y') }}</td>
                            <td>{{ $pesanan->nama_pelanggan ?: 'Umum' }}</td>
                            <td>
                                @foreach($pesanan->items as $item)
                                    <span class="badge badge-neutral">{{ $item->jumlah }}× {{ $item->menu->nama_menu ?? 'Menu' }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge {{ $pesanan->status === 'Selesai' ? 'badge-success' : 'badge-danger' }}">{{ $pesanan->status }}</span>
                            </td>
                            <td class="num"><strong>Rp {{ number_format($pesanan->total, 0, ',', '.') }}</strong></td>
                            <td class="num">
                                <form action="{{ route('pemesanan.destroy', $pesanan) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pesanan {{ $pesanan->kode_pesanan }}? Stok akan dikembalikan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger-soft">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">Belum ada pesanan. Buat pesanan pertama Anda di atas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function() {
        const qtyInputs = document.querySelectorAll('.qty-input');
        const searchInput = document.getElementById('search-menu');
        const kategoriFilter = document.getElementById('filter-kategori');
        const menuCards = document.querySelectorAll('.menu-card');
        const orderSummary = document.getElementById('order-summary');
        const subtotalEl = document.getElementById('subtotal');
        const totalEl = document.getElementById('total');
        const btnSubmit = document.getElementById('btn-submit');

        const rupiah = (angka) => 'Rp ' + Number(angka).toLocaleString('id-ID');

        function updateSummary() {
            let subtotal = 0;
            let items = [];

            qtyInputs.forEach(function(input) {
                const jumlah = parseInt(input.value) || 0;
                if (jumlah > 0) {
                    const harga = parseInt(input.dataset.harga);
                    const nama = input.dataset.nama;
                    const itemTotal = harga * jumlah;
                    subtotal += itemTotal;
                    items.push({ nama, jumlah, harga, total: itemTotal });
                }
            });

            // Update summary display
            if (items.length === 0) {
                orderSummary.innerHTML = '<p class="text-muted" style="text-align:center; padding:20px;">Belum ada item dipilih</p>';
            } else {
                orderSummary.innerHTML = items.map(function(item) {
                    return '<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #F0EBE3;">' +
                        '<div><strong>' + item.nama + '</strong><br><small class="text-muted">' + item.jumlah + ' × ' + rupiah(item.harga) + '</small></div>' +
                        '<span>' + rupiah(item.total) + '</span>' +
                    '</div>';
                }).join('');
            }

            subtotalEl.textContent = rupiah(subtotal);
            totalEl.textContent = rupiah(subtotal);
            btnSubmit.disabled = items.length === 0;
        }

        // Quantity change
        qtyInputs.forEach(function(input) {
            input.addEventListener('input', function() {
                // Validate max stock
                const max = parseInt(this.dataset.stok) || 0;
                let val = parseInt(this.value) || 0;
                if (val > max) {
                    this.value = max;
                    val = max;
                }
                if (val < 0) {
                    this.value = 0;
                    val = 0;
                }
                updateSummary();
            });
        });

        // Tombol stepper (- / +) pada kartu menu
        document.querySelectorAll('.qty-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const card = this.closest('.menu-card');
                const input = card ? card.querySelector('.qty-input') : null;
                if (!input) return;

                const max = parseInt(input.dataset.stok) || 0;
                let val = parseInt(input.value) || 0;
                val += parseInt(this.dataset.qty) || 0;
                if (val > max) val = max;
                if (val < 0) val = 0;

                input.value = val;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });

        // Live search menu
        let searchTimer = null;
        searchInput?.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                const query = this.value.toLowerCase();
                menuCards.forEach(function(card) {
                    const nama = card.dataset.nama;
                    card.style.display = nama.includes(query) ? '' : 'none';
                });
            }, 150);
        });

        // Filter by kategori
        kategoriFilter?.addEventListener('change', function() {
            const katId = this.value;
            menuCards.forEach(function(card) {
                if (!katId || card.dataset.kategori === katId) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Validasi submit (item 0 tidak ikut terkirim + alert bila kosong)
        // ditangani oleh public/js/app.js agar ada satu sumber kebenaran.
    })();
</script>
@endpush