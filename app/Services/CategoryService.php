<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CategoryService
{
    /**
     * Retrieves a paginated list of categories with filtering and sorting options.
     *
     * @param  array  $options  Filtering and sorting options.
     */
    public function getPaginatedCategories(array $options): LengthAwarePaginator
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

        $query = Category::query();

        if ($showDisabled) {
            $query->onlyTrashed();
        }

        $query = $this->applyFilters($query, $filter);
        $query = $this->applySorting($query, $sort, $direction);

        $categories = $query->paginate(15)->appends([
            'show_disabled' => $showDisabled,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction,
        ]);

        return $categories->through(fn (Category $category) => [
            'id' => $category->id,
            'name' => $category->name,
            'deleted_at' => $category->deleted_at,
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
