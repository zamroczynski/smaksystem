<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrCreate(['name' => config('app.super_admin_role_name', 'Super Admin')], ['is_system_protected' => true]);

        $role = Role::create(['name' => 'Kierownik']);
        $role->givePermissionTo(Permission::all());
        
        $role = Role::create(['name' => 'Pracownik']);
        $role->givePermissionTo('Moje Preferencje');
        $role->givePermissionTo('Grafik Pracy');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
