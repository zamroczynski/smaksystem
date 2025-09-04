<?php

namespace App\Services;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UnitOfMeasureService
{
    /**
     * Retrieves a paginated list of units of measurement with filtering and sorting options.
     *
     * @param  array  $options Filtering and sorting options.
     */
    public function getPaginatedUnitOfMeasures(array $options): LengthAwarePaginator
    {
        $showDisabled = $options['show_disabled'] ?? false;
        $filter = $options['filter'] ?? null;
        $sort = $options['sort'] ?? 'id';
        $direction = $options['direction'] ?? 'asc';

        if (! in_array($sort, ['id', 'name', 'symbol'])) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $query = UnitOfMeasure::query();

        if ($showDisabled) {
            $query->onlyTrashed();
        }

        $query = $this->applyFilters($query, $filter);
        $query = $this->applySorting($query, $sort, $direction);

        $unitOfMeasures = $query->paginate(15)->appends([
            'show_disabled' => $showDisabled,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction,
        ]);

        return $unitOfMeasures->through(fn (UnitOfMeasure $unit) => [
            'id' => $unit->id,
            'name' => $unit->name,
            'symbol' => $unit->symbol,
            'deleted_at' => $unit->deleted_at,
        ]);
    }

    /**
     * Applies search filters to the query.
     */
    protected function applyFilters(Builder $query, ?string $filter): Builder
    {
        if ($filter) {
            $lowerCaseFilter = strtolower($filter);
            $query->where(function (Builder $query) use ($lowerCaseFilter) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$lowerCaseFilter}%"])
                      ->orWhereRaw('LOWER(symbol) LIKE ?', ["%{$lowerCaseFilter}%"]);
            });
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