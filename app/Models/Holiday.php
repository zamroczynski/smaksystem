<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Holiday extends Model
{
    /** @use HasFactory<\Database\Factories\Holiday> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'date',
        'day_month',
        'calculation_rule',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'calculation_rule' => 'array',
    ];

    /**
     * Get all of the instances for the Holiday definition.
     */
    public function instances(): HasMany
    {
        return $this->hasMany(HolidayInstance::class, 'holiday_definition_id');
    }
}
