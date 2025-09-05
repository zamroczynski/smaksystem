<?php

namespace App\Services;

use App\Models\VatRate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class VatRateService
{
    /**
     * Downloads a paginated list of VAT rates with filtering and sorting options.
     *
     * @param  array  $options  Filtering and sorting options.
     */
    public function getPaginatedVatRates(array $options): LengthAwarePaginator
    {
        $showDisabled = $options['show_disabled'] ?? false;
        $filter = $options['filter'] ?? null;
        $sort = $options['sort'] ?? 'id';
        $direction = $options['direction'] ?? 'asc';

        if (! in_array($sort, ['id', 'name', 'rate'])) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $query = VatRate::query();

        if ($showDisabled) {
            $query->onlyTrashed();
        }

        $query = $this->applyFilters($query, $filter);
        $query = $this->applySorting($query, $sort, $direction);

        $vatRates = $query->paginate(15)->appends([
            'show_disabled' => $showDisabled,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction,
        ]);

        return $vatRates->through(fn (VatRate $vatRate) => [
            'id' => $vatRate->id,
            'name' => $vatRate->name,
            'rate' => $vatRate->rate,
            'deleted_at' => $vatRate->deleted_at,
        ]);
    }

    /**
     * Applies search filters to the query.
     */
    protected function applyFilters(Builder $query, ?string $filter): Builder
    {
        if ($filter) {
            $lowerCaseFilter = strtolower($filter);
            $words = explode(' ', $lowerCaseFilter);
            $words = array_filter($words);
            foreach ($words as $word) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$word}%"]);
            }
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
