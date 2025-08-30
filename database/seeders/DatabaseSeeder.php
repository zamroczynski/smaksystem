<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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

    }
}
