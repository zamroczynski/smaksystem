<?php

namespace App\Http\Controllers;

use App\Helpers\BreadcrumbsGenerator;
use App\Http\Requests\StoreVatRateRequest;
use App\Http\Requests\UpdateVatRateRequest;
use App\Models\VatRate;
use App\Services\VatRateService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VatRateController extends Controller
{
    public function __construct(private VatRateService $vatRateService) {}

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

        $vatRates = $this->vatRateService->getPaginatedVatRates($options);

        return Inertia::render('VatRates/Index', [
            'vatRates' => $vatRates,
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
        return Inertia::render('VatRates/Create', [
            'breadcrumbs' => $this->getBreadcrumbs('Dodaj stawkę VAT', route('vat-rates.create')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVatRateRequest $request)
    {
        VatRate::create($request->validated());

        return to_route('vat-rates.index')->with('success', 'Stawka VAT została pomyślnie utworzona.');
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
    public function edit(VatRate $vatRate)
    {
        return Inertia::render('VatRates/Edit', [
            'vatRate' => $vatRate,
            'breadcrumbs' => $this->getBreadcrumbs('Edytuj stawkę VAT', route('vat-rates.edit', $vatRate)),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVatRateRequest $request, VatRate $vatRate)
    {
        $vatRate->update($request->validated());

        return to_route('vat-rates.index')->with('success', 'Stawka VAT została pomyślnie zaktualizowana.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VatRate $vatRate)
    {
        $vatRate->delete();

        return to_route('vat-rates.index')->with('success', 'Stawka VAT została pomyślnie zarchiwizowana.');
    }

    /**
     * Restores a deleted (soft delete) VAT rate.
     */
    public function restore(int $vatRateId)
    {
        $vat_rate = VatRate::withTrashed()->find($vatRateId);
        $vat_rate->restore();

        return to_route('vat-rates.index')->with('success', 'Stawka VAT została pomyślnie przywrócona.');
    }

    /**
     * Generates breadcrumbs for the VAT rates module.
     */
    protected function getBreadcrumbs(?string $pageTitle = null, ?string $pageRoute = null): array
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Zarządzanie Produktami', route('products.index'))
            ->add('Stawki VAT', route('vat-rates.index'));

        if ($pageTitle && $pageRoute) {
            $breadcrumbs->add($pageTitle, $pageRoute);
        }

        return $breadcrumbs->get();
    }
}
