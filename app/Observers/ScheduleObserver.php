<?php

namespace App\Observers;

use App\Models\Schedule;
use App\Observers\Traits\LogsActivity;

class ScheduleObserver
{
    use LogsActivity;

    /**
     * Handle the Schedule "created" event.
     */
    public function created(Schedule $schedule): void
    {
        $this->logCreationActivity($schedule);
    }

    /**
     * Handle the Schedule "updated" event.
     */
    public function updated(Schedule $schedule): void
    {
        $this->logUpdateActivity($schedule);
    }

    /**
     * Handle the Schedule "deleted" event.
     */
    public function deleted(Schedule $schedule): void
    {
        $this->logDeletionActivity($schedule);
    }

    /**
     * Handle the Schedule "restored" event.
     */
    public function restored(Schedule $schedule): void
    {
        $this->logRestoredActivity($schedule);
    }

    /**
     * Handle the Schedule "force deleted" event.
     */
    public function forceDeleted(Schedule $schedule): void
    {
        $this->logForceDeletedActivity($schedule);
    }
}
