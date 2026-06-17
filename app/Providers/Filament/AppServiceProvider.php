<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FonnteService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FonnteService::class, function ($app) {
            return new FonnteService();
        });
    }

    public function boot(): void {}
}