<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        Menu::query()->delete();

        $menus = [
            ['nama_menu' => 'Espresso', 'kategori' => 'Kopi', 'harga' => 18000, 'stok' => 40],
            ['nama_menu' => 'Americano', 'kategori' => 'Kopi', 'harga' => 20000, 'stok' => 35],
            ['nama_menu' => 'Kopi Susu Gula Aren', 'kategori' => 'Kopi', 'harga' => 22000, 'stok' => 50],
            ['nama_menu' => 'Cappuccino', 'kategori' => 'Kopi', 'harga' => 25000, 'stok' => 30],
            ['nama_menu' => 'Caffè Latte', 'kategori' => 'Kopi', 'harga' => 25000, 'stok' => 28],
            ['nama_menu' => 'V60 Gayo', 'kategori' => 'Kopi', 'harga' => 30000, 'stok' => 15],
            ['nama_menu' => 'Mocha Latte', 'kategori' => 'Kopi', 'harga' => 28000, 'stok' => 20],
            ['nama_menu' => 'Matcha Latte', 'kategori' => 'Non-Kopi', 'harga' => 26000, 'stok' => 25],
            ['nama_menu' => 'Chocolate Hazelnut', 'kategori' => 'Non-Kopi', 'harga' => 24000, 'stok' => 4],
            ['nama_menu' => 'Teh Tarik', 'kategori' => 'Non-Kopi', 'harga' => 18000, 'stok' => 40],
            ['nama_menu' => 'Lemon Tea Segar', 'kategori' => 'Non-Kopi', 'harga' => 15000, 'stok' => 45],
            ['nama_menu' => 'Nasi Goreng CaféNusa', 'kategori' => 'Makanan', 'harga' => 32000, 'stok' => 18],
            ['nama_menu' => 'Mie Goreng Spesial', 'kategori' => 'Makanan', 'harga' => 30000, 'stok' => 16],
            ['nama_menu' => 'Sandwich Ayam Panggang', 'kategori' => 'Makanan', 'harga' => 28000, 'stok' => 12],
            ['nama_menu' => 'French Fries', 'kategori' => 'Makanan', 'harga' => 18000, 'stok' => 30],
            ['nama_menu' => 'Brownies Fudgy', 'kategori' => 'Dessert', 'harga' => 20000, 'stok' => 14],
            ['nama_menu' => 'Cheesecake Slice', 'kategori' => 'Dessert', 'harga' => 27000, 'stok' => 10],
            ['nama_menu' => 'Croissant Butter', 'kategori' => 'Dessert', 'harga' => 19000, 'stok' => 0],
            ['nama_menu' => 'Puding Cokelat', 'kategori' => 'Dessert', 'harga' => 15000, 'stok' => 9],
        ];

        // Menu event / limited edition.
        $event = ['Kopi Susu Gula Aren', 'V60 Gayo', 'Cheesecake Slice'];

        foreach ($menus as $data) {
            $kategori = Kategori::firstWhere('nama', $data['kategori']);

            Menu::create([
                'nama_menu' => $data['nama_menu'],
                'kategori_id' => $kategori?->id,
                'gambar' => null,
                'harga' => $data['harga'],
                'stok' => $data['stok'],
                'is_active' => true,
                'is_event' => \in_array($data['nama_menu'], $event, true),
            ]);
        }
    }
}
