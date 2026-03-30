<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use Illuminate\Support\Facades\Route;

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', "show")->name('login');
    Route::post('/login', "login");
    Route::post("/logout", "logout")->name('logout');
});

Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/', 'index')->name('dashboard.index');
    });

    Route::controller(ItemController::class)->group(function () {
        Route::get('/barang', 'index')->name('barang.index');
        Route::post('/barang', 'store')->name('barang.store');
        Route::put('/barang/{id}', 'update')->name('barang.update');
        Route::delete('/barang/{id}', 'destroy')->name('barang.destroy');
    });

    Route::controller(StockInController::class)->group(function () {
        Route::get("/barang_masuk", 'index')->name('barang_masuk.index');
        Route::post("/barang_masuk", 'store')->name('barang_masuk.store');
    });

    Route::controller(StockOutController::class)->group(function () {
        Route::get("/barang_keluar", 'index')->name('barang_keluar.index');
        Route::post("/barang_keluar", 'store')->name('barang_keluar.store');
    });

    Route::controller(ReportController::class)->group(function () {
        Route::get("/laporan", 'index')->name('laporan.index');
        Route::get('/laporan/print', 'print')->name('laporan.print');
    });
});
