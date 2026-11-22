@extends('layouts.app')

@section('title', 'Tambah Transaksi')

@section('content')
    <div class="page-header">
        <div>
            <a href="{{ route('transaksi.index') }}" class="link" style="font-size:13px;">← Kembali ke Data Transaksi</a>
            <h2>Tambah Transaksi</h2>
            <p>Catat pemasukan atau biaya operasional baru.</p>
        </div>
    </div>

    <div class="card card--form">
        @include('transaksi._form', [
            'action' => route('transaksi.store'),
            'method' => 'POST',
            'transaksi' => null,
            'submitLabel' => 'Simpan Transaksi',
        ])
    </div>
@endsection