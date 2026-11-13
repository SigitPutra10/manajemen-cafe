<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Menu - CaféNusa.</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#F8FAFC] text-slate-800 font-sans antialiased">

<div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between p-5">
        <div>
            <!-- Logo Brand -->
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 rounded-xl bg-amber-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                    ☕
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight text-slate-900">CaféNusa.</h1>
                    <p class="text-xs text-slate-400">Smart Cafe & Business Cycle</p>
                </div>
            </div>

            <!-- Navigasi -->
            <nav class="space-y-2 text-sm font-medium">
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition">
                    <span>📊</span>
                    <span>Dashboard & Siklus</span>
                </a>
                <a href="{{ route('menu.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-amber-50 text-amber-700 font-semibold">
                    <span>🍴</span>
                    <span>Manajemen Menu (CRUD)</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 transition">
                    <span>🧾</span>
                    <span>Kelola Transaksi</span>
                </a>
            </nav>
        </div>

        <div class="text-xs text-slate-400 border-t pt-4">
            Branch: <span class="font-semibold text-amber-600">nabila</span>
        </div>
    </aside>

    <!-- Konten Utama -->
    <main class="flex-1 p-8">
        <!-- Header Atas -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Manajemen Menu</h2>
                <p class="text-sm text-slate-500">Kelola daftar kopi, minuman, dan makanan CaféNusa.</p>
            </div>
            <button onclick="openModalTambah()" class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition cursor-pointer">
                + Tambah Menu Baru
            </button>
        </div>

        <!-- Notifikasi Sukses -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium">
                ✅ {{ session('success') }}
            </div>
        @endif

        <!-- Error Validasi -->
        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Kartu Statistik Ringkas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">MENU TERDAFTAR</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1">{{ $menus->count() }}</p>
                    <p class="text-xs text-amber-600 mt-2 font-medium">🍴 Total Varian Menu</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    ☕
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">MENU AKTIF</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1">{{ $menus->where('is_active', true)->count() }}</p>
                    <p class="text-xs text-emerald-600 mt-2 font-medium">● Siap Dipesan Pelanggan</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    ✅
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Menu -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="font-bold text-slate-900">Daftar Menu CaféNusa</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                            <th class="py-3.5 px-6">No</th>
                            <th class="py-3.5 px-6">Nama Menu</th>
                            <th class="py-3.5 px-6">Kategori</th>
                            <th class="py-3.5 px-6">Harga</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($menus as $index => $menu)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-4 px-6 text-slate-500">{{ $index + 1 }}</td>
                                <td class="py-4 px-6 font-semibold text-slate-900">{{ $menu->nama_menu }}</td>
                                <td class="py-4 px-6">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                                        {{ $menu->kategori }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-medium text-slate-800">
                                    Rp {{ number_format($menu->harga, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6">
                                    @if($menu->is_active)
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                            ● Aktif
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                            ○ Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openModalEdit({{ $menu->id }}, '{{ addslashes($menu->nama_menu) }}', '{{ $menu->kategori }}', {{ $menu->harga }}, {{ $menu->is_active ? 'true' : 'false' }})"
                                        class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 text-xs font-semibold transition cursor-pointer">
                                        Edit
                                    </button>
                                    <form action="{{ route('menu.destroy', $menu->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus menu ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 text-xs font-semibold transition cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400">
                                    Belum ada menu terdaftar. Klik <strong>+ Tambah Menu Baru</strong> untuk mulai mengisi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Modal Tambah Menu -->
<div id="modalTambah" class="fixed inset-0 bg-slate-900/40 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-slate-900">Tambah Menu Baru</h3>
            <button onclick="closeModalTambah()" class="text-slate-400 hover:text-slate-600 text-xl cursor-pointer">&times;</button>
        </div>
        <form action="{{ route('menu.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Menu</label>
                <input type="text" name="nama_menu" required placeholder="Contoh: Kopi Susu Gula Aren"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-amber-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori</label>
                <select name="kategori" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-amber-500">
                    <option value="Kopi">Kopi</option>
                    <option value="Non-Kopi">Non-Kopi</option>
                    <option value="Makanan">Makanan</option>
                    <option value="Snack">Snack</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Harga (Rp)</label>
                <input type="number" name="harga" required min="0" placeholder="Contoh: 22000"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-amber-500">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="tambah_active" checked class="rounded text-amber-600">
                <label for="tambah_active" class="text-sm text-slate-700">Menu Aktif (Tersedia)</label>
            </div>
            <div class="flex justify-end gap-2 pt-3">
                <button type="button" onclick="closeModalTambah()" class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-sm font-semibold bg-amber-600 hover:bg-amber-700 text-white cursor-pointer">Simpan Menu</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Menu -->
<div id="modalEdit" class="fixed inset-0 bg-slate-900/40 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-slate-900">Edit Menu</h3>
            <button onclick="closeModalEdit()" class="text-slate-400 hover:text-slate-600 text-xl cursor-pointer">&times;</button>
        </div>
        <form id="formEdit" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Menu</label>
                <input type="text" name="nama_menu" id="edit_nama_menu" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-amber-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori</label>
                <select name="kategori" id="edit_kategori" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-amber-500">
                    <option value="Kopi">Kopi</option>
                    <option value="Non-Kopi">Non-Kopi</option>
                    <option value="Makanan">Makanan</option>
                    <option value="Snack">Snack</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Harga (Rp)</label>
                <input type="number" name="harga" id="edit_harga" required min="0"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-amber-500">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="edit_is_active" class="rounded text-amber-600">
                <label for="edit_is_active" class="text-sm text-slate-700">Menu Aktif (Tersedia)</label>
            </div>
            <div class="flex justify-end gap-2 pt-3">
                <button type="button" onclick="closeModalEdit()" class="px-4 py-2 rounded-xl text-sm text-slate-600 hover:bg-slate-100 cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-sm font-semibold bg-amber-600 hover:bg-amber-700 text-white cursor-pointer">Update Menu</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModalTambah() {
        document.getElementById('modalTambah').classList.remove('hidden');
        document.getElementById('modalTambah').classList.add('flex');
    }
    function closeModalTambah() {
        document.getElementById('modalTambah').classList.add('hidden');
        document.getElementById('modalTambah').classList.remove('flex');
    }
    function openModalEdit(id, nama, kategori, harga, isActive) {
        document.getElementById('formEdit').action = '/menu/' + id;
        document.getElementById('edit_nama_menu').value = nama;
        document.getElementById('edit_kategori').value = kategori;
        document.getElementById('edit_harga').value = harga;
        document.getElementById('edit_is_active').checked = isActive;
        document.getElementById('modalEdit').classList.remove('hidden');
        document.getElementById('modalEdit').classList.add('flex');
    }
    function closeModalEdit() {
        document.getElementById('modalEdit').classList.add('hidden');
        document.getElementById('modalEdit').classList.remove('flex');
    }
</script>

</body>
</html>