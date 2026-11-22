@extends('layouts.app')

@section('title', 'Data Menu')

@section('content')
    <!-- ===== Header ===== -->
    <div class="page-header">
        <div>
            <h2>Data Menu</h2>
            <p>Kelola daftar kopi, minuman, makanan, dan dessert CaféNusa.</p>
        </div>
        <a href="{{ route('menu.create') }}" class="btn btn-primary">+ Tambah Menu</a>
    </div>

    <!-- ===== Filter & Pencarian ===== -->
    <form method="GET" action="{{ route('menu.index') }}" class="filter-bar filter-bar--simple" id="form-filter-menu">
        <div class="field field--search">
            <label class="label" for="search">Cari Menu <small class="text-muted">(live, tanpa Enter)</small></label>
            <div class="search-input-wrapper">
                <span class="search-icon">🔍</span>
                <input class="input" type="text" id="search" name="search" value="{{ $search }}" placeholder="Cari nama menu..."
                       data-live-search=".menu-row" data-live-search-status="#search-status" data-live-search-empty="#menu-empty-row">
            </div>
        </div>
        <div class="field">
            <label class="label" for="kategori">Kategori</label>
            <select class="select" id="kategori" name="kategori">
                <option value="">Semua Kategori</option>
                @foreach($semuaKategori as $item)
                    <option value="{{ $item->id }}" @selected((string) $kategori === (string) $item->id)>{{ $item->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-bar__actions" style="align-self:end;">
            <span id="search-status" class="text-muted text-sm" style="margin-right:4px;"></span>
            <button type="submit" class="btn btn-dark">Terapkan</button>
            @if($search !== '' || $kategori)
                <a href="{{ route('menu.index') }}" class="btn btn-outline">Reset</a>
            @endif
        </div>
    </form>

    <!-- ===== Tabel Menu ===== -->
    <div class="card card--flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Gambar</th>
                        <th>Nama Menu</th>
                        <th>Kategori</th>
                        <th class="num">Harga</th>
                        <th class="num">Stok</th>
                        <th>Status</th>
                        <th class="num" style="width:200px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menus as $menu)
                        <tr class="menu-row" data-search-text="{{ strtolower($menu->nama_menu) }} {{ strtolower($menu->kategori->nama ?? '') }}">
                            <td class="text-muted">{{ ($menus->currentPage() - 1) * $menus->perPage() + $loop->iteration }}</td>
                            <td>
                                <img src="{{ $menu->gambarUrl() }}" alt="{{ $menu->nama_menu }}" class="table-img">
                            </td>
                            <td><strong>{{ $menu->nama_menu }}</strong></td>
                            <td>
                                <span class="badge badge-neutral">{{ $menu->kategori->nama ?? '-' }}</span>
                            </td>
                            <td class="num">Rp {{ number_format($menu->harga, 0, ',', '.') }}</td>
                            <td class="num">
                                <span class="{{ $menu->stok <= 5 ? 'text-warning' : '' }}">{{ $menu->stok }} pcs</span>
                            </td>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" {{ $menu->is_event ? 'checked' : '' }} data-url="{{ route('menu.toggle', $menu) }}">
                                    <span class="switch-slider"></span>
                                </label>
                                <span class="badge {{ $menu->is_event ? 'badge-event' : 'badge-danger' }}">{{ $menu->is_event ? 'Event' : 'Nonaktif' }}</span>
                            </td>
                            <td class="num">
                                <div style="display:flex; gap:8px; justify-content:flex-end;">
                                    <a href="{{ route('menu.edit', $menu) }}" class="btn btn-sm btn-outline">Edit</a>
                                    <form action="{{ route('menu.destroy', $menu) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus menu &quot;{{ $menu->nama_menu }}&quot;?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger-soft">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty" id="menu-empty-row">
                                @if($search !== '' || $kategori)
                                    Tidak ada menu yang cocok dengan filter.
                                    <a href="{{ route('menu.index') }}" class="link">Reset filter</a>
                                @else
                                    Belum ada menu terdaftar. Klik <strong>+ Tambah Menu</strong> untuk mulai mengisi.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    @if($menus->isNotEmpty())
                        <tr id="menu-empty-row" style="display:none;">
                            <td colspan="8" class="empty">Tidak ada menu yang cocok dengan pencarian.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($menus->hasPages())
            <div class="pagination-wrap">
                {{ $menus->links() }}
            </div>
        @endif
    </div>

    <p class="text-muted text-sm" style="margin-top:12px;">
        Menampilkan {{ $menus->firstItem() ?? 0 }}–{{ $menus->lastItem() ?? 0 }} dari {{ $menus->total() }} menu.
    </p>
@endsection

@push('scripts')
<script>
    // Toggle Event/Nonaktif via AJAX (live search ditangani app.js tanpa reload)
    document.querySelectorAll('.switch input[type="checkbox"]').forEach(function(cb) {
        cb.addEventListener('change', function() {
            const url = this.dataset.url;
            const badge = this.closest('td').querySelector('.badge');
            const originalState = this.checked;

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(function(res) { return res.ok ? res.json() : Promise.reject(res); })
            .then(function(data) {
                if (data.is_event) {
                    badge.textContent = 'Event';
                    badge.className = 'badge badge-event';
                } else {
                    badge.textContent = 'Nonaktif';
                    badge.className = 'badge badge-danger';
                }
            })
            .catch(function() {
                // Revert on error
                cb.checked = !originalState;
                alert('Gagal mengubah status menu.');
            });
        });
    });
</script>
@endpush