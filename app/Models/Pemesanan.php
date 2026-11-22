<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pemesanan extends Model
{
    use HasFactory;

    public const STATUS = ['Selesai', 'Dibatalkan'];

    protected $fillable = [
        'kode_pesanan',
        'nama_pelanggan',
        'tanggal',
        'total',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PesananItem::class);
    }
}
