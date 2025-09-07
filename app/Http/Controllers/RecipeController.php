<?php

namespace App\Http\Controllers;

use App\Helpers\BreadcrumbsGenerator;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Models\Recipe;
use App\Services\RecipeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecipeController extends Controller
{
    public function __construct(private RecipeService $recipeService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $options = [
            'show_disabled' => $request->boolean('show_disabled'),
            'filter' => $request->input('filter'),
            'sort' => $request->input('sort', 'id'),
            'direction' => $request->input('direction', 'desc'),
        ];
        $recipes = $this->recipeService->getPaginatedRecipes($options);

        return Inertia::render('Recipes/Index', [
            'recipes' => $recipes,
            'flash' => session('flash'),
            'show_disabled' => $options['show_disabled'],
            'breadcrumbs' => $this->getRecipesBreadcrumbs(),
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
        $viewData = $this->recipeService->getDataForCreatePage();

        return Inertia::render('Recipes/Create', array_merge($viewData, [
            'breadcrumbs' => $this->getRecipesBreadcrumbs('Dodaj recepturę', route('recipes.create')),
        ]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecipeRequest $request)
    {
        $this->recipeService->createRecipe($request->validated());

        return redirect()->route('recipes.index')->with('success', 'Receptura została pomyślnie utworzona.');
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
    public function edit(Recipe $recipe): Response
    {
        $viewData = $this->recipeService->getDataForEditPage($recipe);

        return Inertia::render('Recipes/Edit', array_merge($viewData, [
            'breadcrumbs' => $this->getRecipesBreadcrumbs('Edytuj recepturę', route('recipes.edit', $recipe)),
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {
        $this->recipeService->updateRecipe($recipe, $request->validated());

        return redirect()->route('recipes.index')->with('success', 'Receptura została pomyślnie zaktualizowana.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe)
    {
        $recipe->delete();

        return redirect()->route('recipes.index')->with('success', 'Receptura została pomyślnie usunięta.');
    }

    /**
     * Restore the specified soft-deleted recipe.
     */
    public function restore(int $recipeId)
    {
        $recipe = Recipe::withTrashed()->find($recipeId);

        if ($recipe) {
            $recipe->restore();

            return to_route('recipes.index')->with('success', 'Użytkownik został pomyślnie przywrócony.');
        }

        return to_route('recipes.index')->with('error', 'Nie udało się przywrócić użytkownika.');
    }

    /**
     * Generates breadcrumbs for recipe management.
     */
    protected function getRecipesBreadcrumbs(?string $pageTitle = null, ?string $pageRoute = null): array
    {
        $breadcrumbs = BreadcrumbsGenerator::make('Panel nawigacyjny', route('dashboard'))
            ->add('Receptury', route('recipes.index'));

        if ($pageTitle && $pageRoute) {
            $breadcrumbs->add($pageTitle, $pageRoute);
        }

        return $breadcrumbs->get();
    }
}
