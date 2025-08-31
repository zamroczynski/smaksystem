<?php

namespace App\Observers;

use App\Models\User;
use App\Observers\Traits\LogsActivity;

class UserObserver
{
    use LogsActivity;

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->logCreationActivity($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $this->logUpdateActivity($user);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->logDeletionActivity($user);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        $this->logRestoredActivity($user);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $this->logForceDeletedActivity($user);
    }
}
