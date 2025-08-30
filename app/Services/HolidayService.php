<?php

namespace App\Services;

use App\Models\HolidayInstance;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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

        foreach ($definitions as $definition) {
            $date = null;

            if ($definition->day_month) {
                $date = Carbon::createFromFormat('m-d-Y', substr($definition->day_month, 0, 2) . '-' . substr($definition->day_month, 3, 2) . '-' . $year)->startOfDay();
            } elseif ($definition->calculation_rule) {
                $rule = $definition->calculation_rule;
                if (isset($rule['base']) && $rule['base'] === 'easter') {
                    $easter = $this->getEasterSunday($year);
                    $date = (clone $easter)->addDays($rule['offset'] ?? 0);
                }
            }

            if ($date) {
                $instances->push([
                    'name' => $definition->name,
                    'date' => $date->toDateString(),
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
}