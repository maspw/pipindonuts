<?php

namespace App\Providers;

use App\Models\DetilPembelian;
use App\Observers\DetilPembelianObserver;
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
    }
}
