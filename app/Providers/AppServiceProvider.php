<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Import Model-model Transaksi
use App\Models\PengeluaranOperasional; // Sesuaikan jika nama model Anda PengeluaranOperasional
use App\Models\PembelianBahanbaku;
use App\Models\Pembayaran;
use App\Models\PenjualanProduk;
use App\Models\Produksi;
use App\Models\ReturPembelian;


// Import Observers
use App\Observers\PengeluaranObserver;
use App\Observers\PembelianObserver;
use App\Observers\PembayaranObserver;
use App\Observers\PenjualanObserver;
use App\Observers\ReturObserver;

PenjualanProduk::observe(PenjualanObserver::class);
PembelianBahanbaku::observe(PembelianObserver::class);
ReturPembelian::observe(ReturObserver::class);

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Pendaftaran seluruh Observer Transaksi Otomatis Pipin Donuts
        PengeluaranOperasional::observe(PengeluaranObserver::class);
        PembelianBahanbaku::observe(PembelianObserver::class);
        //Pembayaran::observe(PembayaranObserver::class);
        PenjualanProduk::observe(PenjualanObserver::class);
        ReturPembelian::observe(ReturObserver::class);
    }
}