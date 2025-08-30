<?php

namespace App\Http\Controllers;

use App\Helpers\BreadcrumbsGenerator;
use App\Http\Requests\IndexHolidayRequest;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Models\Holiday;
use App\Services\HolidayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
            'show_archived' => (bool) ($validatedData['show_archived'] ?? false),
            'sort_by' => 'name',
            'sort_direction' => $validatedData['direction'] ?? 'asc',
            'breadcrumbs' => $this->getHolidayBreadcrumbs(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Holidays/Create', [
            'baseHolidays' => $this->holidayService->getBaseHolidays(),
            'breadcrumbs' => $this->getHolidayBreadcrumbs('Dodaj dzień wolny', route('holidays.create')),
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
    public function show(Holiday $holiday)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $holiday)
    {
        return Inertia::render('Holidays/Edit', [
            'holiday' => $holiday,
            'baseHolidays' => $this->holidayService->getBaseHolidays(),
            'breadcrumbs' => $this->getHolidayBreadcrumbs('Edytuj dzień wolny', route('holidays.edit', $holiday)),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHolidayRequest $request, Holiday $holiday)
    {
        $holiday->update($request->validated());

        return to_route('holidays.index')->with('success', 'Dzień wolny został pomyślnie zaktualizowany.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

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

    /**
     * Generates breadcrumbs for holiday management.
     */
    protected function getHolidayBreadcrumbs(?string $pageTitle = null, ?string $pageRoute = null): array
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Konfiguracja dni wolnych', route('holidays.index'));

        if ($pageTitle && $pageRoute) {
            $breadcrumbs->add($pageTitle, $pageRoute);
        }

        return $breadcrumbs->get();
    }
}
