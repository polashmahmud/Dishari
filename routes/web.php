<?php

use Illuminate\Support\Facades\Route;
use Polashmahmud\Dishari\Http\Controllers\MenuController;

Route::middleware(['auth'])->prefix('menu-management')->name('menu-management.')->group(function () {
    Route::get('/', [MenuController::class, 'index'])->name('index');
    Route::post('/', [MenuController::class, 'store'])->name('store');
    Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
    Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');
    Route::post('/update-order', [MenuController::class, 'updateOrder'])->name('update-order');

    // Group Routes
    Route::post('/groups', [MenuController::class, 'storeGroup'])->name('groups.store');
    Route::put('/groups/{group}', [MenuController::class, 'updateGroup'])->name('groups.update');
    Route::delete('/groups/{group}', [MenuController::class, 'destroyGroup'])->name('groups.destroy');
});
