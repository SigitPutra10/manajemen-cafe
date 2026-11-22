@extends('layouts.app')

@section('title', 'Tambah Menu')

@section('content')
    <div class="page-header">
        <div>
            <a href="{{ route('menu.index') }}" class="link" style="font-size:13px;">← Kembali ke Data Menu</a>
            <h2>Tambah Menu Baru</h2>
            <p>Lengkapi data menu CaféNusa di bawah ini.</p>
        </div>
    </div>

    <div class="card card--form">
        @include('menu._form', [
            'action' => route('menu.store'),
            'method' => 'POST',
            'menu' => null,
            'submitLabel' => 'Simpan Menu',
        ])
    </div>
@endsection

@push('scripts')
<script>
    // Image preview with client-side validation
    const gambarInput = document.getElementById('gambar');
    const preview = document.getElementById('gambar-preview');

    gambarInput?.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        // Client-side validation
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.');
            this.value = '';
            preview.src = '{{ asset("uploads/placeholder.svg") }}';
            return;
        }

        if (file.size > maxSize) {
            alert('Ukuran file melebihi 2MB.');
            this.value = '';
            preview.src = '{{ asset("uploads/placeholder.svg") }}';
            return;
        }

        // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
</script>
@endpush