<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Http\Requests\IndexHolidayRequest;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Services\HolidayService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use App\Helpers\BreadcrumbsGenerator;

class HolidayController extends Controller
{
    protected $holidayService;

    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(IndexHolidayRequest $request): Response
    {
        $validatedData = $request->validated();

        $holidays = $this->holidayService->getHolidaysForIndex($validatedData);

        return Inertia::render('Holidays/Index', [
            'holidays' => $holidays,
            'filter' => $validatedData['filter'] ?? null,
            'show_archived' => (bool)($validatedData['show_archived'] ?? false),
            'sort_by' => 'name',
            'sort_direction' => $validatedData['direction'] ?? 'asc',
            'breadcrumbs' => BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
                ->add('Dni Wolne', route('holidays.index'))
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $baseHolidays = Holiday::whereNotNull('day_month')
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

        return Inertia::render('Holidays/Create', [
            'baseHolidays' => $baseHolidays,
            'breadcrumbs' => BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
                ->add('Dni Wolne', route('holidays.index'))
                ->add('Dodaj nowy', route('holidays.create'))
                ->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHolidayRequest $request)
    {
        Holiday::create($request->validated());

        return to_route('holidays.index')->with('success', 'Dzień wolny został pomyślnie dodany.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $Holiday)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $Holiday)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHolidayRequest $request, Holiday $Holiday)
    {
        $Holiday->update($request->validated());

        return to_route('holidays.index')->with('success', 'Dzień wolny został pomyślnie zaktualizowany.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $Holiday)
    {
        $Holiday->delete();

        return to_route('holidays.index')->with('success', 'Dzień wolny został pomyślnie zarchiwizowany.');
    }

    /**
     * Restore the specified soft-deleted day.
     */
    public function restore(Request $request, string $id): RedirectResponse
    {
        $holiday = Holiday::onlyTrashed()->findOrFail($id);
        $holiday->restore();
        return to_route('holidays.index')->with('success', 'Dzień wolny został pomyślnie przywrócony.');
    }
}
