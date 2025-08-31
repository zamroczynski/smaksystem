<?php

namespace App\Observers;

use App\Models\ScheduleShiftTemplate;
use App\Observers\Traits\LogsActivity;

class ScheduleShiftTemplateObserver
{
    use LogsActivity;

    /**
     * Handle the ScheduleShiftTemplate "created" event.
     */
    public function created(ScheduleShiftTemplate $scheduleShiftTemplate): void
    {
        $this->logCreationActivity($scheduleShiftTemplate);
    }

    /**
     * Handle the ScheduleShiftTemplate "updated" event.
     */
    public function updated(ScheduleShiftTemplate $scheduleShiftTemplate): void
    {
        $this->logUpdateActivity($scheduleShiftTemplate);
    }

    /**
     * Handle the ScheduleShiftTemplate "deleted" event.
     */
    public function deleted(ScheduleShiftTemplate $scheduleShiftTemplate): void
    {
        $this->logDeletionActivity($scheduleShiftTemplate);
    }

    /**
     * Handle the ScheduleShiftTemplate "restored" event.
     */
    public function restored(ScheduleShiftTemplate $scheduleShiftTemplate): void
    {
        $this->logRestoredActivity($scheduleShiftTemplate);
    }

    /**
     * Handle the ScheduleShiftTemplate "force deleted" event.
     */
    public function forceDeleted(ScheduleShiftTemplate $scheduleShiftTemplate): void
    {
        $this->logForceDeletedActivity($scheduleShiftTemplate);
    }
}
