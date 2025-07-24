<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
}
