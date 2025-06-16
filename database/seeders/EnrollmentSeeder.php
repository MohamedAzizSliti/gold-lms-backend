<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Course;
use Carbon\Carbon;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get students and courses
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'student');
        })->get();

        $courses = Course::all();

        if ($students->isEmpty() || $courses->isEmpty()) {
            $this->command->info('No students or courses found. Please run UserSeeder and CourseSeeder first.');
            return;
        }

        $enrollments = [
            // Student 1 - Rihem Kochti
            [
                'user_id' => $students->where('name', 'Rihem Kochti')->first()?->id ?? $students->first()->id,
                'course_id' => $courses->where('title', 'Formation HTML & CSS Avancé')->first()?->id ?? $courses->first()->id,
                'progress' => 100,
                'status' => 'active',
                'course_price' => 150.00,
                'amount_paid' => 150.00,
                'payment_method' => 'credit_card',
                'discount_amount' => 0.00,
                'enrollment_date' => Carbon::now()->subDays(30),
                'last_activity' => Carbon::now()->subDays(1),
                'is_certificate_downloaded' => 1,
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $students->where('name', 'Rihem Kochti')->first()?->id ?? $students->first()->id,
                'course_id' => $courses->where('title', 'JavaScript ES6 Moderne')->first()?->id ?? $courses->skip(1)->first()->id,
                'progress' => 75,
                'status' => 'active',
                'course_price' => 200.00,
                'amount_paid' => 180.00,
                'payment_method' => 'paypal',
                'discount_amount' => 20.00,
                'enrollment_date' => Carbon::now()->subDays(20),
                'last_activity' => Carbon::now()->subDays(2),
                'is_certificate_downloaded' => 0,
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $students->where('name', 'Rihem Kochti')->first()?->id ?? $students->first()->id,
                'course_id' => $courses->where('title', 'React.js Fundamentals')->first()?->id ?? $courses->skip(2)->first()->id,
                'progress' => 100,
                'status' => 'completed',
                'course_price' => 180.00,
                'amount_paid' => 180.00,
                'payment_method' => 'bank_transfer',
                'discount_amount' => 0.00,
                'enrollment_date' => Carbon::now()->subDays(45),
                'last_activity' => Carbon::now()->subDays(3),
                'is_certificate_downloaded' => 1,
                'created_at' => Carbon::now()->subDays(45),
                'updated_at' => Carbon::now()->subDays(3),
            ],

            // Student 2 - Ahmed Benali
            [
                'user_id' => $students->where('name', 'Ahmed Benali')->first()?->id ?? $students->skip(1)->first()->id,
                'course_id' => $courses->where('title', 'Formation HTML & CSS Avancé')->first()?->id ?? $courses->first()->id,
                'progress' => 88,
                'status' => 'active',
                'course_price' => 150.00,
                'amount_paid' => 135.00,
                'payment_method' => 'credit_card',
                'discount_amount' => 15.00,
                'enrollment_date' => Carbon::now()->subDays(25),
                'last_activity' => Carbon::now()->subDays(1),
                'is_certificate_downloaded' => 0,
                'created_at' => Carbon::now()->subDays(25),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $students->where('name', 'Ahmed Benali')->first()?->id ?? $students->skip(1)->first()->id,
                'course_id' => $courses->where('title', 'Python Programming')->first()?->id ?? $courses->skip(3)->first()->id,
                'progress' => 100,
                'status' => 'completed',
                'course_price' => 220.00,
                'amount_paid' => 220.00,
                'payment_method' => 'paypal',
                'discount_amount' => 0.00,
                'enrollment_date' => Carbon::now()->subDays(60),
                'last_activity' => Carbon::now()->subDays(5),
                'is_certificate_downloaded' => 1,
                'created_at' => Carbon::now()->subDays(60),
                'updated_at' => Carbon::now()->subDays(5),
            ],

            // Student 3 - Fatma Khelifi
            [
                'user_id' => $students->where('name', 'Fatma Khelifi')->first()?->id ?? $students->skip(2)->first()->id,
                'course_id' => $courses->where('title', 'JavaScript ES6 Moderne')->first()?->id ?? $courses->skip(1)->first()->id,
                'progress' => 75,
                'status' => 'active',
                'course_price' => 200.00,
                'amount_paid' => 170.00,
                'payment_method' => 'credit_card',
                'discount_amount' => 30.00,
                'enrollment_date' => Carbon::now()->subDays(15),
                'last_activity' => Carbon::now(),
                'is_certificate_downloaded' => 0,
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => $students->where('name', 'Fatma Khelifi')->first()?->id ?? $students->skip(2)->first()->id,
                'course_id' => $courses->where('title', 'Database Design')->first()?->id ?? $courses->skip(4)->first()->id,
                'progress' => 100,
                'status' => 'completed',
                'course_price' => 190.00,
                'amount_paid' => 190.00,
                'payment_method' => 'bank_transfer',
                'discount_amount' => 0.00,
                'enrollment_date' => Carbon::now()->subDays(40),
                'last_activity' => Carbon::now()->subDays(7),
                'is_certificate_downloaded' => 1,
                'created_at' => Carbon::now()->subDays(40),
                'updated_at' => Carbon::now()->subDays(7),
            ],

            // Student 4 - Mohamed Trabelsi
            [
                'user_id' => $students->where('name', 'Mohamed Trabelsi')->first()?->id ?? $students->skip(3)->first()->id,
                'course_id' => $courses->where('title', 'React.js Fundamentals')->first()?->id ?? $courses->skip(2)->first()->id,
                'progress' => 92,
                'status' => 'active',
                'course_price' => 180.00,
                'amount_paid' => 162.00,
                'payment_method' => 'paypal',
                'discount_amount' => 18.00,
                'enrollment_date' => Carbon::now()->subDays(35),
                'last_activity' => Carbon::now()->subDays(2),
                'is_certificate_downloaded' => 0,
                'created_at' => Carbon::now()->subDays(35),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $students->where('name', 'Mohamed Trabelsi')->first()?->id ?? $students->skip(3)->first()->id,
                'course_id' => $courses->where('title', 'Node.js Backend')->first()?->id ?? $courses->skip(5)->first()->id,
                'progress' => 100,
                'status' => 'completed',
                'course_price' => 250.00,
                'amount_paid' => 250.00,
                'payment_method' => 'credit_card',
                'discount_amount' => 0.00,
                'enrollment_date' => Carbon::now()->subDays(50),
                'last_activity' => Carbon::now()->subDays(10),
                'is_certificate_downloaded' => 1,
                'created_at' => Carbon::now()->subDays(50),
                'updated_at' => Carbon::now()->subDays(10),
            ],

            // Student 5 - Lina Touati
            [
                'user_id' => $students->where('name', 'Lina Touati')->first()?->id ?? $students->skip(4)->first()->id,
                'course_id' => $courses->where('title', 'React.js Fundamentals')->first()?->id ?? $courses->skip(2)->first()->id,
                'progress' => 100,
                'status' => 'completed',
                'course_price' => 180.00,
                'amount_paid' => 180.00,
                'payment_method' => 'bank_transfer',
                'discount_amount' => 0.00,
                'enrollment_date' => Carbon::now()->subDays(55),
                'last_activity' => Carbon::now()->subDays(15),
                'is_certificate_downloaded' => 1,
                'created_at' => Carbon::now()->subDays(55),
                'updated_at' => Carbon::now()->subDays(15),
            ],

            // Additional enrollments for more data
            [
                'user_id' => $students->skip(5)->first()?->id ?? $students->first()->id,
                'course_id' => $courses->first()->id,
                'progress' => 45,
                'status' => 'active',
                'course_price' => 150.00,
                'amount_paid' => 150.00,
                'payment_method' => 'credit_card',
                'discount_amount' => 0.00,
                'enrollment_date' => Carbon::now()->subDays(10),
                'last_activity' => Carbon::now()->subDays(1),
                'is_certificate_downloaded' => 0,
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $students->skip(6)->first()?->id ?? $students->first()->id,
                'course_id' => $courses->skip(1)->first()->id,
                'progress' => 30,
                'status' => 'pending',
                'course_price' => 200.00,
                'amount_paid' => 0.00,
                'payment_method' => null,
                'discount_amount' => 0.00,
                'enrollment_date' => Carbon::now()->subDays(5),
                'last_activity' => Carbon::now()->subDays(3),
                'is_certificate_downloaded' => 0,
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $students->skip(7)->first()?->id ?? $students->first()->id,
                'course_id' => $courses->skip(2)->first()->id,
                'progress' => 100,
                'status' => 'completed',
                'course_price' => 180.00,
                'amount_paid' => 180.00,
                'payment_method' => 'paypal',
                'discount_amount' => 0.00,
                'enrollment_date' => Carbon::now()->subDays(65),
                'last_activity' => Carbon::now()->subDays(20),
                'is_certificate_downloaded' => 1,
                'created_at' => Carbon::now()->subDays(65),
                'updated_at' => Carbon::now()->subDays(20),
            ],
        ];

        foreach ($enrollments as $enrollment) {
            Enrollment::create($enrollment);
        }

        $this->command->info('Enrollment data seeded successfully!');
    }
}
