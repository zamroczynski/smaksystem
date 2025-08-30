<?php

namespace App\Services;

use App\Models\HolidayInstance;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HolidayService
{
    private ?Carbon $easterSunday = null;

    /**
     * Generates and saves holiday instances for the specified year.
     * This is an idempotent operation—it will delete existing entries for the year before generating new ones.
     */
    public function generateForYear(int $year): void
    {
        HolidayInstance::whereYear('date', $year)->delete();

        $definitions = Holiday::all();

        $holidayInstances = $this->calculateDates($definitions, $year);

        if ($holidayInstances->isNotEmpty()) {
            HolidayInstance::insert($holidayInstances->toArray());
        }
    }

    private function calculateDates(Collection $definitions, int $year): Collection
    {
        $instances = collect();

        $calculatedDatesMap = collect();

        // --- STEP 1: Calculate the dates of independent holidays (fixed and Easter-based) ---
        foreach ($definitions as $definition) {
            $date = null;

            if ($definition->day_month) {
                $date = Carbon::createFromFormat('m-d-Y', substr($definition->day_month, 0, 2) . '-' . substr($definition->day_month, 3, 2) . '-' . $year)->startOfDay();
            } elseif (
                isset($definition->calculation_rule['base_type']) &&
                $definition->calculation_rule['base_type'] === 'event' &&
                $definition->calculation_rule['base_event'] === 'easter'
            ) {
                $easter = $this->getEasterSunday($year);
                $date = (clone $easter)->addDays($definition->calculation_rule['offset'] ?? 0);
            }

            if ($date) {
                $calculatedDatesMap->put($definition->id, $date);
            }
        }

        // --- STEP 2: Calculate the dates of holidays that depend on other holidays ---
        foreach ($definitions as $definition) {
            if (
                isset($definition->calculation_rule['base_type']) &&
                $definition->calculation_rule['base_type'] === 'holiday'
            ) {
                $rule = $definition->calculation_rule;
                $baseHolidayId = $rule['base_holiday_id'] ?? null;

                if ($baseHolidayId && $calculatedDatesMap->has($baseHolidayId)) {
                    $baseDate = $calculatedDatesMap->get($baseHolidayId);
                    $date = (clone $baseDate)->addDays($rule['offset'] ?? 0);

                    $calculatedDatesMap->put($definition->id, $date);
                }
            }
        }

        // --- STEP 3: Get your final data ready to save ---
        foreach ($definitions as $definition) {
             if ($calculatedDatesMap->has($definition->id)) {
                 $instances->push([
                    'name' => $definition->name,
                    'date' => $calculatedDatesMap->get($definition->id)->toDateString(),
                    'holiday_definition_id' => $definition->id,
                ]);
             }
        }

        return $instances;
    }

    /**
     * Calculates the date of Easter Sunday and buffers the result.
     */
    private function getEasterSunday(int $year): Carbon
    {
        if ($this->easterSunday && $this->easterSunday->year === $year) {
            return $this->easterSunday;
        }

        $date = easter_date($year);
        $this->easterSunday = Carbon::createFromTimestamp($date)->startOfDay();

        return $this->easterSunday;
    }

    /**
     * Retrieves a paginated and filtered list of days off for the index view.
     */
    public function getHolidaysForIndex(array $validatedData): LengthAwarePaginator
    {
        $query = Holiday::query();

        $query->when($validatedData['show_archived'] ?? false, function ($q) {
            $q->onlyTrashed();
        });

        $query->when($validatedData['filter'] ?? null, function ($q, $filter) {
            $q->where(function ($subQuery) use ($filter) {
                $subQuery->where('name', 'like', "%{$filter}%")
                         ->orWhere('date', 'like', "%{$filter}%")
                         ->orWhere('day_month', 'like', "%{$filter}%");
            });
        });

        $query->orderBy(
            'name',
            $validatedData['direction'] ?? 'asc'
        );

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Refactored private method to fetch base holidays.
     */
    public function getBaseHolidays()
    {
        return Holiday::whereNotNull('day_month')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    /**
     * Synchronizes a single holiday definition with the holiday_instances table.
     * It recalculates its occurrences for the current and next year.
     *
     * @param Holiday $holiday The holiday definition that was changed.
     */
    public function syncHolidayInstances(Holiday $holiday): void
    {
        $currentYear = now()->year;
        $yearsToSync = [$currentYear, $currentYear + 1];

        HolidayInstance::where('holiday_definition_id', $holiday->id)->delete();

        if ($holiday->trashed()) {
            return;
        }

        foreach ($yearsToSync as $year) {
            $date = $this->calculateSingleDate($holiday, $year);

            if ($date) {
                HolidayInstance::updateOrCreate(
                    [
                        'date' => $date->toDateString(),
                        'holiday_definition_id' => $holiday->id,
                    ],
                    [
                        'name' => $holiday->name,
                    ]
                );
            }
        }
    }

    /**
     * Calculates the date for a single holiday definition for a given year.
     * This is a refactored part of the logic from calculateDates.
     *
     * @param Holiday $definition
     * @param int $year
     * @return Carbon|null
     */
    private function calculateSingleDate(Holiday $definition, int $year): ?Carbon
    {
        if ($definition->day_month) {
            return Carbon::createFromFormat('m-d-Y', substr($definition->day_month, 0, 2) . '-' . substr($definition->day_month, 3, 2) . '-' . $year)->startOfDay();
        }
        
        if ($definition->calculation_rule) {
             $rule = $definition->calculation_rule;
             if ($rule['base_type'] === 'event' && $rule['base_event'] === 'easter') {
                 return (clone $this->getEasterSunday($year))->addDays($rule['offset'] ?? 0);
             }
        }

        if ($definition->date && Carbon::parse($definition->date)->year === $year) {
            return Carbon::parse($definition->date);
        }

        return null;
    }
}