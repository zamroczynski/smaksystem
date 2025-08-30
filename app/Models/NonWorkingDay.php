<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NonWorkingDay extends Model
{
    /** @use HasFactory<\Database\Factories\NonWorkingDayFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'date',
        'day_month',
        'calculation_rule',
    ];

    protected $casts = [
        'date' => 'date',
        'calculation_rule' => 'array',
    ];
}
