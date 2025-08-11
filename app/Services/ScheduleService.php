<?php

namespace App\Services;

use App\Models\Preference;
use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleService
{
    /**
     * Prepare data for the schedule index view.
     */
    public function getSchedulesForIndex(bool $showArchived, int $perPage = 10): LengthAwarePaginator
    {
        $query = Schedule::query();

        if ($showArchived) {
            $query->onlyTrashed();
        } else {
            $query->where('status', '!=', 'archived');
        }

        $schedules = $query->orderByDesc('period_start_date')
            ->orderBy('name')
            ->paginate($perPage)
            ->appends([
                'show_archived' => $showArchived,
            ]);

        return $schedules->through(function ($schedule) {
            return [
                'id' => $schedule->id,
                'name' => $schedule->name,
                'period_start_date' => Carbon::parse($schedule->period_start_date)->format('Y-m-d'),
                'status' => $schedule->status,
                'created_at' => $schedule->created_at->format('Y-m-d H:i'),
                'updated_at' => $schedule->updated_at->format('Y-m-d H:i'),
                'deleted_at' => $schedule->deleted_at ? $schedule->deleted_at->format('Y-m-d H:i') : null,
            ];
        });
    }

    /**
     * Prepare data for the schedule edit view.
     */
    public function getScheduleDetailsForEdit(Schedule $schedule): array
    {
        $schedule->load([
            'shiftTemplates' => function ($query) {
                $query->select('shift_templates.id', 'name', 'time_from', 'time_to', 'duration_hours', 'required_staff_count');
            },
            'assignments' => function ($query) {
                $query->select('schedule_id', 'shift_template_id', 'user_id', 'assignment_date', 'position');
            },
        ]);

        $users = User::select('id', 'name')->orderBy('name')->get();

        $startDate = Carbon::parse($schedule->period_start_date);
        $endDate = $startDate->copy()->endOfMonth();

        $monthDays = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $monthDays[] = [
                'date' => $date->format('Y-m-d'),
                'day_number' => $date->day,
                'is_sunday' => $date->isSunday(),
                'is_saturday' => $date->isSaturday(),
                'is_holiday' => false, // TODO: Implement holiday check if needed
            ];
        }

        $assignments = [];
        foreach ($schedule->assignments as $assignment) {
            $key = $assignment->shift_template_id.'_'.$assignment->assignment_date->format('Y-m-d').'_'.$assignment->position;
            $assignments[$key] = $assignment->user_id;
        }

        $preferences = Preference::query()
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date_from', [$startDate, $endDate])
                    ->orWhereBetween('date_to', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('date_from', '<', $startDate)
                            ->where('date_to', '>', $endDate);
                    });
            })
            ->select('user_id', 'date_from', 'date_to', 'availability')
            ->get()
            ->groupBy('user_id');

        $formattedPreferences = [];
        foreach ($preferences as $userId => $userPreferences) {
            foreach ($userPreferences as $preference) {
                $currentDate = Carbon::parse($preference->date_from);
                while ($currentDate->lte(Carbon::parse($preference->date_to))) {
                    $formattedPreferences[$userId][$currentDate->format('Y-m-d')] = $preference->availability;
                    $currentDate->addDay();
                }
            }
        }
        // ------------------------------------------------

        return [
            'schedule' => [
                'id' => $schedule->id,
                'name' => $schedule->name,
                'period_start_date' => $schedule->period_start_date->format('Y-m-d'),
                'status' => $schedule->status,
            ],
            'assignedShiftTemplates' => $schedule->shiftTemplates->toArray(),
            'users' => $users->toArray(),
            'initialAssignments' => $assignments,
            'monthDays' => $monthDays,
            'preferences' => $formattedPreferences,
        ];
    }

    /**
     * Update schedule details and assignments.
     *
     * @param  array  $validatedData  Validated data for the schedule (name, period_start_date, status)
     * @param  array  $newAssignments  New assignments data
     */
    public function updateScheduleAssignments(Schedule $schedule, array $newAssignments): void
    {
        DB::transaction(function () use ($schedule, $newAssignments) {
            ScheduleAssignment::where('schedule_id', $schedule->id)->delete();

            $assignmentsToInsert = [];

            foreach ($newAssignments as $assignmentData) {
                if (
                    isset($assignmentData['shift_template_id']) &&
                    isset($assignmentData['assignment_date']) &&
                    isset($assignmentData['position']) &&
                    isset($assignmentData['user_id'])
                ) {
                    $assignmentsToInsert[] = [
                        'schedule_id' => $schedule->id,
                        'shift_template_id' => (int) $assignmentData['shift_template_id'],
                        'user_id' => $assignmentData['user_id'] !== null ? (int) $assignmentData['user_id'] : null,
                        'assignment_date' => $assignmentData['assignment_date'],
                        'position' => (int) $assignmentData['position'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                } else {
                    Log::warning('Skipping invalid assignment data structure: '.json_encode($assignmentData));
                }
            }

            if (! empty($assignmentsToInsert)) {
                ScheduleAssignment::insert($assignmentsToInsert);
            }
        });
    }

    /**
     * Get published and archived schedules for worker view.
     */
    public function getPublishedAndArchivedSchedules(int $perPage = 10): LengthAwarePaginator
    {
        return Schedule::query()
            ->whereIn('status', ['published', 'archived'])
            ->orderByDesc('period_start_date')
            ->orderBy('name')
            ->paginate($perPage)
            ->through(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'name' => $schedule->name,
                    'period_start_date' => Carbon::parse($schedule->period_start_date)->format('Y-m-d'),
                    'status' => $schedule->status,
                ];
            });
    }

    /**
     * Get detailed schedule data for worker's view.
     *
     * @param  int|null  $userId  Optional: User ID to filter assignments.
     */
    public function getScheduleDetailsForWorkerShow(Schedule $schedule, ?int $userId = null): array
    {
        $schedule->load([
            'shiftTemplates' => function ($query) {
                $query->select('shift_templates.id', 'name', 'time_from', 'time_to', 'required_staff_count');
            },
            'assignments' => function ($query) use ($userId) {
                $query->select('schedule_id', 'shift_template_id', 'user_id', 'assignment_date', 'position')
                    ->with('user:id,name');
                if ($userId) {
                    $query->where('user_id', $userId);
                }
            },
        ]);

        $users = User::select('id', 'name')->get();

        $startDate = Carbon::parse($schedule->period_start_date);
        $endDate = $startDate->copy()->endOfMonth();
        $monthDays = [];
        Carbon::setLocale('pl');

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $monthDays[] = [
                'date' => $date->format('Y-m-d'),
                'day_number' => $date->day,
                'day_name_short' => $date->dayName,
                'is_sunday' => $date->isSunday(),
                'is_saturday' => $date->isSaturday(),
                'is_holiday' => false,
            ];
        }

        $assignments = $schedule->assignments->groupBy(function ($assignment) {
            return "{$assignment->shift_template_id}_{$assignment->assignment_date->format('Y-m-d')}_{$assignment->position}";
        })->map(function ($groupedAssignments) {
            return $groupedAssignments->map(function ($assignment) {
                return [
                    'user_id' => $assignment->user_id,
                    'user_name' => $assignment->user->name,
                ];
            });
        })->toArray();

        return [
            'schedule' => [
                'id' => $schedule->id,
                'name' => $schedule->name,
                'period_start_date' => $schedule->period_start_date->format('Y-m-d'),
                'status' => $schedule->status,
            ],
            'shiftTemplates' => $schedule->shiftTemplates,
            'users' => $users,
            'assignments' => $assignments,
            'monthDays' => $monthDays,
            'view_mode' => $userId ? 'my' : 'full',
        ];
    }

    /**
     * Get schedule data for PDF generation.
     * This method will be similar to getScheduleDetailsForWorkerShow but optimized for PDF.
     * We'll eager load all necessary data at once.
     */
    public function getSchedulePdfData(Schedule $schedule, string $viewType, ?int $userId): array
    {
        $schedule->load([
            'shiftTemplates' => function ($query) {
                $query->select('shift_templates.id', 'name', 'time_from', 'time_to', 'required_staff_count');
            },
            'assignments' => function ($query) use ($userId) {
                $query->select('schedule_id', 'shift_template_id', 'user_id', 'assignment_date', 'position')
                    ->with('user:id,name');
                if ($userId) {
                    $query->where('user_id', $userId);
                }
            },
        ]);

        $users = User::select('id', 'name')->get();

        $startDate = Carbon::parse($schedule->period_start_date);
        $endDate = $startDate->copy()->endOfMonth();
        $monthDays = [];
        Carbon::setLocale('pl');

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $monthDays[] = [
                'date' => $date->format('Y-m-d'),
                'day_number' => $date->day,
                'day_name_short' => $date->dayName,
                'is_sunday' => $date->isSunday(),
                'is_saturday' => $date->isSaturday(),
                'is_holiday' => false,
            ];
        }

        $transformedAssignments = [];
        foreach ($schedule->assignments as $assignment) {
            $key = $assignment->shift_template_id.'_'.$assignment->assignment_date->format('Y-m-d').'_'.$assignment->position;
            $transformedAssignments[$key][] = [
                'user_id' => $assignment->user->id,
                'user_name' => $assignment->user->name,
            ];
        }

        return [
            'schedule' => $schedule->toArray(),
            'shiftTemplates' => $schedule->shiftTemplates->toArray(),
            'users' => $users->toArray(),
            'assignments' => $transformedAssignments,
            'monthDays' => $monthDays,
            'view_type' => $viewType,
            'auth_user_id' => $userId,
        ];
    }
}
