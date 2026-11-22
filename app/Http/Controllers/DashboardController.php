<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Menu;
use App\Models\Pemesanan;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ===== Filter =====
        $dari = $request->query('dari');
        $sampai = $request->query('sampai');
        $kategori = $request->query('kategori');
        $statusStok = $request->query('status_stok');
        $statusPesanan = $request->query('status_pesanan');

        if (! \is_string($dari) || ! \strtotime($dari)) {
            $dari = null;
        }

        if (! \is_string($sampai) || ! \strtotime($sampai)) {
            $sampai = null;
        }

        if (! \in_array($statusStok, ['Tersedia', 'Menipis', 'Habis'], true)) {
            $statusStok = null;
        }

        if (! \in_array($statusPesanan, Pemesanan::STATUS, true)) {
            $statusPesanan = null;
        }

        $filterAktif = $dari || $sampai || $request->filled('kategori') || $statusStok || $statusPesanan;

        // ===== KPI transaksi keuangan (terfilter rentang tanggal) =====
        $queryTransaksi = Transaksi::query()
            ->when($dari, fn ($q) => $q->where('tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->where('tanggal', '<=', $sampai));

        $totalPendapatan = (int) (clone $queryTransaksi)->where('jenis', 'Pemasukan')->sum('nominal');
        $totalBiaya = (int) (clone $queryTransaksi)->where('jenis', 'Biaya Operasional')->sum('nominal');
        $labaBersih = $totalPendapatan - $totalBiaya;

        // ===== KPI pemesanan (terfilter tanggal, kategori, status pesanan) =====
        $queryPesanan = Pemesanan::query()
            ->when($dari, fn ($q) => $q->where('tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->where('tanggal', '<=', $sampai))
            ->when($statusPesanan, fn ($q) => $q->where('status', $statusPesanan))
            ->when($request->filled('kategori'), function ($q) use ($kategori) {
                $q->whereHas('items.menu', fn ($m) => $m->where('kategori_id', $kategori));
            });

        $totalPesanan = (int) (clone $queryPesanan)->count();
        $omzetPesanan = (int) (clone $queryPesanan)->sum('total');

        // ===== Filter status stok mempengaruhi ringkasan menu =====
        $queryMenu = Menu::query()->with('kategori')
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori_id', $kategori))
            ->when($statusStok === 'Tersedia', fn ($q) => $q->where('stok', '>', 5))
            ->when($statusStok === 'Menipis', fn ($q) => $q->whereBetween('stok', [1, 5]))
            ->when($statusStok === 'Habis', fn ($q) => $q->where('stok', 0));

        $totalMenu = (int) (clone $queryMenu)->count();
        $menuMenipis = (int) Menu::whereBetween('stok', [1, 5])->count();
        $menuHabis = (int) Menu::where('stok', 0)->count();

        // ===== Grafik 12 bulan terakhir =====
        $awalPeriode = Carbon::now()->startOfMonth()->subMonths(11);
        $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $bulanan = [];
        for ($i = 0; $i < 12; $i++) {
            $bulan = $awalPeriode->copy()->addMonths($i);
            $key = $bulan->format('Y-m');

            $bulanan[$key] = [
                'label' => $namaBulan[$bulan->month - 1].' '.$bulan->format('y'),
                'pemasukan' => 0,
                'biaya' => 0,
            ];
        }

        $transaksiPeriode = (clone $queryTransaksi)
            ->where('tanggal', '>=', $awalPeriode->toDateString())
            ->get(['tanggal', 'jenis', 'nominal']);

        foreach ($transaksiPeriode as $transaksi) {
            $key = $transaksi->tanggal->format('Y-m');

            if (! isset($bulanan[$key])) {
                continue;
            }

            if ($transaksi->jenis === 'Pemasukan') {
                $bulanan[$key]['pemasukan'] += $transaksi->nominal;
            } else {
                $bulanan[$key]['biaya'] += $transaksi->nominal;
            }
        }

        $chartBulanan = [
            'labels' => \array_column($bulanan, 'label'),
            'pemasukan' => \array_column($bulanan, 'pemasukan'),
            'biaya' => \array_column($bulanan, 'biaya'),
        ];

        // ===== Komposisi menu per kategori =====
        $kategoriMenu = Menu::selectRaw('kategori_id, COUNT(*) as total')
            ->groupBy('kategori_id')
            ->pluck('total', 'kategori_id');

        $semuaKategori = Kategori::orderBy('nama')->get();

        $chartKategori = [
            'labels' => $semuaKategori->pluck('nama')->all(),
            'data' => $semuaKategori->map(fn (Kategori $k) => (int) ($kategoriMenu[$k->id] ?? 0))->all(),
        ];

        // ===== Tabel riwayat pemesanan (terfilter) =====
        $riwayatPesanan = (clone $queryPesanan)
            ->with('items.menu')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        // ===== Tabel riwayat transaksi keuangan (terfilter) =====
        $transaksiTerakhir = (clone $queryTransaksi)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        return view('dashboard.index', \compact(
            'totalPendapatan',
            'totalBiaya',
            'labaBersih',
            'totalPesanan',
            'omzetPesanan',
            'totalMenu',
            'menuMenipis',
            'menuHabis',
            'riwayatPesanan',
            'transaksiTerakhir',
            'chartBulanan',
            'chartKategori',
            'dari',
            'sampai',
            'kategori',
            'statusStok',
            'statusPesanan',
            'semuaKategori',
            'filterAktif',
        ));
    }
}
