<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'product_type_id',
        'category_id',
        'unit_of_measure_id',
        'vat_rate_id',
        'is_sellable',
        'is_inventoried',
        'selling_price',
        'default_purchase_price',
    ];

    protected $casts = [
        'is_sellable' => 'boolean',
        'is_inventoried' => 'boolean',
        'selling_price' => 'decimal:2',
        'default_purchase_price' => 'decimal:2',
    ];

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier')
            ->withPivot(['supplier_product_code', 'last_purchase_price']);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'recipes', 'dish_product_id', 'ingredient_product_id')
            ->withPivot('quantity');
    }

    public function usedInDishes(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'recipes', 'ingredient_product_id', 'dish_product_id')
            ->withPivot('quantity');
    }
}
