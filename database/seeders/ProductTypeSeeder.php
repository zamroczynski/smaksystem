<?php

namespace Database\Seeders;

use App\Models\ProductType;
use Illuminate\Database\Seeder;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Danie',
            'Składnik',
            'Półprodukt',
            'Towar handlowy',
        ];

        foreach ($types as $typeName) {
            ProductType::updateOrCreate(['name' => $typeName]);
        }
    }
}
