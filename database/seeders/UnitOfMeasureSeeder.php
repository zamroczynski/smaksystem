<?php

namespace Database\Seeders;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class UnitOfMeasureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Kilogram', 'symbol' => 'kg'],
            ['name' => 'Gram', 'symbol' => 'g'],
            ['name' => 'Litr', 'symbol' => 'l'],
            ['name' => 'Mililitr', 'symbol' => 'ml'],
            ['name' => 'Sztuka', 'symbol' => 'szt'],
            ['name' => 'Porcja', 'symbol' => 'por'],
            ['name' => 'Butelka', 'symbol' => 'but'],
            ['name' => 'Puszka', 'symbol' => 'pusz'],
        ];

        foreach ($units as $unit) {
            UnitOfMeasure::updateOrCreate(['symbol' => $unit['symbol']], $unit);
        }
    }
}
