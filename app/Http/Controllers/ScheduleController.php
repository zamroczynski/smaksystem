<?php

namespace App\Http\Controllers;

use App\Helpers\BreadcrumbsGenerator;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\Schedule;
use App\Models\ShiftTemplate;
use App\Models\HolidayInstance;
use App\Services\ScheduleService;
use App\Services\HolidayService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    protected ScheduleService $scheduleService;

    /**
     * Constructor to inject the ScheduleService.
     */
    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $showArchived = $request->boolean('show_archived');
        $schedules = $this->scheduleService->getSchedulesForIndex($showArchived);
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Grafiki Pracy', route('schedules.index'))
            ->get();

        return Inertia::render('Schedules/Index', [
            'schedules' => $schedules,
            'show_archived' => $showArchived,
            'flash' => session('flash'),
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $activeShiftTemplates = ShiftTemplate::all();
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Grafiki Pracy', route('schedules.index'))
            ->add('Dodaj Grafik Pracy', route('schedules.create'))
            ->get();

        return Inertia::render('Schedules/Create', [
            'activeShiftTemplates' => $activeShiftTemplates,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScheduleRequest $request, HolidayService $holidayService)
    {
        $validatedData = $request->validated();

        try {
            $targetYear = Carbon::parse($validatedData['period_start_date'])->year;

            $holidaysExist = HolidayInstance::whereYear('date', $targetYear)->exists();

            if (! $holidaysExist) {
                $holidayService->generateForYear($targetYear);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Wystąpił błąd podczas przygotowywania dni wolnych. Spróbuj ponownie.');
        }

        $schedule = Schedule::create($validatedData);
        if ($request->has('selected_shift_templates') && is_array($request->selected_shift_templates)) {
            $schedule->shiftTemplates()->attach($request->selected_shift_templates);
        }

        return to_route('schedules.index')->with('success', 'Grafik pracy został pomyślnie utworzony.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule)
    {
        if ($schedule->status !== 'draft') {
            return redirect()->route('schedules.index')->with('error', 'Opublikowany lub zarchiwizowany grafik nie może być edytowany.');
        }

        $scheduleData = $this->scheduleService->getScheduleDetailsForEdit($schedule);

        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Grafiki Pracy', route('schedules.index'))
            ->add('Edycja: '.$schedule->name, route('schedules.edit', $schedule->id))
            ->get();

        return Inertia::render('Schedules/Edit', [
            'schedule' => $scheduleData['schedule'],
            'assignedShiftTemplates' => $scheduleData['assignedShiftTemplates'],
            'users' => $scheduleData['users'],
            'initialAssignments' => $scheduleData['initialAssignments'],
            'monthDays' => $scheduleData['monthDays'],
            'preferences' => $scheduleData['preferences'],
            'breadcrumbs' => $breadcrumbs,
            'flash' => session('flash'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        if ($schedule->status !== 'draft') {
            return redirect()->route('schedules.index')->with('error', 'Opublikowany lub zarchiwizowany grafik nie może być edytowany.');
        }

        $validatedData = $request->validated();

        $schedule->update([
            'name' => $validatedData['name'],
            'period_start_date' => $validatedData['period_start_date'],
            'status' => $validatedData['status'],
        ]);

        $this->scheduleService->updateScheduleAssignments($schedule, $validatedData['assignments'] ?? []);

        return redirect()->route('schedules.edit', $schedule)->with('flash', ['success' => 'Grafik pracy zaktualizowany pomyślnie.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->update(['status' => 'archived']);
        $schedule->delete();

        return back()->with('success', 'Grafik pracy został pomyślnie zarchiwizowany.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        $schedule = Schedule::withTrashed()->findOrFail($id);
        $schedule->restore();
        $schedule->update(['status' => 'draft']);

        return back()->with('success', 'Grafik pracy został pomyślnie przywrócony.');
    }

    /**
     * Publish the specified schedule.
     */
    public function publish(Schedule $schedule)
    {
        $schedule->update(['status' => 'published']);

        return back()->with('success', 'Grafik pracy został pomyślnie opublikowany.');
    }

    /**
     * Set the specified schedule back to draft status.
     */
    public function unpublish(Schedule $schedule)
    {
        $schedule->update(['status' => 'draft']);

        return back()->with('success', 'Grafik pracy został przestawiony na status roboczy.');
    }
}
