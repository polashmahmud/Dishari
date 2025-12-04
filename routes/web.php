<?php

use Illuminate\Support\Facades\Route;
use Polashmahmud\Menu\Http\Controllers\MenuController;

Route::middleware(['auth'])->prefix('menu-management')->name('menu-management.')->group(function () {
    Route::get('/', [MenuController::class, 'index'])->name('index');
    Route::post('/', [MenuController::class, 'store'])->name('store');
    Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
    Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');
    Route::post('/update-order', [MenuController::class, 'updateOrder'])->name('update-order');
});
