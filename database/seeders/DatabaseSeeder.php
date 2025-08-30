<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

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
            HolidaySeeder::class
        ]);

        $user = User::factory()->create([
            'login' => 'admin',
            'name' => 'admin',
            'email' => 'test@example.com',
        ]);

        $user->assignRole('Kierownik');
        User::factory(100)->employee()->create();

        $this->command->info('Generating holiday instances for the current and next year...');
        Artisan::call('app:generate-holidays', [
            '--year' => now()->year
        ]);

        $this->command->info('Holiday instances generated successfully.');
    }
}
