<?php

namespace App\Providers;

use App\Models\Holiday;
use App\Models\Preference;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleShiftTemplate;
use App\Models\ShiftTemplate;
use App\Models\User;
use App\Observers\HolidayObserver;
use App\Observers\PreferenceObserver;
use App\Observers\RoleObserver;
use App\Observers\ScheduleAssignmentObserver;
use App\Observers\ScheduleObserver;
use App\Observers\ScheduleShiftTemplateObserver;
use App\Observers\ShiftTemplateObserver;
use App\Observers\UserObserver;
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
        User::observe(UserObserver::class);
        Preference::observe(PreferenceObserver::class);
        Role::observe(RoleObserver::class);
        Schedule::observe(ScheduleObserver::class);
        ScheduleAssignment::observe(ScheduleAssignmentObserver::class);
        ScheduleShiftTemplate::observe(ScheduleShiftTemplateObserver::class);
        ShiftTemplate::observe(ShiftTemplateObserver::class);
    }
}
