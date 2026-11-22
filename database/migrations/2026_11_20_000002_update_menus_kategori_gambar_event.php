<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->foreignId('kategori_id')->nullable()->after('nama_menu');
            $table->string('gambar')->nullable()->after('nama_menu');
            $table->boolean('is_event')->default(false)->after('is_active');
        });

        // Pindahkan data kategori lama (string) ke foreign key baru.
        DB::statement('
            UPDATE menus m
            JOIN kategorias k ON k.nama = m.kategori
            SET m.kategori_id = k.id
        ');

        // Pastikan tidak ada baris yang tersisa tanpa kategori.
        DB::statement('
            UPDATE menus
            SET kategori_id = (SELECT id FROM kategorias ORDER BY id LIMIT 1)
            WHERE kategori_id IS NULL
        ');

        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });

        DB::statement('ALTER TABLE menus MODIFY kategori_id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('nama_menu');
            $table->dropColumn(['kategori_id', 'gambar', 'is_event']);
        });
    }
};
