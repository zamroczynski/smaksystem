<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Holiday; 
use App\Observers\HolidayObserver; 

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
