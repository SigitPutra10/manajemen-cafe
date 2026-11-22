@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- ===== Header ===== -->
    <div class="page-header">
        <div>
            <h2>Dashboard CaféNusa</h2>
            <p>Ringkasan penjualan, stok, dan transaksi kedai.</p>
        </div>
        <a href="{{ route('pemesanan.index') }}" class="btn btn-primary">+ Buat Pemesanan</a>
    </div>

    <!-- ===== Filter ===== -->
    <form method="GET" action="{{ route('dashboard') }}" class="filter-bar">
        <div class="field">
            <label class="label" for="dari">Tanggal Mulai</label>
            <input class="input" type="date" id="dari" name="dari" value="{{ $dari }}">
        </div>
        <div class="field">
            <label class="label" for="sampai">Tanggal Akhir</label>
            <input class="input" type="date" id="sampai" name="sampai" value="{{ $sampai }}">
        </div>
        <div class="field">
            <label class="label" for="kategori">Kategori Menu</label>
            <select class="select" id="kategori" name="kategori">
                <option value="">Semua Kategori</option>
                @foreach($semuaKategori as $item)
                    <option value="{{ $item->id }}" @selected((string) $kategori === (string) $item->id)>{{ $item->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label class="label" for="status_stok">Status Stok</label>
            <select class="select" id="status_stok" name="status_stok">
                <option value="">Semua Status</option>
                <option value="Tersedia" @selected($statusStok === 'Tersedia')>Stok Aman</option>
                <option value="Menipis" @selected($statusStok === 'Menipis')>Stok Menipis</option>
                <option value="Habis" @selected($statusStok === 'Habis')>Stok Habis</option>
            </select>
        </div>
        <div class="field">
            <label class="label" for="status_pesanan">Status Pesanan</label>
            <select class="select" id="status_pesanan" name="status_pesanan">
                <option value="">Semua Status</option>
                @foreach(\App\Models\Pemesanan::STATUS as $item)
                    <option value="{{ $item }}" @selected($statusPesanan === $item)>{{ $item }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-bar__actions">
            <button type="submit" class="btn btn-dark">Terapkan</button>
            @if($filterAktif)
                <a href="{{ route('dashboard') }}" class="btn btn-outline">Reset</a>
            @endif
        </div>
    </form>

    <!-- ===== KPI ===== -->
    <div class="grid-kpi">
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <p class="kpi__label">Pendapatan</p>
                <span>💰</span>
            </div>
            <p class="kpi__value">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
            <p class="kpi__hint">Akumulasi pemasukan transaksi</p>
        </div>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <p class="kpi__label">Biaya Operasional</p>
                <span>🧾</span>
            </div>
            <p class="kpi__value">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</p>
            <p class="kpi__hint">Akumulasi pengeluaran</p>
        </div>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <p class="kpi__label">Laba Bersih</p>
                <span>📈</span>
            </div>
            <p class="kpi__value" style="color:{{ $labaBersih >= 0 ? 'inherit' : 'var(--badge-danger)' }};">
                Rp {{ number_format($labaBersih, 0, ',', '.') }}
            </p>
            <p class="kpi__hint" style="color:{{ $labaBersih >= 0 ? 'var(--badge-success)' : 'var(--badge-danger)' }};">
                {{ $labaBersih >= 0 ? '● Profit sehat' : '● Defisit — evaluasi biaya' }}
            </p>
        </div>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <p class="kpi__label">Pemesanan</p>
                <span>🧺</span>
            </div>
            <p class="kpi__value">{{ $totalPesanan }} pesanan</p>
            <p class="kpi__hint">Omzet Rp {{ number_format($omzetPesanan, 0, ',', '.') }}</p>
        </div>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <p class="kpi__label">Menu Tercatat</p>
                <span>🍽️</span>
            </div>
            <p class="kpi__value">{{ $totalMenu }} menu</p>
            <p class="kpi__hint">
                <span class="badge badge-warning">{{ $menuMenipis }} menipis</span>
                <span class="badge badge-danger">{{ $menuHabis }} habis</span>
            </p>
        </div>
    </div>

    <!-- ===== Grafik ===== -->
    <div class="grid-kpi" style="grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));">
        <div class="card">
            <div style="margin-bottom:16px;">
                <h3 class="section-title">Pendapatan vs Biaya</h3>
                <p class="section-sub">12 bulan terakhir</p>
            </div>
            <div style="height:280px;">
                <canvas id="chartSiklus"></canvas>
            </div>
        </div>

        <div class="card">
            <div style="margin-bottom:16px;">
                <h3 class="section-title">Komposisi Menu</h3>
                <p class="section-sub">Jumlah menu per kategori</p>
            </div>
            <div style="height:280px;">
                <canvas id="chartKategori"></canvas>
            </div>
        </div>
    </div>

    <!-- ===== Riwayat Pemesanan ===== -->
    <div class="card card--flush" style="margin-top:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border-bottom:1px solid #F0EBE3;">
            <div>
                <h3 class="section-title">Riwayat Pemesanan</h3>
                <p class="section-sub">8 pesanan terbaru sesuai filter</p>
            </div>
            <a href="{{ route('pemesanan.index') }}" class="show-more">Lihat semua →</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode</th>
                        <th>Pelanggan</th>
                        <th>Menu</th>
                        <th>Status</th>
                        <th class="num">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayatPesanan as $pesanan)
                        <tr>
                            <td class="text-muted">{{ $pesanan->tanggal->format('d M Y') }}</td>
                            <td>{{ $pesanan->kode_pesanan }}</td>
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
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Belum ada pemesanan yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== Riwayat Transaksi ===== -->
    <div class="card card--flush" style="margin-top:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border-bottom:1px solid #F0EBE3;">
            <div>
                <h3 class="section-title">Transaksi Keuangan Terakhir</h3>
                <p class="section-sub">8 transaksi terbaru sesuai filter</p>
            </div>
            <a href="{{ route('transaksi.index') }}" class="show-more">Lihat semua →</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode</th>
                        <th>Keterangan</th>
                        <th>Jenis</th>
                        <th class="num">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksiTerakhir as $transaksi)
                        <tr>
                            <td class="text-muted">{{ $transaksi->tanggal->format('d M Y') }}</td>
                            <td>{{ $transaksi->kode_transaksi }}</td>
                            <td>{{ $transaksi->keterangan }}</td>
                            <td>
                                <span class="badge {{ $transaksi->jenis === 'Pemasukan' ? 'badge-success' : 'badge-danger' }}">{{ $transaksi->jenis }}</span>
                            </td>
                            <td class="num" style="color:{{ $transaksi->jenis === 'Pemasukan' ? 'var(--badge-success)' : 'var(--badge-danger)' }};">
                                {{ $transaksi->jenis === 'Pemasukan' ? '+' : '−' }} Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">Belum ada transaksi yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    const dataBulanan = @json($chartBulanan);
    const dataKategori = @json($chartKategori);
    const rupiah = (angka) => 'Rp ' + Number(angka).toLocaleString('id-ID');

    new Chart(document.getElementById('chartSiklus'), {
        type: 'line',
        data: {
            labels: dataBulanan.labels,
            datasets: [
                {
                    label: 'Pendapatan',
                    data: dataBulanan.pemasukan,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.10)',
                    pointBackgroundColor: '#10B981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 3,
                    borderWidth: 2.5,
                    tension: 0.35,
                    fill: true,
                },
                {
                    label: 'Biaya Operasional',
                    data: dataBulanan.biaya,
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.08)',
                    pointBackgroundColor: '#EF4444',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 3,
                    borderWidth: 2.5,
                    tension: 0.35,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#2C1E16',
                    padding: 10,
                    callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${rupiah(ctx.parsed.y)}` },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#F0EBE3' },
                    ticks: {
                        callback: (value) => 'Rp ' + (value / 1000000) + ' jt',
                        color: '#9C8B78',
                        font: { size: 11 },
                    },
                },
                x: { grid: { display: false }, ticks: { color: '#9C8B78', font: { size: 11 } } },
            },
        },
    });

    new Chart(document.getElementById('chartKategori'), {
        type: 'doughnut',
        data: {
            labels: dataKategori.labels,
            datasets: [{
                data: dataKategori.data,
                backgroundColor: ['#D97706', '#6366F1', '#10B981', '#F59E0B', '#EF4444', '#64748B'],
                borderColor: '#FFFFFF',
                borderWidth: 3,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '66%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, boxWidth: 8, padding: 14, color: '#6B7280', font: { size: 11 } },
                },
                tooltip: {
                    backgroundColor: '#2C1E16',
                    padding: 10,
                    callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.parsed} menu` },
                },
            },
        },
    });
</script>
@endpush
