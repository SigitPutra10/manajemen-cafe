@extends('layouts.app')

@section('title', 'Edit Transaksi')

@section('content')
    <div class="page-header">
        <div>
            <a href="{{ route('transaksi.index') }}" class="link" style="font-size:13px;">← Kembali ke Data Transaksi</a>
            <h2>Edit Transaksi</h2>
            <p>Perbarui data transaksi <strong>{{ $transaksi->kode_transaksi }}</strong>.</p>
        </div>
    </div>

    <div class="card card--form">
        @include('transaksi._form', [
            'action' => route('transaksi.update', $transaksi),
            'method' => 'PUT',
            'transaksi' => $transaksi,
            'submitLabel' => 'Update Transaksi',
        ])
    </div>
@endsection