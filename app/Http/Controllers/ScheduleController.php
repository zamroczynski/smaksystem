<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ShiftTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use Carbon\Carbon;
use App\Services\ScheduleService;

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

        return Inertia::render('Schedules/Index', [
            'schedules' => $schedules,
            'show_archived' => $showArchived,
            'flash' => session('flash'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $activeShiftTemplates = ShiftTemplate::all();

        return Inertia::render('Schedules/Create', [
            'activeShiftTemplates' => $activeShiftTemplates,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreScheduleRequest $request)
    {
        $schedule = Schedule::create($request->validated());
        if ($request->has('selected_shift_templates') && is_array($request->selected_shift_templates)) {
            $schedule->shiftTemplates()->attach($request->selected_shift_templates);
        }

        return to_route('schedules.index')->with('success', 'Grafik pracy został pomyślnie utworzony.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        // Ta metoda może być używana do wyświetlenia szczegółów grafiku (widok pracownika)
        // Na razie zostawimy ją prostą, rozszerzymy ją, gdy będziemy implementować widok pracownika.
        return Inertia::render('Schedules/Show', [
            'schedule' => [
                'id' => $schedule->id,
                'name' => $schedule->name,
                'period_start_date' => Carbon::parse($schedule->period_start_date)->format('Y-m-d'),
                'status' => $schedule->status,
                // Możesz tu również załadować przypisane zmiany i pracowników
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule)
    {
        if ($schedule->status !== 'draft') {
            return redirect()->route('schedules.index')->with('error', 'Opublikowany lub zarchiwizowany grafik nie może być edytowany.');
        }

        $editData = $this->scheduleService->getScheduleEditData($schedule);

        return Inertia::render('Schedules/Edit', $editData);
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
        $newAssignments = $request->input('assignments', []);

        $this->scheduleService->updateScheduleAndAssignments($schedule, $validatedData, $newAssignments);

        return redirect()->route('schedules.index')->with('success', 'Grafik pracy został pomyślnie zaktualizowany.');
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
