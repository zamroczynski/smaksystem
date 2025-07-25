<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleAssignment extends Model
{
    protected $fillable = [
        'schedule_id',
        'user_id',
        'shift_template_id',
        'assignment_date',
        'position',
    ];

    protected $casts = [
        'assignment_date' => 'date',
    ];

    /**
     * Get the schedule that owns the ScheduleAssignment.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the user that owns the ScheduleAssignment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shift template that owns the ScheduleAssignment.
     */
    public function shiftTemplate(): BelongsTo
    {
        return $this->belongsTo(ShiftTemplate::class);
    }
}
