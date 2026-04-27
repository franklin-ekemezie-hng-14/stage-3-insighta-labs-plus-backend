<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'github_id' => 'admin_test',
            'username'  => 'admin_user',
            'email'     => 'admin_user@example.com',
            'role'      => 'admin',
            'is_active' => true,
        ]);

        User::factory()->create([
            'github_id' => 'analyst_test',
            'username'  => 'analyst_user',
            'email'     => 'analyst_user@example.com',
            'role'      => 'analyst',
            'is_active' => true,
        ]);

        $this->call([
            ProfileSeeder::class,
        ]);
    }
}
