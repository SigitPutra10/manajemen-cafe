<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->date('tanggal');
            $table->string('keterangan');
            $table->enum('jenis', ['Pemasukan', 'Biaya Operasional']);
            $table->integer('nominal');
            $table->enum('fase_siklus', ['Recovery', 'Expansion', 'Peak', 'Contraction']);
            $table->timestamps();

            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
