<?php

namespace App\Rules;

use App\Models\Preference;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class NoOverlappingPreferencesRule implements ValidationRule
{
    /**
     * The end date of the preference range.
     *
     * @var string
     */
    protected $dateTo;

    /**
     * The ID of the preference being updated, if any.
     *
     * @var int|null
     */
    protected $ignoreId;

    /**
     * Create a new rule instance.
     *
     * @param  string  $dateTo  The end date of the new preference.
     * @param  int|null  $ignoreId  The ID of the preference to ignore (used during updates).
     */
    public function __construct(string $dateTo, ?int $ignoreId = null)
    {
        $this->dateTo = $dateTo;
        $this->ignoreId = $ignoreId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $dateFrom = Carbon::parse($value);
        $dateTo = Carbon::parse($this->dateTo);

        $userId = Auth::id();
        if (! $userId) {
            return;
        }

        $query = Preference::where('user_id', $userId)
            ->where(function ($query) use ($dateFrom, $dateTo) {
                $query->where('date_from', '<=', $dateTo)
                    ->where('date_to', '>=', $dateFrom);
            });

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        $conflictingPreference = $query->exists();

        if ($conflictingPreference) {
            $fail(__('validation.custom.preferences.overlapping'));
        }
    }
}
