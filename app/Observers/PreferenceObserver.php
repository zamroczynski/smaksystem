<?php

namespace App\Observers;

use App\Models\Preference;
use App\Observers\Traits\LogsActivity;

class PreferenceObserver
{
    use LogsActivity;

    /**
     * Handle the Preference "created" event.
     */
    public function created(Preference $preference): void
    {
        $this->logCreationActivity($preference);
    }

    /**
     * Handle the Preference "updated" event.
     */
    public function updated(Preference $preference): void
    {
        $this->logUpdateActivity($preference);
    }

    /**
     * Handle the Preference "deleted" event.
     */
    public function deleted(Preference $preference): void
    {
        $this->logDeletionActivity($preference);
    }

    /**
     * Handle the Preference "restored" event.
     */
    public function restored(Preference $preference): void
    {
        $this->logRestoredActivity($preference);
    }

    /**
     * Handle the Preference "force deleted" event.
     */
    public function forceDeleted(Preference $preference): void
    {
        $this->logForceDeletedActivity($preference);
    }
}
