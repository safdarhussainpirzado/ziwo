<?php

use Illuminate\Support\Facades\Route;
use Modules\ZiwoAdmin\Http\Controllers\ZiwoAdminController;

Route::prefix('ziwo')->name('ziwo.')->group(function () {
    Route::get('/login', [ZiwoAdminController::class, 'loginForm'])->name('login');
    Route::post('/login', [ZiwoAdminController::class, 'login'])->name('login.post');

    Route::middleware(['auth'])->group(function () {
        Route::get('/export/{format}', [ZiwoAdminController::class, 'export'])->name('export');
    });
});
