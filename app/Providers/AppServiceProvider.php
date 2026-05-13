<?php

namespace App\Providers;

use App\Models\ReturPembelian;
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
        ReturPembelian::observe(ReturPembelianObserver::class);
    }
}
