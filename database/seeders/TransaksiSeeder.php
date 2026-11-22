<?php

namespace Database\Seeders;

use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        Transaksi::query()->delete();

        $keteranganPemasukan = [
            'Penjualan harian (dine-in)',
            'Penjualan takeaway',
            'Pesanan online (GoFood/GrabFood)',
            'Reservasi grup & komunitas',
            'Penjualan event akhir pekan',
            'Pesanan catering kantor',
            'Penjualan paket biji kopi',
        ];

        $keteranganBiaya = [
            'Pembelian biji kopi arabika',
            'Gaji barista part-time',
            'Listrik & air',
            'Sewa tempat bulanan',
            'Packaging cup & kemasan',
            'Maintenance mesin espresso',
            'Promosi media sosial',
            'Pembelian bahan baku makanan',
            'Biaya kebersihan & operasional',
        ];

        $hitungHarian = [];
        $awalBulan = Carbon::now()->startOfMonth()->subMonths(11);

        for ($bulanLalu = 11; $bulanLalu >= 0; $bulanLalu--) {
            $bulan = $awalBulan->copy()->addMonths($bulanLalu);

            $buatNominal = function (array $range): int {
                return (int) (round(random_int($range[0], $range[1]) / 5000) * 5000);
            };

            $buatKode = function (Carbon $tanggal) use (&$hitungHarian): string {
                $key = $tanggal->format('Ymd');
                $hitungHarian[$key] = ($hitungHarian[$key] ?? 0) + 1;

                return 'TRX-'.$key.'-'.str_pad((string) $hitungHarian[$key], 3, '0', STR_PAD_LEFT);
            };

            // Pemasukan
            for ($i = 0, $jml = random_int(5, 8); $i < $jml; $i++) {
                $tanggal = $bulan->copy()->addDays(random_int(0, $bulan->daysInMonth - 1));

                Transaksi::create([
                    'kode_transaksi' => $buatKode($tanggal),
                    'tanggal' => $tanggal->toDateString(),
                    'keterangan' => $keteranganPemasukan[array_rand($keteranganPemasukan)],
                    'jenis' => 'Pemasukan',
                    'nominal' => $buatNominal([600000, 2200000]),
                ]);
            }

            // Biaya Operasional
            for ($i = 0, $jml = random_int(3, 5); $i < $jml; $i++) {
                $tanggal = $bulan->copy()->addDays(random_int(0, $bulan->daysInMonth - 1));

                Transaksi::create([
                    'kode_transaksi' => $buatKode($tanggal),
                    'tanggal' => $tanggal->toDateString(),
                    'keterangan' => $keteranganBiaya[array_rand($keteranganBiaya)],
                    'jenis' => 'Biaya Operasional',
                    'nominal' => $buatNominal([300000, 900000]),
                ]);
            }
        }
    }
}
