<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReturPembelianController;

Route::get('/halo', function () {
    return view('welcome');
});

Route::get('/supplier', [SupplierController::class, 'index']);
Route::get('/supplier/create', [SupplierController::class, 'create']);
Route::post('/supplier', [SupplierController::class, 'store']);
Route::get('/depan', [App\Http\Controllers\KeranjangController::class, 'daftarbarang'])
    ->middleware('customer')
    ->name('depan');

// Export routes (proteksi auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/export/retur-pembelian/pdf', [ReturPembelianController::class, 'exportPdf'])
        ->name('retur.export.pdf');
    Route::get('/export/retur-pembelian/csv', [ReturPembelianController::class, 'exportCsv'])
        ->name('retur.export.csv');
});