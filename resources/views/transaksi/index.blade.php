@extends('layouts.app')

@section('title', 'Data Transaksi')

@section('content')
    <!-- ===== Header ===== -->
    <div class="page-header">
        <div>
            <h2>Data Transaksi</h2>
            <p>Catat pemasukan dan biaya operasional kedai.</p>
        </div>
        <a href="{{ route('transaksi.create') }}" class="btn btn-primary">+ Tambah Transaksi</a>
    </div>

    <!-- ===== Filter ===== -->
    <form method="GET" action="{{ route('transaksi.index') }}" class="filter-bar filter-bar--simple">
        <div class="field">
            <label class="label" for="jenis">Jenis Transaksi</label>
            <select class="select" id="jenis" name="jenis">
                <option value="">Semua Jenis</option>
                @foreach(\App\Models\Transaksi::JENIS as $item)
                    <option value="{{ $item }}" @selected($jenis === $item)>{{ $item }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label class="label" for="dari">Tanggal Mulai</label>
            <input class="input" type="date" id="dari" name="dari" value="{{ $dari }}">
        </div>
        <div class="field">
            <label class="label" for="sampai">Tanggal Akhir</label>
            <input class="input" type="date" id="sampai" name="sampai" value="{{ $sampai }}">
        </div>
        <div class="filter-bar__actions">
            <button type="submit" class="btn btn-dark">Terapkan</button>
            @if($jenis || $dari || $sampai)
                <a href="{{ route('transaksi.index') }}" class="btn btn-outline">Reset</a>
            @endif
        </div>
    </form>

    <!-- ===== Tabel Transaksi ===== -->
    <div class="card card--flush">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode</th>
                        <th>Keterangan</th>
                        <th>Jenis</th>
                        <th class="num">Nominal</th>
                        <th class="num" style="width:180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis as $transaksi)
                        <tr>
                            <td class="text-muted">{{ $transaksi->tanggal->format('d M Y') }}</td>
                            <td>{{ $transaksi->kode_transaksi }}</td>
                            <td>{{ $transaksi->keterangan }}</td>
                            <td>
                                <span class="badge {{ $transaksi->jenis === 'Pemasukan' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $transaksi->jenis }}
                                </span>
                            </td>
                            <td class="num" style="color:{{ $transaksi->jenis === 'Pemasukan' ? 'var(--badge-success)' : 'var(--badge-danger)' }};">
                                {{ $transaksi->jenis === 'Pemasukan' ? '+' : '−' }} Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                            </td>
                            <td class="num">
                                <div style="display:flex; gap:8px; justify-content:flex-end;">
                                    <a href="{{ route('transaksi.edit', $transaksi) }}" class="btn btn-sm btn-outline">Edit</a>
                                    <form action="{{ route('transaksi.destroy', $transaksi) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus transaksi {{ $transaksi->kode_transaksi }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger-soft">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                @if($jenis || $dari || $sampai)
                                    Tidak ada transaksi yang cocok dengan filter.
                                    <a href="{{ route('transaksi.index') }}" class="link">Reset filter</a>
                                @else
                                    Belum ada transaksi. Klik <strong>+ Tambah Transaksi</strong> untuk mulai mencatat.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transaksis->hasPages())
            <div class="pagination-wrap">
                {{ $transaksis->links() }}
            </div>
        @endif
    </div>

    <p class="text-muted text-sm" style="margin-top:12px;">
        Menampilkan {{ $transaksis->firstItem() ?? 0 }}–{{ $transaksis->lastItem() ?? 0 }} dari {{ $transaksis->total() }} transaksi.
    </p>
@endsection