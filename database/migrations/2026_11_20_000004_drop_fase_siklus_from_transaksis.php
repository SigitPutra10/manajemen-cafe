<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('fase_siklus');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->enum('fase_siklus', ['Recovery', 'Expansion', 'Peak', 'Contraction'])->default('Recovery')->after('nominal');
        });
    }
};
