<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    public const JENIS = ['Pemasukan', 'Biaya Operasional'];
    public const PEMASUKAN = 'Pemasukan';

    protected $fillable = [
        'kode_transaksi',
        'tanggal',
        'keterangan',
        'jenis',
        'nominal',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'integer',
    ];

    /**
     * Kode transaksi unik: TRX-YYYYMMDD-XXX (XXX berurutan per hari).
     * Dipakai TransaksiController dan PemesananController.
     */
    public static function generateKode(string $tanggal): string
    {
        $prefix = 'TRX-'.date('Ymd', \strtotime($tanggal)).'-';

        $terakhir = static::where('kode_transaksi', 'like', $prefix.'%')
            ->orderByDesc('kode_transaksi')
            ->value('kode_transaksi');

        $urut = $terakhir ? ((int) substr($terakhir, -3)) + 1 : 1;

        do {
            $kode = $prefix.\str_pad((string) $urut, 3, '0', \STR_PAD_LEFT);
            $urut++;
        } while (static::where('kode_transaksi', $kode)->exists());

        return $kode;
    }
}
