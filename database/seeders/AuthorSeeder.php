<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Comment;
class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     
    public function run(): void
    { 
        Author::factory()->count(10)
            ->has(
                Post::factory(2)
                    ->has(Comment::factory(2), 'comments'),
                'posts'
            )
            ->create();
    }
}
