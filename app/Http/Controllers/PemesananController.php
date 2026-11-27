<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Menu;
use App\Models\Pemesanan;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PemesananController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $kategori = $request->query('kategori');

        // Menu Event/Limited (is_event = true) yang ditampilkan ke kasir/pelanggan.
        $menus = Menu::query()
            ->with('kategori')
            ->where('is_event', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where('nama_menu', 'like', '%'.$search.'%');
            })
            ->when($request->filled('kategori'), function ($query) use ($kategori) {
                $query->where('kategori_id', $kategori);
            })
            ->orderBy('nama_menu')
            ->get();

        $semuaKategori = Kategori::orderBy('nama')->get();

        $pemesananTerakhir = Pemesanan::with('items.menu')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        return view('pemesanan.index', compact('menus', 'semuaKategori', 'search', 'kategori', 'pemesananTerakhir'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pelanggan' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'exists:menus,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ], [], [
            'items' => 'pesanan',
            'items.*.menu_id' => 'menu',
            'items.*.jumlah' => 'jumlah',
        ]);

        $pemesanan = DB::transaction(function () use ($validated) {
            $total = 0;
            $rincian = [];

            foreach ($validated['items'] as $item) {
                $jumlah = (int) $item['jumlah'];

                // Kunci baris agar tidak ada balapan stok (race condition).
                $menu = Menu::whereKey($item['menu_id'])->lockForUpdate()->first();

                if (! $menu || ! $menu->is_event) {
                    throw ValidationException::withMessages([
                        'items' => 'Menu yang dipilih tidak tersedia atau sudah nonaktif.',
                    ]);
                }

                if ($menu->stok < $jumlah) {
                    throw ValidationException::withMessages([
                        'items' => 'Stok "'.$menu->nama_menu.'" tidak mencukupi. Sisa stok: '.$menu->stok.'.',
                    ]);
                }

                $rincian[] = ['menu' => $menu, 'jumlah' => $jumlah];
            }

            foreach ($rincian as $baris) {
                /** @var Menu $menu */
                $menu = $baris['menu'];
                $jumlah = $baris['jumlah'];

                $menu->stok -= $jumlah; // Model hook otomatis menyesuaikan status.
                $menu->save();

                $total += $menu->harga * $jumlah;
            }

            $pemesanan = Pemesanan::create([
                'kode_pesanan' => $this->generateKodePesanan(),
                'nama_pelanggan' => $validated['nama_pelanggan'] ?? null,
                'tanggal' => now()->toDateString(),
                'total' => $total,
                'status' => 'Selesai',
            ]);

            foreach ($rincian as $baris) {
                /** @var Menu $menu */
                $menu = $baris['menu'];
                $jumlah = $baris['jumlah'];

                $pemesanan->items()->create([
                    'menu_id' => $menu->id,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $menu->harga,
                    'subtotal' => $menu->harga * $jumlah,
                ]);
            }

            // Otomatis catat pemasukan ke tabel transaksis.
            $daftarMenu = [];
            foreach ($rincian as $baris) {
                $daftarMenu[] = $baris['jumlah'].'x '.$baris['menu']->nama_menu;
            }

            $keterangan = 'Pesanan '.$pemesanan->kode_pesanan
                .($pemesanan->nama_pelanggan ? ' - '.$pemesanan->nama_pelanggan : '')
                .' ('.\implode(', ', $daftarMenu).')';

            Transaksi::create([
                'kode_transaksi' => Transaksi::generateKode(now()->toDateString()),
                'tanggal' => now()->toDateString(),
                'jenis' => Transaksi::PEMASUKAN,
                'keterangan' => \mb_substr($keterangan, 0, 255),
                'nominal' => $pemesanan->total,
            ]);

            return $pemesanan;
        });

        return redirect()
            ->route('pemesanan.index')
            ->with('success', 'Pesanan '.$pemesanan->kode_pesanan.' berhasil dibuat. Total Rp '.\number_format($pemesanan->total, 0, ',', '.').'. Stok otomatis berkurang.');
    }

    public function destroy(Pemesanan $pemesanan)
    {
        if ($pemesanan->status === 'Selesai') {
            // Kembalikan stok + hapus transaksi pemasukan terkait pesanan ini.
            DB::transaction(function () use ($pemesanan) {
                Transaksi::where('keterangan', 'like', '%'.$pemesanan->kode_pesanan.'%')->delete();

                foreach ($pemesanan->items as $item) {
                    $menu = Menu::whereKey($item->menu_id)->lockForUpdate()->first();

                    if ($menu) {
                        $menu->stok += $item->jumlah;
                        $menu->save();
                    }
                }

                $pemesanan->delete();
            });
        } else {
            // Pesanan dibatalkan: hapus transaksi terkait (bila ada) tanpa
            // mengubah stok (stok tidak pernah dipotong untuk status ini).
            Transaksi::where('keterangan', 'like', '%'.$pemesanan->kode_pesanan.'%')->delete();
            $pemesanan->delete();
        }

        return redirect()
            ->route('pemesanan.index')
            ->with('success', 'Pesanan '.$pemesanan->kode_pesanan.' dihapus, transaksi terkait ikut dihapus, dan stok dikembalikan.');
    }

    private function generateKodePesanan(): string
    {
        $prefix = 'PSN-'.now()->format('Ymd').'-';

        $terakhir = Pemesanan::where('kode_pesanan', 'like', $prefix.'%')
            ->orderByDesc('kode_pesanan')
            ->value('kode_pesanan');

        $urut = $terakhir ? ((int) substr($terakhir, -3)) + 1 : 1;

        do {
            $kode = $prefix.\str_pad((string) $urut, 3, '0', \STR_PAD_LEFT);
            $urut++;
        } while (Pemesanan::where('kode_pesanan', $kode)->exists());

        return $kode;
    }
}
