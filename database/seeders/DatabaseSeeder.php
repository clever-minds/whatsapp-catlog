<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin user if not exists
        if (\App\Models\User::where('email', 'admin@admin.com')->count() === 0) {
            \App\Models\User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Run other seeders
        $this->call([
            PizzaRawMaterialSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
