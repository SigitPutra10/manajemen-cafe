@php
    $menu = $menu ?? null;
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="form form--split" id="form-menu">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="form-col">
        <div class="field">
            <label class="label" for="nama_menu">Nama Menu <span class="req">*</span></label>
            <input class="input" type="text" id="nama_menu" name="nama_menu" required maxlength="255"
                   value="{{ old('nama_menu', $menu->nama_menu ?? '') }}"
                   placeholder="Contoh: Kopi Susu Gula Aren">
            @error('nama_menu')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label class="label" for="kategori_id">Kategori <span class="req">*</span></label>
            <select class="select" id="kategori_id" name="kategori_id" required>
                <option value="" disabled @selected(! old('kategori_id', $menu->kategori_id ?? null))>Pilih kategori</option>
                @foreach($semuaKategori as $item)
                    <option value="{{ $item->id }}" @selected(old('kategori_id', $menu->kategori_id ?? null) === $item->id)>{{ $item->nama }}</option>
                @endforeach
            </select>
            @error('kategori_id')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label class="label" for="gambar">Gambar Menu <span class="req">*</span></label>
            <div class="image-preview-wrapper">
                <img id="gambar-preview" src="{{ $menu && $menu->gambar ? asset('uploads/menus/' . $menu->gambar) : asset('uploads/placeholder.svg') }}" alt="Preview gambar" class="image-preview">
                <input class="input" type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp" {{ !$menu ? 'required' : '' }}>
                <p class="hint">Format: JPG, PNG, WEBP. Maksimal 2MB. Dimensi ideal 400x400px.</p>
            </div>
            @error('gambar')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="form-col">
        <div class="field">
            <label class="label" for="harga">Harga (Rp) <span class="req">*</span></label>
            <input class="input" type="number" id="harga" name="harga" required min="0" step="500"
                   value="{{ old('harga', $menu->harga ?? '') }}"
                   placeholder="22000">
            @error('harga')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label class="label" for="stok">Stok (pcs) <span class="req">*</span></label>
            <input class="input" type="number" id="stok" name="stok" required min="0"
                   value="{{ old('stok', $menu->stok ?? 0) }}"
                   placeholder="0">
            @error('stok')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <div class="field field--checkbox">
            <label class="checkbox-label">
                <input type="checkbox" name="is_event" id="is_event" value="1" {{ old('is_event', $menu->is_event ?? false) ? 'checked' : '' }}>
                <span class="checkbox-custom"></span>
                <span>Menu Event / Limited (aktifkan untuk tampil di pemesanan)</span>
            </label>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('menu.index') }}" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>