<?php

namespace App\Providers;

use App\Models\Brand;
use App\Policies\BrandPolicy;
use App\Services\BrandService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Brand Service
        $this->app->singleton(BrandService::class, function ($app) {
            return new BrandService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Brand Policy
        Gate::policy(Brand::class, BrandPolicy::class);
    }
}
