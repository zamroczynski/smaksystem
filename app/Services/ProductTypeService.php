<?php

namespace App\Services;

use App\Models\ProductType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductTypeService
{
    /**
     * Retrieves a paginated list of product types with filtering and sorting options.
     *
     * @param  array  $options  Filtering and sorting options.
     */
    public function getPaginatedProductTypes(array $options): LengthAwarePaginator
    {
        $showDisabled = $options['show_disabled'] ?? false;
        $filter = $options['filter'] ?? null;
        $sort = $options['sort'] ?? 'id';
        $direction = $options['direction'] ?? 'asc';

        if (! in_array($sort, ['id', 'name'])) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $query = ProductType::query();

        if ($showDisabled) {
            $query->onlyTrashed();
        }

        $query = $this->applyFilters($query, $filter);
        $query = $this->applySorting($query, $sort, $direction);

        $productTypes = $query->paginate(10)->appends([
            'show_disabled' => $showDisabled,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction,
        ]);

        return $productTypes->through(fn (ProductType $productType) => [
            'id' => $productType->id,
            'name' => $productType->name,
            'deleted_at' => $productType->deleted_at,
        ]);
    }

    /**
     * Applies search filters to the query.
     */
    protected function applyFilters(Builder $query, ?string $filter): Builder
    {
        if ($filter) {
            $lowerCaseFilter = strtolower($filter);
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$lowerCaseFilter}%"]);
        }

        return $query;
    }

    /**
     * Applies sorting to the query.
     */
    protected function applySorting(Builder $query, string $sort, string $direction): Builder
    {
        $query->orderBy($sort, $direction);

        return $query;
    }
}
