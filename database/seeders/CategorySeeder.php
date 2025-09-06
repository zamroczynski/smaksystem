<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Dania główne',
            'Zupy',
            'Przystawki',
            'Sałatki',
            'Desery',
            'Napoje bezalkoholowe',
            'Napoje alkoholowe',
            'Warzywa',
            'Owoce',
            'Mięso',
            'Ryby i owoce morza',
            'Nabiał',
            'Produkty sypkie',
            'Przyprawy',
        ];

        foreach ($categories as $categoryName) {
            Category::updateOrCreate(['name' => $categoryName]);
        }
    }
}
