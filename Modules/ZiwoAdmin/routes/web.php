<?php

use Illuminate\Support\Facades\Route;
use Modules\ZiwoAdmin\Http\Controllers\ZiwoAdminController;

Route::prefix('ziwo')->name('ziwo.')->group(function () {
    Route::get('/login', [ZiwoAdminController::class, 'loginForm'])->name('login');
    Route::post('/login', [ZiwoAdminController::class, 'login'])->name('login.post');

    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [ZiwoAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/statistics', [ZiwoAdminController::class, 'statistics'])->name('statistics');
        Route::get('/export/{format}', [ZiwoAdminController::class, 'export'])->name('export');
    });
});
