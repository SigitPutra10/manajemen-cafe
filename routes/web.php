<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\OrderQueueController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if ($request->user()) {
        return $request->user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('pos.index');
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::delete('/reports/{order}', [ReportController::class, 'destroy'])
            ->whereNumber('order')
            ->name('reports.destroy');

        Route::resource('categories', CategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('products', ProductController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::patch('products/{product}/toggle-availability', [ProductController::class, 'toggleAvailability'])
            ->name('products.toggle-availability');

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });

Route::middleware(['auth', 'role:kasir,admin'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/orders', [PosController::class, 'store'])->name('pos.store');
    Route::get('/orders/queue', [OrderQueueController::class, 'index'])->name('orders.queue');
    Route::patch('/orders/{order}/complete', [OrderQueueController::class, 'complete'])->whereNumber('order')->name('orders.complete');
    Route::get('/orders/{order}/receipt', [PosController::class, 'receipt'])
        ->whereNumber('order')
        ->name('orders.receipt');
});

Route::get('/dashboard', function (Request $request) {
    return $request->user()?->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('pos.index');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


