<?php

namespace App\Observers;

use App\Models\ScheduleAssignment;
use App\Observers\Traits\LogsActivity;

class ScheduleAssignmentObserver
{
    use LogsActivity;

    /**
     * Handle the ScheduleAssignment "created" event.
     */
    public function created(ScheduleAssignment $scheduleAssignment): void
    {
        $this->logCreationActivity($scheduleAssignment);
    }

    /**
     * Handle the ScheduleAssignment "updated" event.
     */
    public function updated(ScheduleAssignment $scheduleAssignment): void
    {
        $this->logUpdateActivity($scheduleAssignment);
    }

    /**
     * Handle the ScheduleAssignment "deleted" event.
     */
    public function deleted(ScheduleAssignment $scheduleAssignment): void
    {
        $this->logDeletionActivity($scheduleAssignment);
    }

    /**
     * Handle the ScheduleAssignment "restored" event.
     */
    public function restored(ScheduleAssignment $scheduleAssignment): void
    {
        $this->logRestoredActivity($scheduleAssignment);
    }

    /**
     * Handle the ScheduleAssignment "force deleted" event.
     */
    public function forceDeleted(ScheduleAssignment $scheduleAssignment): void
    {
        $this->logForceDeletedActivity($scheduleAssignment);
    }
}
