<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Kopi', 'Non-Kopi', 'Makanan', 'Dessert'] as $nama) {
            Kategori::firstOrCreate(['nama' => $nama]);
        }
    }
}
