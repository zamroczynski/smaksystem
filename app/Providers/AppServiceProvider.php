<?php

namespace App\Providers;

use App\Models\Holiday;
use App\Observers\HolidayObserver;
use Illuminate\Support\ServiceProvider;

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
        Holiday::observe(HolidayObserver::class);
    }
}
