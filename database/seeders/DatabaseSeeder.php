<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@cafe.test'],
            [
                'name' => 'Admin Cafe',
                'password' => 'password',
                'role' => 'admin',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'kasir@cafe.test'],
            [
                'name' => 'Kasir Cafe',
                'password' => 'password',
                'role' => 'kasir',
            ],
        );

        Setting::query()->updateOrCreate(
            ['id' => 1],
            [
                'cafe_name' => 'Nama Cafe',
                'address' => 'Jl. Kopi No. 12, Jakarta Selatan',
                'phone' => '021-5550123',
            ],
        );

        $menuData = [
            'Coffee' => [
                ['name' => 'Espresso', 'price' => 18000],
                ['name' => 'Kopi Susu Gula Aren', 'price' => 25000],
            ],
            'Non-Coffee' => [
                ['name' => 'Matcha Latte', 'price' => 28000],
                ['name' => 'Lychee Tea', 'price' => 22000],
            ],
            'Pastry' => [
                ['name' => 'Butter Croissant', 'price' => 24000],
                ['name' => 'Pain au Chocolat', 'price' => 26000],
            ],
        ];

        foreach ($menuData as $categoryName => $products) {
            $category = Category::query()->firstOrCreate([
                'name' => $categoryName,
            ]);

            foreach ($products as $product) {
                Product::query()->updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'name' => $product['name'],
                    ],
                    [
                        'price' => $product['price'],
                        'is_available' => true,
                    ],
                );
            }
        }
    }
}
