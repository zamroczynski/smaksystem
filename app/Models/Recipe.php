<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'instructions',
        'product_id',
        'yield_quantity',
        'yield_unit_of_measure_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'yield_quantity' => 'decimal:4',
    ];

    /**
     * The final product that this recipe creates.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * The unit of measure for the recipe's yield.
     */
    public function yieldUnitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'yield_unit_of_measure_id');
    }

    /**
     * The detailed ingredient entries for the recipe.
     */
    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /**
     * A convenience relationship to directly access the ingredient products.
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'recipe_ingredients')
            ->withPivot('quantity', 'unit_of_measure_id');
    }
}
