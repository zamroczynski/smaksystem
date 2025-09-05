<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\ProductType;
use App\Models\UnitOfMeasure;
use App\Models\VatRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isSellable = $this->faker->boolean();

        return [
            'name' => $this->faker->words(3, true),
            'sku' => $this->faker->unique()->ean8(),
            'description' => $this->faker->sentence(),
            'product_type_id' => ProductType::factory(),
            'category_id' => Category::factory(),
            'unit_of_measure_id' => UnitOfMeasure::factory(),
            'vat_rate_id' => VatRate::factory(),
            'is_sellable' => $isSellable,
            'is_inventoried' => $this->faker->boolean(),
            'selling_price' => $isSellable ? $this->faker->randomFloat(2, 10, 200) : null,
            'default_purchase_price' => $this->faker->randomFloat(2, 5, 100),
        ];
    }
}
