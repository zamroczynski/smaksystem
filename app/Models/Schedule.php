<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Schedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'period_start_date',
        'status',
    ];

    protected $casts = [
        'period_start_date' => 'date',
    ];

    /**
     * Get the schedule assignments for the schedule.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class);
    }

    /**
     * The shift templates that belong to the schedule.
     */
    public function shiftTemplates(): BelongsToMany
    {
        return $this->belongsToMany(ShiftTemplate::class, 'schedule_shift_templates');
    }
}
