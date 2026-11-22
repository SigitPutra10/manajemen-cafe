@extends('layouts.app')

@section('title', 'Kategori Menu')

@section('content')
    <div class="page-header">
        <div>
            <h2>Kategori Menu</h2>
            <p>Kelola kategori yang digunakan pada form Menu, Pemesanan, dan filter Dashboard.</p>
        </div>
        <button type="button" class="btn btn-primary" id="btn-tambah-kategori">+ Tambah Kategori</button>
    </div>

    <div class="card card--flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Menu</th>
                        <th class="num" style="width:220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategoris as $index => $kategori)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td><strong>{{ $kategori->nama }}</strong></td>
                            <td>
                                <span class="badge badge-neutral">{{ $kategori->menus_count }} menu</span>
                            </td>
                            <td class="num">
                                <div style="display:flex; gap:8px; justify-content:flex-end;">
                                    <button type="button"
                                            class="btn btn-sm btn-outline btn-edit"
                                            data-url="{{ route('kategori.update', $kategori) }}"
                                            data-nama="{{ $kategori->nama }}">
                                        Edit
                                    </button>
                                    <form action="{{ route('kategori.destroy', $kategori) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Yakin ingin menghapus kategori &quot;{{ $kategori->nama }}&quot;?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger-soft">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Belum ada kategori. Klik <strong>+ Tambah Kategori</strong> untuk mulai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== Modal Tambah / Edit ===== -->
    <div id="modal-kategori"
         style="display:none; position:fixed; inset:0; background:rgba(44,30,22,.55); z-index:50; align-items:center; justify-content:center; padding:16px;">
        <div class="card" style="max-width:420px; width:100%;">
            <h3 class="section-title" id="modal-title">Tambah Kategori</h3>
            <p class="section-sub" style="margin-bottom:16px;">Nama kategori harus unik.</p>

            <form id="form-kategori" method="POST">
                @csrf
                <div class="field">
                    <label class="label" for="nama">Nama Kategori <span class="req">*</span></label>
                    <input class="input" type="text" id="nama" name="nama" required maxlength="100"
                           placeholder="Contoh: Minuman Dingin">
                    <p class="error-text" id="nama-error" style="display:none;"></p>
                </div>

                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button type="button" class="btn btn-outline" id="btn-batal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const modal = document.getElementById('modal-kategori');
        const form = document.getElementById('form-kategori');
        const title = document.getElementById('modal-title');
        const input = document.getElementById('nama');
        const error = document.getElementById('nama-error');
        const token = document.querySelector('meta[name="csrf-token"]').content;

        function buka(url, nama) {
            form.action = url;
            form.querySelector('[name="_method"]')?.remove();

            if (nama) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = '_method';
                hidden.value = 'PUT';
                form.appendChild(hidden);
            }

            title.textContent = nama ? 'Edit Kategori' : 'Tambah Kategori';
            input.value = nama || '';
            error.style.display = 'none';
            modal.style.display = 'flex';
            input.focus();
        }

        function tutup() { modal.style.display = 'none'; }

        document.getElementById('btn-tambah-kategori').addEventListener('click', function () {
            buka('{{ route('kategori.store') }}', null);
        });

        document.querySelectorAll('.btn-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                buka(btn.dataset.url, btn.dataset.nama);
            });
        });

        document.getElementById('btn-batal').addEventListener('click', tutup);
        modal.addEventListener('click', function (e) { if (e.target === modal) tutup(); });

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const method = form.querySelector('[name="_method"]');
            const isEdit = method && method.value === 'PUT';

            fetch(form.action, {
                method: isEdit ? 'PUT' : 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'nama=' + encodeURIComponent(input.value),
            })
                .then(function (res) { return res.ok ? res.json() : Promise.reject(res); })
                .then(function () { window.location.reload(); })
                .catch(function (res) {
                    if (res && res.json) {
                        res.json().then(function (data) {
                            const pesan = (data.errors && data.errors.nama) ? data.errors.nama[0] : 'Gagal menyimpan kategori.';
                            error.textContent = pesan;
                            error.style.display = 'block';
                        });
                    } else {
                        error.textContent = 'Gagal menyimpan kategori.';
                        error.style.display = 'block';
                    }
                });
        });

        // Tambahkan method PUT sementara saat mode edit.
        document.querySelectorAll('.btn-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!form.querySelector('[name="_method"]')) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = '_method';
                    hidden.value = 'PUT';
                    form.appendChild(hidden);
                }
            });
        });
    })();
</script>
@endpush
