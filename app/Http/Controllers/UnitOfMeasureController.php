<?php

namespace App\Http\Controllers;

use App\Models\UnitOfMeasure;
use App\Services\UnitOfMeasureService;
use App\Helpers\BreadcrumbsGenerator;
use App\Http\Requests\StoreUnitOfMeasureRequest;
use App\Http\Requests\UpdateUnitOfMeasureRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitOfMeasureController extends Controller
{
    public function __construct(private UnitOfMeasureService $unitOfMeasureService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $options = [
            'show_disabled' => $request->boolean('show_disabled'),
            'filter' => $request->input('filter'),
            'sort' => $request->input('sort', 'id'),
            'direction' => $request->input('direction', 'asc'),
        ];

        $unitOfMeasures = $this->unitOfMeasureService->getPaginatedUnitOfMeasures($options);

        return Inertia::render('UnitOfMeasures/Index', [
            'unitOfMeasures' => $unitOfMeasures,
            'flash' => session('flash'),
            'show_disabled' => $request->boolean('show_disabled'),
            'filter' => $request->input('filter'),
            'sort_by' => $request->input('sort', 'id'),
            'sort_direction' => $request->input('direction', 'asc'),
            'breadcrumbs' => $this->getBreadcrumbs(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('UnitOfMeasures/Create', [
            'breadcrumbs' => $this->getBreadcrumbs('Dodaj jednostkę', route('unit-of-measures.create')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitOfMeasureRequest $request)
    {
        UnitOfMeasure::create($request->validated());

        return to_route('unit-of-measures.index')->with('success', 'Jednostka miary została pomyślnie utworzona.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnitOfMeasure $unitOfMeasure)
    {
        return Inertia::render('UnitOfMeasures/Edit', [
            'unitOfMeasure' => $unitOfMeasure,
            'breadcrumbs' => $this->getBreadcrumbs('Edytuj jednostkę', route('unit-of-measures.edit', $unitOfMeasure)),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitOfMeasureRequest $request, UnitOfMeasure $unitOfMeasure)
    {
        $unitOfMeasure->update($request->validated());

        return to_route('unit-of-measures.index')->with('success', 'Jednostka miary została pomyślnie zaktualizowana.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnitOfMeasure $unitOfMeasure)
    {
        $unitOfMeasure->delete();

        return to_route('unit-of-measures.index')->with('success', 'Jednostka miary została pomyślnie zarchiwizowana.');
    }

    /**
     * Restores a deleted (soft delete) unit of measure.
     */
    public function restore(int $unitOfMeasureId)
    {
        $unit_of_measure = UnitOfMeasure::withTrashed()->find($unitOfMeasureId);
        $unit_of_measure->restore();

        return to_route('unit-of-measures.index')->with('success', 'Jednostka miary została pomyślnie przywrócona.');
    }

    /**
     * Generates breadcrumbs for the measurement units module.
     */
    protected function getBreadcrumbs(?string $pageTitle = null, ?string $pageRoute = null): array
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Zarządzanie Produktami', route('products.index'))
            ->add('Jednostki miary', route('unit-of-measures.index'));

        if ($pageTitle && $pageRoute) {
            $breadcrumbs->add($pageTitle, $pageRoute);
        }

        return $breadcrumbs->get();
    }
}
