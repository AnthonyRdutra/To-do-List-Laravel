<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\TokenAuthMiddleware;
use App\Providers\RouteServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */



    public function register(): void
    {
        // Passport::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::aliasMiddleware('token.auth', TokenAuthMiddleware::class);
    }
}
