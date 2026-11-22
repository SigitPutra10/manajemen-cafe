<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

// ===== Halaman publik =====
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ===== Halaman terproteksi =====
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('menu', MenuController::class)->except(['show']);
    Route::patch('menu/{menu}/toggle', [MenuController::class, 'toggle'])->name('menu.toggle');

    Route::resource('kategori', KategoriController::class)->except(['show', 'create', 'edit']);

    Route::get('pemesanan', [PemesananController::class, 'index'])->name('pemesanan.index');
    Route::post('pemesanan', [PemesananController::class, 'store'])->name('pemesanan.store');
    Route::delete('pemesanan/{pemesanan}', [PemesananController::class, 'destroy'])->name('pemesanan.destroy');

    Route::resource('transaksi', TransaksiController::class)->except(['show']);
});
