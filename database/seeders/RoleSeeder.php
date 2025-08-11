<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::create(['name' => 'Kierownik']);
        $role->givePermissionTo(Permission::all());
        $role = Role::create(['name' => 'Pracownik']);
        $role->givePermissionTo('Moje Preferencje');
        $role->givePermissionTo('Grafik Pracy');
    }
}
