<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolidayInstance extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'date',
        'holiday_definition_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the definition that this instance belongs to.
     */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(Holiday::class, 'holiday_definition_id');
    }
}
