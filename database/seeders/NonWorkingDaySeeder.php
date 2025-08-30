<?php

namespace Database\Seeders;

use App\Models\NonWorkingDay;
use Illuminate\Database\Seeder;

class NonWorkingDaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            ['name' => 'Nowy Rok', 'day_month' => '01-01'],
            ['name' => 'Święto Trzech Króli', 'day_month' => '01-06'],
            ['name' => 'Święto Pracy', 'day_month' => '05-01'],
            ['name' => 'Święto Konstytucji 3 Maja', 'day_month' => '05-03'],
            ['name' => 'Wniebowzięcie Najświętszej Maryi Panny', 'day_month' => '08-15'],
            ['name' => 'Wszystkich Świętych', 'day_month' => '11-01'],
            ['name' => 'Narodowe Święto Niepodległości', 'day_month' => '11-11'],
            ['name' => 'Boże Narodzenie (pierwszy dzień)', 'day_month' => '12-25'],
            ['name' => 'Boże Narodzenie (drugi dzień)', 'day_month' => '12-26'],

            ['name' => 'Wielkanoc (Niedziela)', 'calculation_rule' => json_encode(['base' => 'easter', 'offset' => 0])],
            ['name' => 'Poniedziałek Wielkanocny', 'calculation_rule' => json_encode(['base' => 'easter', 'offset' => 1])],
            ['name' => 'Zesłanie Ducha Świętego (Zielone Świątki)', 'calculation_rule' => json_encode(['base' => 'easter', 'offset' => 49])],
            ['name' => 'Boże Ciało', 'calculation_rule' => json_encode(['base' => 'easter', 'offset' => 60])],
        ];

        foreach ($holidays as $holiday) {
            NonWorkingDay::updateOrCreate(['name' => $holiday['name']], $holiday);
        }
    }
}
