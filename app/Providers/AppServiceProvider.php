<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use App\Services\ApartmentService;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ApartmentService::class, function ($app) {
            return new ApartmentService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        //
        JsonResource::withoutWrapping();

    }
}
