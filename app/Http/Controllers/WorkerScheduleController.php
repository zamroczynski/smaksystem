<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ScheduleService;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class WorkerScheduleController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Display a listing of published and archived schedules for workers.
     */
    public function index(Request $request)
    {
        $schedules = $this->scheduleService->getPublishedAndArchivedSchedules(
            $request->input('per_page', 10)
        );

        return Inertia::render('Schedules/ViewSchedules', [
            'schedules' => $schedules,
            'flash' => session('flash'),
            'can_view_my_schedule' => Auth::check(),
        ]);
    }

    /**
     * Display the specified schedule for workers (full view or filtered by current user).
     */
    public function show(Schedule $schedule, Request $request)
    {
        if (!in_array($schedule->status, ['published', 'archived']) && !$schedule->trashed()) {
            abort(403, 'Ten grafik nie jest dostępny do podglądu.');
        }

        $viewType = ($request->has('my') && $request->boolean('my') && Auth::check()) ? 'my' : 'full';

        $scheduleData = $this->scheduleService->getScheduleDetailsForWorkerShow(
            $schedule,
            $viewType === 'my' ? Auth::id() : null
        );

        return Inertia::render('Schedules/ShowSchedule', [
            'scheduleData' => $scheduleData,
            'breadcrumbs' => [
                ['title' => 'Panel nawigacyjny', 'href' => '/dashboard'],
                ['title' => 'Grafiki Pracy', 'href' => route('employee.schedules.index')],
                ['title' => $schedule->name, 'href' => '#'],
            ],
        ]);
    }

    /**
     * Download the full schedule as PDF.
     */
    public function downloadFullPdf(Schedule $schedule)
    {
        if (!in_array($schedule->status, ['published', 'archived']) && !$schedule->trashed()) {
            abort(403, 'Ten grafik nie jest dostępny do pobrania.');
        }

        $data = $this->scheduleService->getSchedulePdfData($schedule, 'full', null);

        $pdf = Pdf::loadView('pdfs.schedule_full', $data);

        $filename = 'grafik_pracy_' . $schedule->name . '_' . $schedule->period_start_date->format('Y-m') . '_caly.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download the user's personal schedule as PDF.
     */
    public function downloadMyPdf(Schedule $schedule)
    {
        if (!Auth::check()) {
            abort(403, 'Musisz być zalogowany, aby pobrać swój grafik.');
        }

        if (!in_array($schedule->status, ['published', 'archived']) && !$schedule->trashed()) {
            abort(403, 'Ten grafik nie jest dostępny do pobrania.');
        }

        $userId = Auth::id();
        $data = $this->scheduleService->getSchedulePdfData($schedule, 'full', $userId);

        $pdf = Pdf::loadView('pdfs.schedule_my', $data);

        $user = Auth::user();
        $filename = 'grafik_pracy_' . $schedule->name . '_' . $user->name . '_' . $schedule->period_start_date->format('Y-m') . '_moj.pdf';

        return $pdf->download($filename);
    }
}
