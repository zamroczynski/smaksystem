<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ShiftTemplate;

class ShiftTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ShiftTemplate::create([
            'name' => 'Dniówka',
            'time_from' => '07:00:00',
            'time_to' => '19:00:00',
            'duration_hours' => 12.0,
            'required_staff_count' => 2,
        ]);
        ShiftTemplate::create([
            'name' => 'Nocka',
            'time_from' => '19:00:00',
            'time_to' => '07:00:00',
            'duration_hours' => 12.0,
            'required_staff_count' => 2,
        ]);
    }
}
