<?php

namespace App\Services;

use App\Models\Preference;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PreferenceService
{
    /**
     * Retrieves paginated preferences for the logged-in user
     * with filtering and sorting options.
     */
    public function getPaginatedPreferences(array $options): LengthAwarePaginator
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = $user->preferences();

        if ($options['show_inactive_or_deleted']) {
            $query->withTrashed()
                ->where(function ($q) {
                    $q->whereDate('date_to', '<', Carbon::today())
                        ->orWhereNotNull('deleted_at');
                });
        } else {
            $query->whereDate('date_to', '>=', Carbon::today())
                ->whereNull('deleted_at');
        }

        if (!empty($options['filter'])) {
            $query->where('description', 'ILIKE', '%' . $options['filter'] . '%');
        }

        $sort = $options['sort'] ?? 'date_from';
        $direction = $options['direction'] ?? 'desc';
        $query->orderBy($sort, $direction);

        $preferences = $query->paginate(10)->appends($options);

        $preferences->through(function ($preference) {
            $dateTo = Carbon::parse($preference->date_to);
            return [
                'id' => $preference->id,
                'description' => $preference->description,
                'date_from' => $preference->date_from->format('Y-m-d'),
                'date_to' => $preference->date_to->format('Y-m-d'),
                'is_active' => $dateTo->gte(Carbon::today()) && $preference->deleted_at === null,
                'deleted_at' => $preference->deleted_at,
                'availability' => $preference->availability,
            ];
        });

        return $preferences;
    }
}