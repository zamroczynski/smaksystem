<?php

namespace App\Observers;

use App\Models\ShiftTemplate;
use App\Observers\Traits\LogsActivity;

class ShiftTemplateObserver
{
    use LogsActivity;

    /**
     * Handle the ShiftTemplate "created" event.
     */
    public function created(ShiftTemplate $shiftTemplate): void
    {
        $this->logCreationActivity($shiftTemplate);
    }

    /**
     * Handle the ShiftTemplate "updated" event.
     */
    public function updated(ShiftTemplate $shiftTemplate): void
    {
        $this->logUpdateActivity($shiftTemplate);
    }

    /**
     * Handle the ShiftTemplate "deleted" event.
     */
    public function deleted(ShiftTemplate $shiftTemplate): void
    {
        $this->logDeletionActivity($shiftTemplate);
    }

    /**
     * Handle the ShiftTemplate "restored" event.
     */
    public function restored(ShiftTemplate $shiftTemplate): void
    {
        $this->logRestoredActivity($shiftTemplate);
    }

    /**
     * Handle the ShiftTemplate "force deleted" event.
     */
    public function forceDeleted(ShiftTemplate $shiftTemplate): void
    {
        $this->logForceDeletedActivity($shiftTemplate);
    }
}
