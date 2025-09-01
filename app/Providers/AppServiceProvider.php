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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;

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
        if (Schema::hasTable('roles')) {
            Gate::before(function ($user, $ability) {
                $superAdminRole = config('app.super_admin_role_name', 'Super Admin');
                return $user->hasRole($superAdminRole) ? true : null;
            });
        }
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
