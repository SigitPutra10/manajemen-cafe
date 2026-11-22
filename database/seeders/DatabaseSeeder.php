<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin CaféNusa',
            'email' => 'admin@cafenusa.test',
            'password' => 'password',
        ]);

        $this->call([
            KategoriSeeder::class,
            MenuSeeder::class,
            // TransaksiSeeder sengaja tidak dipanggil: data transaksi
            // dimulai dari kosong dan diisi melalui form transaksi baru.
        ]);
    }
}
