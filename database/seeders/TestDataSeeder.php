<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Create roles if they don't exist
        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        }
        
        $instructorRole = Role::where('name', 'instructor')->first();
        if (!$instructorRole) {
            $instructorRole = Role::create(['name' => 'instructor', 'guard_name' => 'web']);
        }
        
        $studentRole = Role::where('name', 'student')->first();
        if (!$studentRole) {
            $studentRole = Role::create(['name' => 'student', 'guard_name' => 'web']);
        }

        // Create users
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        
        // Assign role if not already assigned
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }

        $instructor1 = User::firstOrCreate(
            ['email' => 'instructor@example.com'],
            [
                'name' => 'John Instructor',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        
        if (!$instructor1->hasRole('instructor')) {
            $instructor1->assignRole($instructorRole);
        }

        $instructor2 = User::firstOrCreate(
            ['email' => 'instructor2@example.com'],
            [
                'name' => 'Jane Teacher',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        
        if (!$instructor2->hasRole('instructor')) {
            $instructor2->assignRole($instructorRole);
        }

        $student = User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Sam Student',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        
        if (!$student->hasRole('student')) {
            $student->assignRole($studentRole);
        }

        // Create categories
        try {
            $webDevCategory = Category::firstOrCreate(
                ['slug' => 'web-development'],
                [
                    'name' => 'Web Development',
                    'description' => 'Learn web development technologies',
                    'status' => 1,
                    'type' => 'course', 
                    'created_by_id' => $admin->id
                ]
            );

            $dataCategory = Category::firstOrCreate(
                ['slug' => 'data-science'],
                [
                    'name' => 'Data Science',
                    'description' => 'Learn data science and analytics',
                    'status' => 1,
                    'type' => 'course',
                    'created_by_id' => $admin->id
                ]
            );
            
            echo "Categories created successfully!\n";
        } catch (\Exception $e) {
            echo "Error creating categories: " . $e->getMessage() . "\n";
        }

        echo "Test data seeding completed successfully!\n";
    }
}
