<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\UnitOfMeasure;
use App\Models\VatRate;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pl_PL');

        $types = ProductType::pluck('id', 'name');
        $categories = Category::pluck('id', 'name');
        $units = UnitOfMeasure::pluck('id', 'symbol');

        $vat8 = VatRate::where('name', 'Stawka obniżona (gastro)')->firstOrFail()->id;
        $vat23 = VatRate::where('name', 'Stawka podstawowa')->firstOrFail()->id;

        $productDefinitions = [
            'Składnik' => [
                ['name' => 'Mąka pszenna typ 500', 'category' => 'Produkty sypkie', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Cukier biały', 'category' => 'Produkty sypkie', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Pomidor malinowy', 'category' => 'Warzywa', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Cebula', 'category' => 'Warzywa', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Filet z kurczaka', 'category' => 'Mięso', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Oliwa z oliwek Extra Virgin', 'category' => 'Produkty sypkie', 'unit' => 'l', 'vat_id' => $vat8],
                ['name' => 'Ser mozzarella wiórki', 'category' => 'Nabiał', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Sól kamienna', 'category' => 'Przyprawy', 'unit' => 'g', 'vat_id' => $vat8],
                ['name' => 'Pieprz czarny mielony', 'category' => 'Przyprawy', 'unit' => 'g', 'vat_id' => $vat8],
                ['name' => 'Ziemniak', 'category' => 'Warzywa', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Schab wieprzowy bez kości', 'category' => 'Mięso', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Jajko', 'category' => 'Nabiał', 'unit' => 'szt', 'vat_id' => $vat8],
                ['name' => 'Bułka tarta', 'category' => 'Produkty sypkie', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Olej rzepakowy', 'category' => 'Produkty sypkie', 'unit' => 'l', 'vat_id' => $vat8],
                ['name' => 'Marchew', 'category' => 'Warzywa', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Pietruszka korzeń', 'category' => 'Warzywa', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Seler korzeń', 'category' => 'Warzywa', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Makaron nitki', 'category' => 'Produkty sypkie', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Ogórek zielony', 'category' => 'Warzywa', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Śmietana 18%', 'category' => 'Nabiał', 'unit' => 'l', 'vat_id' => $vat8],
            ],
            'Półprodukt' => [
                ['name' => 'Sos pomidorowy do pizzy', 'category' => 'Przyprawy', 'unit' => 'l', 'vat_id' => $vat8],
                ['name' => 'Bulion warzywny', 'category' => 'Zupy', 'unit' => 'l', 'vat_id' => $vat8],
            ],
            'Towar handlowy' => [
                ['name' => 'Coca-Cola 0.5l', 'category' => 'Napoje bezalkoholowe', 'unit' => 'but', 'vat_id' => $vat23, 'price' => 8.00, 'cost' => 2.50],
                ['name' => 'Woda mineralna niegazowana 0.5l', 'category' => 'Napoje bezalkoholowe', 'unit' => 'but', 'vat_id' => $vat8, 'price' => 6.00, 'cost' => 1.50],
            ],
            'Danie' => [
                [
                    'name' => 'Kotlet schabowy z ziemniakami', 'category' => 'Dania główne', 'unit' => 'por', 'vat_id' => $vat8, 'price' => 35.00,
                    'recipe' => [
                        ['ingredient' => 'Schab wieprzowy bez kości', 'quantity' => 0.18],
                        ['ingredient' => 'Jajko', 'quantity' => 1],
                        ['ingredient' => 'Bułka tarta', 'quantity' => 0.05],
                        ['ingredient' => 'Olej rzepakowy', 'quantity' => 0.05],
                        ['ingredient' => 'Ziemniak', 'quantity' => 0.25],
                        ['ingredient' => 'Sól kamienna', 'quantity' => 2],
                        ['ingredient' => 'Pieprz czarny mielony', 'quantity' => 1],
                    ],
                ],
                [
                    'name' => 'Rosół z makaronem', 'category' => 'Zupy', 'unit' => 'por', 'vat_id' => $vat8, 'price' => 18.00,
                    'recipe' => [
                        ['ingredient' => 'Filet z kurczaka', 'quantity' => 0.1],
                        ['ingredient' => 'Marchew', 'quantity' => 0.05],
                        ['ingredient' => 'Pietruszka korzeń', 'quantity' => 0.03],
                        ['ingredient' => 'Seler korzeń', 'quantity' => 0.02],
                        ['ingredient' => 'Makaron nitki', 'quantity' => 0.08],
                        ['ingredient' => 'Sól kamienna', 'quantity' => 3],
                    ],
                ],
                [
                    'name' => 'Mizeria', 'category' => 'Sałatki', 'unit' => 'por', 'vat_id' => $vat8, 'price' => 12.00,
                    'recipe' => [
                        ['ingredient' => 'Ogórek zielony', 'quantity' => 0.15],
                        ['ingredient' => 'Śmietana 18%', 'quantity' => 0.05],
                        ['ingredient' => 'Sól kamienna', 'quantity' => 1],
                        ['ingredient' => 'Pieprz czarny mielony', 'quantity' => 0.5],
                    ],
                ],
            ],
        ];

        Log::info('ProductSeeder: Rozpoczynanie tworzenia produktów...');
        foreach ($productDefinitions as $typeName => $products) {
            if (! isset($types[$typeName])) {
                Log::warning("ProductSeeder: Nie znaleziono typu produktu '{$typeName}'. Pomijanie.");

                continue;
            }
            $productTypeId = $types[$typeName];

            foreach ($products as $productData) {
                Product::updateOrCreate(
                    ['name' => $productData['name']],
                    [
                        'sku' => $faker->unique()->ean8(),
                        'product_type_id' => $productTypeId,
                        'category_id' => $categories[$productData['category']],
                        'unit_of_measure_id' => $units[$productData['unit']],
                        'vat_rate_id' => $productData['vat_id'],
                        'is_sellable' => isset($productData['price']),
                        'is_inventoried' => true,
                        'selling_price' => $productData['price'] ?? null,
                        'default_purchase_price' => $productData['cost'] ?? $faker->randomFloat(2, 1, 20),
                    ]
                );
            }
        }
    }
}
