<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use App\Services\AppService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        // You could bind services here if needed.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || request()->isSecure()) {
            URL::forceScheme('https');
        }
    }
}
