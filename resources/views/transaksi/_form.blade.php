@php
    $transaksi = $transaksi ?? null;
@endphp

<form action="{{ $action }}" method="POST" class="form form--split" id="form-transaksi">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    @if($transaksi)
        <div class="alert alert-info form-col--full" style="margin-bottom:0;">
            <strong>Kode Transaksi:</strong> {{ $transaksi->kode_transaksi }}
        </div>
    @endif

    <div class="form-col">
        <div class="field">
            <label class="label" for="tanggal">Tanggal <span class="req">*</span></label>
            <input class="input" type="date" id="tanggal" name="tanggal" required
                   value="{{ old('tanggal', isset($transaksi) ? $transaksi->tanggal->format('Y-m-d') : now()->format('Y-m-d')) }}">
            @error('tanggal')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label class="label" for="keterangan">Keterangan <span class="req">*</span></label>
            <input class="input" type="text" id="keterangan" name="keterangan" required maxlength="255"
                   value="{{ old('keterangan', $transaksi->keterangan ?? '') }}"
                   placeholder="Contoh: Penjualan harian (dine-in)">
            @error('keterangan')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="form-col">
        <div class="field">
            <label class="label" for="jenis">Jenis Transaksi <span class="req">*</span></label>
            <select class="select" id="jenis" name="jenis" required>
                <option value="" disabled @selected(! old('jenis', $transaksi->jenis ?? null))>Pilih jenis</option>
                @foreach(\App\Models\Transaksi::JENIS as $item)
                    <option value="{{ $item }}" @selected(old('jenis', $transaksi->jenis ?? null) === $item)>{{ $item }}</option>
                @endforeach
            </select>
            @error('jenis')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label class="label" for="nominal">Nominal (Rp) <span class="req">*</span></label>
            <input class="input" type="number" id="nominal" name="nominal" required min="1" step="500"
                   value="{{ old('nominal', $transaksi->nominal ?? '') }}"
                   placeholder="1500000">
            @error('nominal')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('transaksi.index') }}" class="btn btn-outline">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>