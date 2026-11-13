<?php

use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('menu.index');
});

Route::resource('menu', MenuController::class)->except(['create', 'show', 'edit']);