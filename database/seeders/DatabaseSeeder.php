<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use App\Models\Review;
    
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create some users first
        User::factory(50)->create();

        // User::factory(10)->create();

        User::firstOrCreate([
            'email' => 'test@test.ee',
        ], [
            'name' => 'Test User',
            'email' => 'test@test.ee',
            'password' => Hash::make('test@test.ee'),
        ]);

        $this->call([
            AuthorSeeder::class,
            // PostSeeder::class,
            // CommentSeeder::class,
            ProductSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
