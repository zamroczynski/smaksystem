<?php

namespace App\Http\Controllers;

use App\Helpers\BreadcrumbsGenerator;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

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

        $categories = $this->categoryService->getPaginatedCategories($options);

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
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
        return Inertia::render('Categories/Create', [
            'breadcrumbs' => $this->getBreadcrumbs('Dodaj kategorię', route('categories.create')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        Category::create($request->validated());

        return to_route('categories.index')->with('success', 'Kategoria została pomyślnie utworzona.');
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
    public function edit(Category $category)
    {
        return Inertia::render('Categories/Edit', [
            'category' => $category,
            'breadcrumbs' => $this->getBreadcrumbs('Edytuj kategorię', route('categories.edit', $category)),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return to_route('categories.index')->with('success', 'Kategoria została pomyślnie zaktualizowana.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return to_route('categories.index')->with('success', 'Kategoria została pomyślnie zarchiwizowana.');
    }

    /**
     * Restores a deleted (soft delete) category.
     */
    public function restore(int $categoryId)
    {
        $category = Category::withTrashed()->find($categoryId);
        $category->restore();

        return to_route('categories.index')->with('success', 'Kategoria została pomyślnie przywrócona.');
    }

    /**
     * Generates breadcrumbs for the category module.
     */
    protected function getBreadcrumbs(?string $pageTitle = null, ?string $pageRoute = null): array
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Zarządzanie Produktami', route('products.index'))
            ->add('Kategorie', route('categories.index'));

        if ($pageTitle && $pageRoute) {
            $breadcrumbs->add($pageTitle, $pageRoute);
        }

        return $breadcrumbs->get();
    }
}
