<?php

namespace Database\Seeders;

use App\Models\VatRate;
use Illuminate\Database\Seeder;

class VatRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vatRates = [
            ['name' => 'Stawka podstawowa', 'rate' => 23.00],
            ['name' => 'Stawka obniżona (gastro)', 'rate' => 8.00],
            ['name' => 'Stawka super obniżona', 'rate' => 5.00],
            ['name' => 'Stawka zerowa', 'rate' => 0.00],
            ['name' => 'Zwolniony z VAT (ZW)', 'rate' => 0.00],
        ];

        foreach ($vatRates as $rate) {
            VatRate::updateOrCreate(['name' => $rate['name']], $rate);
        }
    }
}
