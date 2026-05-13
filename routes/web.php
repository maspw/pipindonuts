<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReturPembelianController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\KasirAuthController;

Route::get('/', function () {
    return redirect()->route('kasir.index');
    return view('welcome');
});

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

// ── KASIR — Login & Logout (tidak butuh auth) ──────────────────────────
Route::prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/login', [KasirAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [KasirAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [KasirAuthController::class, 'logout'])->name('logout');
});

// ── KASIR — Area Terlindungi (butuh role Kasir / Admin) ────────────────
Route::middleware(['kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/', [KasirController::class, 'index'])->name('index');
    Route::post('/transaksi', [KasirController::class, 'prosesTransaksi'])->name('transaksi');
    Route::get('/struk/{id}', [KasirController::class, 'struk'])->name('struk');
    Route::post('/midtrans/token', [KasirController::class, 'midtransToken'])->name('midtrans.token');
});