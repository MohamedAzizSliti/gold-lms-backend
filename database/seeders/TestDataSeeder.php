<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Create an instructor
        $instructor = User::create([
            'name' => 'Test Instructor',
            'email' => 'instructor@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Create a course
        Course::create([
            'title' => 'Programming Basics',
            'description' => 'Learn the basics of programming',
            'slug' => Str::slug('Programming Basics'),
            'price' => 99.99,
            'status' => 'published',
            'instructor_id' => $instructor->id,
        ]);
    }
}
