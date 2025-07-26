<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\ShiftTemplate;
use App\Models\ScheduleAssignment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
    public function getScheduleEditData(Schedule $schedule): array
    {
        $assignedShiftTemplates = $schedule->shiftTemplates()
            ->select('shift_templates.id', 'shift_templates.name', 'shift_templates.required_staff_count')
            ->get();

        $users = User::select('id', 'name')
                      ->orderBy('name')
                      ->get();

        $assignments = $schedule->assignments()
            ->select('shift_template_id', 'user_id', 'assignment_date', 'position')
            ->get()
            ->mapWithKeys(function ($assignment) {
                $key = $assignment->shift_template_id . '_' . $assignment->assignment_date->format('Y-m-d') . '_' . $assignment->position;
                return [$key => $assignment->user_id];
            })->toArray();

        $startDate = Carbon::parse($schedule->period_start_date);
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $endDate->day;

        $monthDays = [];
        $holidays = [
            '2025-01-01', '2025-01-06', '2025-04-20', '2025-04-21',
            '2025-05-01', '2025-05-03', '2025-06-08', '2025-06-19',
            '2025-08-15', '2025-11-01', '2025-11-11', '2025-12-25', '2025-12-26',
        ];

        for ($i = 0; $i < $daysInMonth; $i++) {
            $currentDay = $startDate->copy()->addDays($i);
            $monthDays[] = [
                'date' => $currentDay->format('Y-m-d'),
                'day_number' => $currentDay->day,
                'is_sunday' => $currentDay->isSunday(),
                'is_saturday' => $currentDay->isSaturday(),
                'is_holiday' => in_array($currentDay->format('Y-m-d'), $holidays), // Bezpośrednie sprawdzenie
            ];
        }

        return [
            'schedule' => [
                'id' => $schedule->id,
                'name' => $schedule->name,
                'period_start_date' => $schedule->period_start_date->format('Y-m-d'),
                'status' => $schedule->status,
            ],
            'assignedShiftTemplates' => $assignedShiftTemplates,
            'users' => $users,
            'initialAssignments' => $assignments,
            'monthDays' => $monthDays,
        ];
    }

    /**
     * Update schedule details and assignments.
     *
     * @param Schedule $schedule
     * @param array $validatedData Validated data for the schedule (name, period_start_date, status)
     * @param array $newAssignments New assignments data
     * @return void
     */
    public function updateScheduleAndAssignments(Schedule $schedule, array $validatedData, array $newAssignments): void
    {
        $schedule->update($validatedData);

        $schedule->assignments()->delete();

        $assignmentsToInsert = [];
        foreach ($newAssignments as $assignmentData) {
            $assignmentsToInsert[] = [
                'schedule_id' => $schedule->id,
                'shift_template_id' => $assignmentData['shift_template_id'],
                'user_id' => $assignmentData['user_id'],
                'assignment_date' => $assignmentData['assignment_date'],
                'position' => $assignmentData['position'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($assignmentsToInsert)) {
            ScheduleAssignment::insert($assignmentsToInsert);
        }
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
     * @param Schedule $schedule
     * @param int|null $userId Optional: User ID to filter assignments.
     * @return array
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
            $key = $assignment->shift_template_id . '_' . $assignment->assignment_date->format('Y-m-d') . '_' . $assignment->position;
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