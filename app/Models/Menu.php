<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_menu',
        'kategori_id',
        'gambar',
        'harga',
        'stok',
        'status',
        'is_active',
        'is_event',
    ];

    protected $casts = [
        'harga' => 'integer',
        'stok' => 'integer',
        'is_active' => 'boolean',
        'is_event' => 'boolean',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function pesananItems(): HasMany
    {
        return $this->hasMany(PesananItem::class);
    }

    protected static function booted(): void
    {
        // Status selalu konsisten dengan jumlah stok.
        static::saving(function (Menu $menu) {
            $menu->status = $menu->stok > 0 ? 'Tersedia' : 'Habis';
        });
    }

    /**
     * URL gambar menu; memakai placeholder bila belum ada foto.
     */
    public function gambarUrl(): string
    {
        if ($this->gambar && \file_exists(\public_path('uploads/menus/'.$this->gambar))) {
            return \asset('uploads/menus/'.$this->gambar);
        }

        return \asset('uploads/placeholder.svg');
    }

    /**
     * Kelas badge warna untuk status stok: aman / menipis / habis.
     */
    public function stokBadgeClass(): string
    {
        if ($this->stok <= 0) {
            return 'badge-danger';
        }

        if ($this->stok <= 5) {
            return 'badge-warning';
        }

        return 'badge-success';
    }

    public function stokLabel(): string
    {
        if ($this->stok <= 0) {
            return 'Stok Habis';
        }

        if ($this->stok <= 5) {
            return 'Stok Menipis';
        }

        return 'Stok Aman';
    }
}
