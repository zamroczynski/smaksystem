<?php

namespace App\Observers;

use App\Models\Role;
use App\Observers\Traits\LogsActivity;

class RoleObserver
{
    use LogsActivity;

    /**
     * Handle the Role "created" event.
     */
    public function created(Role $role): void
    {
        $this->logCreationActivity($role);
    }

    /**
     * Handle the Role "updated" event.
     */
    public function updated(Role $role): void
    {
        $this->logUpdateActivity($role);
    }

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(Role $role): void
    {
        $this->logDeletionActivity($role);
    }

    /**
     * Handle the Role "restored" event.
     */
    public function restored(Role $role): void
    {
        $this->logRestoredActivity($role);
    }

    /**
     * Handle the Role "force deleted" event.
     */
    public function forceDeleted(Role $role): void
    {
        $this->logForceDeletedActivity($role);
    }
}
