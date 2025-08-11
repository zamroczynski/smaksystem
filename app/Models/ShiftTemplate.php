<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'time_from',
        'time_to',
        'duration_hours',
        'required_staff_count',
    ];

    protected $casts = [
        'time_from' => 'string',
        'time_to' => 'string',
        'duration_hours' => 'float',
        'required_staff_count' => 'integer',
    ];

    /**
     * Get the schedule assignments for the shift template.
     */
    public function scheduleAssignments(): HasMany // Dodaj tę metodę
    {
        return $this->hasMany(ScheduleAssignment::class);
    }

    /**
     * The schedules that belong to the shift template.
     */
    public function schedules(): BelongsToMany // Dodaj tę metodę
    {
        return $this->belongsToMany(Schedule::class, 'schedule_shift_templates');
    }
}
