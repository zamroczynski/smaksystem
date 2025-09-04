<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use App\Helpers\BreadcrumbsGenerator;
use App\Services\ProductTypeService;
use App\Http\Requests\StoreProductTypeRequest;
use App\Http\Requests\UpdateProductTypeRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductTypeController extends Controller
{
    public function __construct(private ProductTypeService $productTypeService) {}

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

        $productTypes = $this->productTypeService->getPaginatedProductTypes($options);

        return Inertia::render('ProductTypes/Index', [
            'productTypes' => $productTypes,
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
        return Inertia::render('ProductTypes/Create', [
            'breadcrumbs' => $this->getBreadcrumbs('Dodaj typ produktu', route('product-types.create')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductTypeRequest $request)
    {
        ProductType::create($request->validated());

        return to_route('product-types.index')->with('success', 'Typ produktu został pomyślnie utworzony.');
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
    public function edit(ProductType $productType)
    {
        return Inertia::render('ProductTypes/Edit', [
            'productType' => $productType,
            'breadcrumbs' => $this->getBreadcrumbs('Edytuj typ produktu', route('product-types.edit', $productType)),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductTypeRequest $request, ProductType $productType)
    {
        $productType->update($request->validated());

        return to_route('product-types.index')->with('success', 'Typ produktu został pomyślnie zaktualizowany.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductType $productType)
    {
        $productType->delete();

        return to_route('product-types.index')->with('success', 'Typ produktu został pomyślnie zarchiwizowany.');
    }

    /**
     * Restores a deleted (soft delete) product type.
     */
    public function restore(int $productTypeId)
    {
        $product_type = ProductType::withTrashed()->find($productTypeId);
        $product_type->restore();

        return to_route('product-types.index')->with('success', 'Typ produktu został pomyślnie przywrócony.');
    }

    /**
     * Generates breadcrumbs for the product types module.
     */
    protected function getBreadcrumbs(?string $pageTitle = null, ?string $pageRoute = null): array
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Zarządzanie Produktami', route('products.index'))
            ->add('Typy Produktów', route('product-types.index'));

        if ($pageTitle && $pageRoute) {
            $breadcrumbs->add($pageTitle, $pageRoute);
        }

        return $breadcrumbs->get();
    }
}
