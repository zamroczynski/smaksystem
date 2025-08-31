<?php

namespace App\Http\Controllers;

use App\Helpers\BreadcrumbsGenerator;
use App\Http\Requests\StoreShiftTemplateRequest;
use App\Models\ShiftTemplate;
use App\Services\ShiftTemplateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShiftTemplateController extends Controller
{
    public function __construct(private ShiftTemplateService $shiftTemplateService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $options = [
            'show_deleted' => $request->boolean('show_deleted'),
            'filter' => $request->input('filter'),
            'sort' => $request->input('sort', 'name'),
            'direction' => $request->input('direction', 'asc'),
        ];

        $shiftTemplates = $this->shiftTemplateService->getPaginatedShiftTemplates($options);

        return Inertia::render('ShiftTemplates/Index', [
            'shiftTemplates' => $shiftTemplates,
            'show_deleted' => $options['show_deleted'],
            'flash' => session('flash'),
            'breadcrumbs' => $this->getShiftTemplatesBreadcrumbs(),
            'filter' => $options['filter'],
            'sort_by' => $options['sort'],
            'sort_direction' => $options['direction'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('ShiftTemplates/Create', [
            'breadcrumbs' => $this->getShiftTemplatesBreadcrumbs('Dodaj Harmonogram', route('shift-templates.create')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShiftTemplateRequest $request)
    {
        $validatedData = $request->validated();
        ShiftTemplate::create($validatedData);

        return to_route('shift-templates.index')->with('success', 'Zmiana został pomyślnie dodany.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShiftTemplate $shiftTemplate)
    {
        return Inertia::render('ShiftTemplates/Edit', [
            'shiftTemplate' => [
                'id' => $shiftTemplate->id,
                'name' => $shiftTemplate->name,
                'time_from' => Carbon::parse($shiftTemplate->time_from)->format('H:i'),
                'time_to' => Carbon::parse($shiftTemplate->time_to)->format('H:i'),
                'duration_hours' => number_format($shiftTemplate->duration_hours, 2),
                'required_staff_count' => $shiftTemplate->required_staff_count,
            ],
            'breadcrumbs' => $this->getShiftTemplatesBreadcrumbs('Edytuj Harmonogram', route('shift-templates.edit', $shiftTemplate)),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreShiftTemplateRequest $request, ShiftTemplate $shiftTemplate)
    {
        $validatedData = $request->validated();
        $shiftTemplate->update($validatedData);

        return to_route('shift-templates.index')->with('success', 'Zmiana została pomyślnie zaktualizowana.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShiftTemplate $shiftTemplate)
    {
        $shiftTemplate->delete();

        return back()->with('success', 'Zmiana została pomyślnie usunięta.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        $shiftTemplate = ShiftTemplate::withTrashed()->findOrFail($id);
        $shiftTemplate->restore();

        return back()->with('success', 'Zmiana została pomyślnie przywrócona.');
    }

    /**
     * Generates breadcrumbs for shift schedules.
     */
    protected function getShiftTemplatesBreadcrumbs(?string $pageTitle = null, ?string $pageRoute = null): array
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Harmonogramy Zmian', route('shift-templates.index'));

        if ($pageTitle && $pageRoute) {
            $breadcrumbs->add($pageTitle, $pageRoute);
        }

        return $breadcrumbs->get();
    }
}
