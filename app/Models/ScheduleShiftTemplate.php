<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleShiftTemplate extends Model
{
    protected $fillable = [
        'schedule_id',
        'shift_template_id',
    ];

    /**
     * Get the schedule that owns the ScheduleShiftTemplate.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the shift template that owns the ScheduleShiftTemplate.
     */
    public function shiftTemplate(): BelongsTo
    {
        return $this->belongsTo(ShiftTemplate::class);
    }
}
