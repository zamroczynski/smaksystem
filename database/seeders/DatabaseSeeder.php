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
        // User::factory(10)->create();
        
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(ShiftTemplateSeeder::class);

        $user = User::factory()->create([
            'login' => 'admin',
            'name' => 'admin',
            'email' => 'test@example.com',
        ]);

        $user->assignRole('Kierownik');
        User::factory(20)->employee()->create();

    }
}
