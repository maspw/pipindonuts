<?php

namespace App\Providers;

use App\Models\DetilPembelian;
use App\Models\ReturPembelian;
use App\Observers\DetilPembelianObserver;
use App\Observers\ReturPembelianObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        DetilPembelian::observe(DetilPembelianObserver::class);
        ReturPembelian::observe(ReturPembelianObserver::class);
    }
}
