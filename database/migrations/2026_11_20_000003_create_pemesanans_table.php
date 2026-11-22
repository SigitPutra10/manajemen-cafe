<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pesanan')->unique();
            $table->string('nama_pelanggan')->nullable();
            $table->date('tanggal');
            $table->integer('total')->default(0);
            $table->string('status')->default('Selesai'); // Selesai | Dibatalkan
            $table->timestamps();

            $table->index('tanggal');
            $table->index('status');
        });

        Schema::create('pesanan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained()->restrictOnDelete();
            $table->integer('jumlah');
            $table->integer('harga_satuan');
            $table->integer('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_items');
        Schema::dropIfExists('pemesanans');
    }
};
