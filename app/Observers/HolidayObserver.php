<?php

namespace App\Observers;

use App\Models\Holiday;
use App\Observers\Traits\LogsActivity;
use App\Services\HolidayService;

class HolidayObserver
{
    use LogsActivity;

    public function __construct(protected HolidayService $holidayService) {}

    /**
     * Handle the Holiday "created" event.
     */
    public function created(Holiday $holiday): void
    {
        $this->holidayService->syncHolidayInstances($holiday);

        $this->logCreationActivity($holiday);
    }

    /**
     * Handle the Holiday "updated" event.
     */
    public function updated(Holiday $holiday): void
    {
        $this->holidayService->syncHolidayInstances($holiday);

        $this->logUpdateActivity($holiday);
    }

    /**
     * Handle the Holiday "deleted" event.
     */
    public function deleted(Holiday $holiday): void
    {
        $this->holidayService->syncHolidayInstances($holiday);

        $this->logDeletionActivity($holiday);
    }

    /**
     * Handle the Holiday "restored" event.
     */
    public function restored(Holiday $holiday): void
    {
        $this->holidayService->syncHolidayInstances($holiday);

        $this->logRestoredActivity($holiday);
    }

    /**
     * Handle the Holiday "force deleted" event.
     */
    public function forceDeleted(Holiday $holiday): void
    {
        $this->holidayService->syncHolidayInstances($holiday);

        $this->logForceDeletedActivity($holiday);
    }
}
