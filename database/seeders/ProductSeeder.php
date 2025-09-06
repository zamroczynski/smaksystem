<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\UnitOfMeasure;
use App\Models\VatRate;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

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
            ],
            'Półprodukt' => [
                ['name' => 'Sos pomidorowy do pizzy', 'category' => 'Przyprawy', 'unit' => 'l', 'vat_id' => $vat8],
                ['name' => 'Bulion warzywny', 'category' => 'Zupy', 'unit' => 'l', 'vat_id' => $vat8],
                ['name' => 'Ciasto na pizzę', 'category' => 'Produkty sypkie', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Sos czosnkowy', 'category' => 'Nabiał', 'unit' => 'l', 'vat_id' => $vat8],
                ['name' => 'Marynata do mięs', 'category' => 'Przyprawy', 'unit' => 'l', 'vat_id' => $vat8],
                ['name' => 'Puree ziemniaczane', 'category' => 'Warzywa', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Gotowany ryż', 'category' => 'Produkty sypkie', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Dressing winegret', 'category' => 'Przyprawy', 'unit' => 'l', 'vat_id' => $vat8],
                ['name' => 'Karmelizowana cebula', 'category' => 'Warzywa', 'unit' => 'kg', 'vat_id' => $vat8],
                ['name' => 'Baza do zupy krem', 'category' => 'Zupy', 'unit' => 'l', 'vat_id' => $vat8],
            ],
            'Towar handlowy' => [
                ['name' => 'Coca-Cola 0.5l', 'category' => 'Napoje bezalkoholowe', 'unit' => 'but', 'vat_id' => $vat23, 'price' => 8.00, 'cost' => 2.50],
                ['name' => 'Woda mineralna niegazowana 0.5l', 'category' => 'Napoje bezalkoholowe', 'unit' => 'but', 'vat_id' => $vat8, 'price' => 6.00, 'cost' => 1.50],
                ['name' => 'Sok pomarańczowy Cappy 0.33l', 'category' => 'Napoje bezalkoholowe', 'unit' => 'but', 'vat_id' => $vat23, 'price' => 7.00, 'cost' => 2.00],
                ['name' => 'Piwo Tyskie 0.5l', 'category' => 'Napoje alkoholowe', 'unit' => 'but', 'vat_id' => $vat23, 'price' => 12.00, 'cost' => 4.00],
                ['name' => 'Wino czerwone stołowe', 'category' => 'Napoje alkoholowe', 'unit' => 'but', 'vat_id' => $vat23, 'price' => 50.00, 'cost' => 20.00],
                ['name' => 'Lemoniada domowa 0.4l', 'category' => 'Napoje bezalkoholowe', 'unit' => 'szt', 'vat_id' => $vat8, 'price' => 14.00, 'cost' => 3.00],
                ['name' => 'Red Bull 0.25l', 'category' => 'Napoje bezalkoholowe', 'unit' => 'pusz', 'vat_id' => $vat23, 'price' => 10.00, 'cost' => 4.50],
                ['name' => 'Chipsy Lays solone', 'category' => 'Przystawki', 'unit' => 'szt', 'vat_id' => $vat8, 'price' => 9.00, 'cost' => 3.50],
                ['name' => 'Orzeszki ziemne solone', 'category' => 'Przystawki', 'unit' => 'szt', 'vat_id' => $vat8, 'price' => 7.00, 'cost' => 2.50],
                ['name' => 'Paluszki', 'category' => 'Przystawki', 'unit' => 'szt', 'vat_id' => $vat8, 'price' => 5.00, 'cost' => 1.50],
            ],
        ];

        foreach ($productDefinitions as $typeName => $products) {
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