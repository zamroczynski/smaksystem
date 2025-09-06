<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\UnitOfMeasure;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RecipeService
{
    /**
     * Retrieves a paginated list of recipes with filtering and sorting.
     */
    public function getPaginatedRecipes(array $options): LengthAwarePaginator
    {
        $showDisabled = $options['show_disabled'] ?? false;
        $filter = $options['filter'] ?? null;
        $sort = $options['sort'] ?? 'id';
        $direction = $options['direction'] ?? 'desc';

        if (! in_array($sort, ['id', 'name', 'product_name', 'created_at'])) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $query = Recipe::query();

        $query = $this->applyFilters($query, $filter);
        $query = $this->applySorting($query, $sort, $direction);

        if ($showDisabled) {
            $query->onlyTrashed();
        }

        $recipes = $query->paginate(10)->appends([
            'show_disabled' => $showDisabled,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction,
        ]);

        return $recipes->through(function ($recipe) {
            $recipe->load('product:id,name', 'yieldUnitOfMeasure:id,name');

            return [
                'id' => $recipe->id,
                'name' => $recipe->name,
                'product_name' => $recipe->product->name,
                'yield_quantity' => $recipe->yield_quantity,
                'yield_unit_name' => $recipe->yieldUnitOfMeasure->name,
                'created_at' => $recipe->created_at ? $recipe->created_at->format('Y-m-d H:i') : null,
                'deleted_at' => $recipe->deleted_at,
            ];
        });
    }

    /**
     * Applies search filters to the query.
     */
    protected function applyFilters(Builder $query, ?string $filter): Builder
    {
        if ($filter) {
            $query->where(function ($query) use ($filter) {
                $lowerCaseFilter = strtolower($filter);
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$lowerCaseFilter}%"])
                    ->orWhereHas('product', function ($q) use ($lowerCaseFilter) {
                        $q->whereRaw('LOWER(name) LIKE ?', ["%{$lowerCaseFilter}%"]);
                    });
            });
        }

        return $query;
    }

    /**
     * Applies sorting to the query.
     */
    protected function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        if ($sort === 'product_name') {
            $query->select('recipes.*')
                ->join('products', 'recipes.product_id', '=', 'products.id')
                ->orderBy('products.name', $direction);
        } else {
            $query->orderBy('recipes.'.$sort, $direction);
        }

        return $query;
    }

    /**
     * Creates a new recipe with its ingredients.
     *
     * @param array $data Validated data from the request.
     * @return Recipe The newly created recipe.
     */
    public function createRecipe(array $data): Recipe
    {
        return DB::transaction(function () use ($data) {
            $recipe = Recipe::create($data);

            $recipe->recipeIngredients()->createMany($data['ingredients']);

            return $recipe;
        });
    }

    /**
     * Updates an existing recipe and syncs its ingredients.
     *
     * @param Recipe $recipe The recipe instance to update.
     * @param array $data Validated data from the request.
     * @return Recipe The updated recipe.
     */
    public function updateRecipe(Recipe $recipe, array $data): Recipe
    {
        return DB::transaction(function () use ($recipe, $data) {
            $recipe->update($data);

            $recipe->recipeIngredients()->delete();
            $recipe->recipeIngredients()->createMany($data['ingredients']);

            return $recipe;
        });
    }

    /**
     * Gathers all necessary data for the recipe creation form.
     *
     * @return array
     */
    public function getDataForCreatePage(): array
    {
        return $this->getSharedFormData();
    }

    /**
     * Gathers all necessary data for the recipe editing form.
     *
     * @param Recipe $recipe
     * @return array
     */
    public function getDataForEditPage(Recipe $recipe): array
    {
        $recipe->load(['recipeIngredients' => function ($query) {
            $query->with('product:id,name', 'unitOfMeasure:id,name');
        }]);

        $recipeData = [
            'recipe' => [
                'id' => $recipe->id,
                'name' => $recipe->name,
                'description' => $recipe->description,
                'instructions' => $recipe->instructions,
                'product_id' => $recipe->product_id,
                'yield_quantity' => $recipe->yield_quantity,
                'yield_unit_of_measure_id' => $recipe->yield_unit_of_measure_id,
                'is_active' => $recipe->is_active,
                'ingredients' => $recipe->recipeIngredients->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_of_measure_id' => $item->unit_of_measure_id,
                ]),
            ],
        ];

        return array_merge($this->getSharedFormData(), $recipeData);
    }

    /**
     * Retrieves data common to both create and edit forms.
     *
     * @return array
     */
    private function getSharedFormData(): array
    {
        $productTypes = ProductType::whereIn('name', ['Danie', 'Składnik', 'Półprodukt'])->pluck('id', 'name');

        $dishProducts = Product::where('product_type_id', $productTypes['Danie'] ?? null)
            ->orderBy('name')
            ->get(['id', 'name']);

        $ingredientProducts = Product::whereIn('product_type_id', [$productTypes['Składnik'] ?? null, $productTypes['Półprodukt'] ?? null])
            ->orderBy('name')
            ->get(['id', 'name']);

        return [
            'dishProducts' => $dishProducts,
            'ingredientProducts' => $ingredientProducts,
            'unitsOfMeasure' => UnitOfMeasure::all(['id', 'name']),
        ];
    }
}