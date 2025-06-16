<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $teacherRole = Role::where('name', 'teacher')->first();
        $studentRole = Role::where('name', 'student')->first();

        // Create Admin User
        $admin = User::create([
            'name' => 'Admin Gold LMS',
            'email' => 'admin@goldlms.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password123'),
            'status' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        if ($adminRole) {
            $admin->assignRole($adminRole);
        }

        // Create Teachers
        $teachers = [
            [
                'name' => 'Prof. Ahmed Benali',
                'email' => 'ahmed.benali@goldlms.com',
                'password' => Hash::make('teacher123'),
            ],
            [
                'name' => 'Prof. Fatma Khelifi',
                'email' => 'fatma.khelifi@goldlms.com',
                'password' => Hash::make('teacher123'),
            ],
            [
                'name' => 'Prof. Mohamed Trabelsi',
                'email' => 'mohamed.trabelsi@goldlms.com',
                'password' => Hash::make('teacher123'),
            ],
        ];

        foreach ($teachers as $teacherData) {
            $teacherData['email_verified_at'] = Carbon::now();
            $teacherData['status'] = 1;
            $teacherData['created_at'] = Carbon::now();
            $teacherData['updated_at'] = Carbon::now();
            $teacher = User::create($teacherData);
            
            if ($teacherRole) {
                $teacher->assignRole($teacherRole);
            }
        }

        // Create Students
        $students = [
            [
                'name' => 'Rihem Kochti',
                'email' => 'rihem.kochti@student.com',
                'password' => Hash::make('student123'),
            ],
            [
                'name' => 'Ahmed Benali',
                'email' => 'ahmed.benali@student.com',
                'password' => Hash::make('student123'),
            ],
            [
                'name' => 'Fatma Khelifi',
                'email' => 'fatma.khelifi@student.com',
                'password' => Hash::make('student123'),
            ],
            [
                'name' => 'Mohamed Trabelsi',
                'email' => 'mohamed.trabelsi@student.com',
                'password' => Hash::make('student123'),
            ],
            [
                'name' => 'Lina Touati',
                'email' => 'lina.touati@student.com',
                'password' => Hash::make('student123'),
            ],
            [
                'name' => 'Youssef Mansouri',
                'email' => 'youssef.mansouri@student.com',
                'password' => Hash::make('student123'),
            ],
            [
                'name' => 'Amina Rahali',
                'email' => 'amina.rahali@student.com',
                'password' => Hash::make('student123'),
            ],
        ];

        foreach ($students as $studentData) {
            $studentData['email_verified_at'] = Carbon::now();
            $studentData['status'] = 1;
            $studentData['created_at'] = Carbon::now();
            $studentData['updated_at'] = Carbon::now();
            $student = User::create($studentData);
            
            if ($studentRole) {
                $student->assignRole($studentRole);
            }
        }

        $this->command->info('User data seeded successfully!');
    }
}
