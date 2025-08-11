<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'Edycja pracowników']);
        Permission::create(['name' => 'Edycja ról']);
        Permission::create(['name' => 'Moje Preferencje']);
        Permission::create(['name' => 'Harmonogram Zmian']);
        Permission::create(['name' => 'Grafik Pracy']);
        Permission::create(['name' => 'Edycja Grafików Pracy']);
    }
}
