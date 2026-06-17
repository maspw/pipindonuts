<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReturPembelianController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\KasirAuthController;
use App\Http\Controllers\AprioriTestController;
use Illuminate\Http\Request;
Route::get('/apriori', [AprioriTestController::class, 'index'])->name('apriori.index');
Route::get('/kasir/refresh-rules', [KasirController::class, 'refreshRules'])->name('kasir.refresh-rules');
use App\Http\Controllers\JurnalUmumController;

// Route Jurnal Umum Otomatis Pipin Donuts
Route::get('/laporan/jurnal-umum', [JurnalUmumController::class, 'index'])
    ->name('laporan.jurnal_umum');

// Halaman utama langsung diarahkan ke login kasir
Route::get('/', function () {
    return redirect()->route('kasir.login');
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
// ── KASIR — Login & Logout
// ── KASIR — Login & Logout (tidak butuh auth)
Route::prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/login', [KasirAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [KasirAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [KasirAuthController::class, 'logout'])->name('logout');
});

// ── KASIR — Area Terlindungi
Route::middleware(['auth'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/', [KasirController::class, 'index'])->name('index');
    Route::post('/transaksi', [KasirController::class, 'prosesTransaksi'])->name('transaksi');
    Route::get('/struk/{id}', [KasirController::class, 'struk'])->name('struk');
    Route::post('/midtrans/token', [KasirController::class, 'midtransToken'])->name('midtrans.token');
});
});

