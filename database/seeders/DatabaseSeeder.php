<?php

namespace Database\Seeders;

use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            ShiftTemplateSeeder::class,
            HolidaySeeder::class,
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdminUser = User::create([
            'login' => env('SUPER_ADMIN_LOGIN', 'superadmin'),
            'name' => 'Super Admin',
            'email' => env('SUPER_ADMIN_EMAIL', 'superadmin@example.com'),
            'password' => env('SUPER_ADMIN_PASSWORD', 'password'),
            'is_system_protected' => true,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
        $superAdminRoleName = config('app.super_admin_role_name', 'Super Admin');
        $superAdminRole = Role::withoutGlobalScopes()->where('name', $superAdminRoleName)->first();
        $superAdminUser->assignRole($superAdminRole);

        $user = User::factory()->create([
            'login' => 'kierownik',
            'name' => 'Kierownik',
            'email' => 'test@example.com',
        ]);

        $user->assignRole('Kierownik');
        User::factory(100)->employee()->create();

        $this->command->info('Generating holiday instances for the current and next year...');
        Artisan::call('app:generate-holidays', [
            '--year' => now()->year,
        ]);

        $this->command->info('Holiday instances generated successfully.');
    }
}
