<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitOfMeasure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'symbol'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
