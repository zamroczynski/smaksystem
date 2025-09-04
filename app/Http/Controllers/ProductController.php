<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\UnitOfMeasure;
use App\Models\VatRate;
use App\Services\ProductService;
use App\Helpers\BreadcrumbsGenerator;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService) {}

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
        
        $products = $this->productService->getPaginatedProducts($options);

        return Inertia::render('Products/Index', [
            'products' => $products,
            'flash' => session('flash'),
            'show_disabled' => $request->boolean('show_disabled'),
            'filter' => $request->input('filter'),
            'sort_by' => $request->input('sort', 'id'),
            'sort_direction' => $request->input('direction', 'asc'),
            'breadcrumbs' => $this->getProductsBreadcrumbs(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Products/Create', [
            'productTypes' => ProductType::all(['id', 'name']),
            'categories' => Category::all(['id', 'name']),
            'unitsOfMeasure' => UnitOfMeasure::all(['id', 'name', 'symbol']),
            'vatRates' => VatRate::all(['id', 'name', 'rate']),
            'breadcrumbs' => $this->getProductsBreadcrumbs('Dodaj produkt', route('products.create')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        Log::channel('product_actions')->info('Utworzono nowy produkt.', [
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);

        return to_route('products.index')->with('success', 'Produkt został pomyślnie utworzony.');
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
    public function edit(Product $product)
    {
        return Inertia::render('Products/Edit', [
            'product' => $product,
            'productTypes' => ProductType::all(['id', 'name']),
            'categories' => Category::all(['id', 'name']),
            'unitsOfMeasure' => UnitOfMeasure::all(['id', 'name', 'symbol']),
            'vatRates' => VatRate::all(['id', 'name', 'rate']),
            'breadcrumbs' => $this->getProductsBreadcrumbs('Edytuj produkt', route('products.edit', $product)),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        Log::channel('product_actions')->info('Zaktualizowano produkt.', [
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);

        return to_route('products.index')->with('success', 'Produkt został pomyślnie zaktualizowany.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $productName = $product->name;
        $productId = $product->id;
        
        $product->delete();

        Log::channel('product_actions')->info('Usunięto produkt.', [
            'user_id' => Auth::id(),
            'product_id' => $productId,
            'product_name' => $productName,
        ]);

        return to_route('products.index')->with('success', 'Produkt został pomyślnie usunięty.');
    }

    /**
     * Restores a deleted (soft delete) product.
     */
    public function restore(int $productId)
    {
        $product = Product::withTrashed()->find($productId);
        $product->restore();

        Log::channel('product_actions')->info('Przywrócono produkt.', [
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);

        return to_route('products.index')->with('success', 'Produkt został pomyślnie przywrócony.');
    }

    /**
     * Generates breadcrumbs for the product module.
     */
    protected function getProductsBreadcrumbs(?string $pageTitle = null, ?string $pageRoute = null): array
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Zarządzanie Produktami', route('products.index'));

        if ($pageTitle && $pageRoute) {
            $breadcrumbs->add($pageTitle, $pageRoute);
        }

        return $breadcrumbs->get();
    }
}
