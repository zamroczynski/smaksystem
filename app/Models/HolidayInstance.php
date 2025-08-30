<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidayInstance extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
