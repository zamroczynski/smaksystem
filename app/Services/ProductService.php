<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    /**
     * Retrieves a paginated list of products with filtering and sorting options.
     *
     * @param  array  $options  Filtering and sorting options.
     */
    public function getPaginatedProducts(array $options): LengthAwarePaginator
    {
        $showDisabled = $options['show_disabled'] ?? false;
        $filter = $options['filter'] ?? null;
        $sort = $options['sort'] ?? 'id';
        $direction = $options['direction'] ?? 'asc';

        if (! in_array($sort, ['id', 'name', 'sku', 'category'])) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $productsQuery = Product::query()->with(['category', 'unitOfMeasure']);

        if ($showDisabled) {
            $productsQuery->onlyTrashed();
        }

        $productsQuery = $this->applyFilters($productsQuery, $filter);
        $productsQuery = $this->applySorting($productsQuery, $sort, $direction);

        $products = $productsQuery->paginate(10)->appends([
            'show_disabled' => $showDisabled,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction,
        ]);

        return $products->through(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category_name' => $product->category->name,
            'unit_symbol' => $product->unitOfMeasure->symbol,
            'selling_price' => $product->selling_price,
            'is_sellable' => $product->is_sellable,
            'deleted_at' => $product->deleted_at,
        ]);
    }

    /**
     * Applies search filters to the query.
     */
    protected function applyFilters(Builder $query, ?string $filter): Builder
    {
        if ($filter) {
            $query->where(function (Builder $query) use ($filter) {
                $lowerCaseFilter = strtolower($filter);
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$lowerCaseFilter}%"])
                    ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$lowerCaseFilter}%"])
                    ->orWhereHas('category', function (Builder $q) use ($lowerCaseFilter) {
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
        if ($sort === 'category') {
            $query->select('products.*')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->orderBy('categories.name', $direction);
        } else {
            $query->orderBy('products.'.$sort, $direction);
        }

        return $query;
    }
}
