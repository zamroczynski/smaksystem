<?php

namespace App\Services;

use App\Models\ShiftTemplate;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShiftTemplateService
{
    /**
     * Downloads paginated shift schedules with filtering and sorting options.
     */
    public function getPaginatedShiftTemplates(array $options): LengthAwarePaginator
    {
        $query = ShiftTemplate::query();

        if ($options['show_deleted']) {
            $query->onlyTrashed();
        }

        if (!empty($options['filter'])) {
            $query->where('name', 'ILIKE', '%' . $options['filter'] . '%');
        }

        $query->orderBy($options['sort'], $options['direction']);

        $shiftTemplates = $query->paginate(10)->appends($options);

        $shiftTemplates->through(function ($shiftTemplate) {
            return [
                'id' => $shiftTemplate->id,
                'name' => $shiftTemplate->name,
                'time_from' => Carbon::parse($shiftTemplate->time_from)->format('H:i'),
                'time_to' => Carbon::parse($shiftTemplate->time_to)->format('H:i'),
                'duration_hours' => number_format($shiftTemplate->duration_hours, 2),
                'required_staff_count' => $shiftTemplate->required_staff_count,
                'deleted_at' => $shiftTemplate->deleted_at ? $shiftTemplate->deleted_at->format('Y-m-d H:i') : null,
            ];
        });

        return $shiftTemplates;
    }
}