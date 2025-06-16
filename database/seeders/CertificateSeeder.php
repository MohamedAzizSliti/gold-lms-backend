<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Certificate;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use Carbon\Carbon;

class CertificateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'student');
        })->get();

        $courses = Course::all();
        $enrollments = Enrollment::where('progress', 100)->get();

        if ($students->isEmpty() || $courses->isEmpty()) {
            $this->command->info('No students or courses found. Please run UserSeeder and CourseSeeder first.');
            return;
        }

        $certificates = [
            // Rihem Kochti Certificates
            [
                'user_id' => $students->where('name', 'Rihem Kochti')->first()?->id,
                'course_id' => $courses->where('title', 'Formation HTML & CSS Avancé')->first()?->id,
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Rihem Kochti')->first()?->id)
                    ->where('course_id', $courses->where('title', 'Formation HTML & CSS Avancé')->first()?->id)->first()?->id,
                'certificate_id' => 'CERT-ESSECT-' . str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT),
                'issued_date' => Carbon::now()->subDays(20),
                'score' => 95,
                'grade' => 'Excellent',
                'status' => 'issued',
                'template_data' => json_encode([
                    'student_name' => 'Rihem Kochti',
                    'course_name' => 'Formation HTML & CSS Avancé',
                    'completion_date' => Carbon::now()->subDays(20)->format('d/m/Y'),
                    'score' => '95%',
                    'evaluation' => '3/3 Réussi',
                    'organization' => 'GOLD LMS',
                    'direction' => 'DIRECTION ESSECT'
                ]),
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(20),
            ],
            [
                'user_id' => $students->where('name', 'Rihem Kochti')->first()?->id,
                'course_id' => $courses->where('title', 'React.js Fundamentals')->first()?->id,
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Rihem Kochti')->first()?->id)
                    ->where('course_id', $courses->where('title', 'React.js Fundamentals')->first()?->id)->first()?->id,
                'certificate_id' => 'CERT-ESSECT-' . str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT),
                'issued_date' => Carbon::now()->subDays(35),
                'score' => 92,
                'grade' => 'Excellent',
                'status' => 'issued',
                'template_data' => json_encode([
                    'student_name' => 'Rihem Kochti',
                    'course_name' => 'React.js Fundamentals',
                    'completion_date' => Carbon::now()->subDays(35)->format('d/m/Y'),
                    'score' => '92%',
                    'evaluation' => '3/3 Réussi',
                    'organization' => 'GOLD LMS',
                    'direction' => 'DIRECTION ESSECT'
                ]),
                'created_at' => Carbon::now()->subDays(35),
                'updated_at' => Carbon::now()->subDays(35),
            ],

            // Ahmed Benali Certificate
            [
                'user_id' => $students->where('name', 'Ahmed Benali')->first()?->id,
                'course_id' => $courses->where('title', 'Python Programming')->first()?->id,
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Ahmed Benali')->first()?->id)
                    ->where('course_id', $courses->where('title', 'Python Programming')->first()?->id)->first()?->id,
                'certificate_id' => 'CERT-ESSECT-' . str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT),
                'issued_date' => Carbon::now()->subDays(50),
                'score' => 89,
                'grade' => 'Très Bien',
                'status' => 'issued',
                'template_data' => json_encode([
                    'student_name' => 'Ahmed Benali',
                    'course_name' => 'Python Programming',
                    'completion_date' => Carbon::now()->subDays(50)->format('d/m/Y'),
                    'score' => '89%',
                    'evaluation' => '3/3 Réussi',
                    'organization' => 'GOLD LMS',
                    'direction' => 'DIRECTION ESSECT'
                ]),
                'created_at' => Carbon::now()->subDays(50),
                'updated_at' => Carbon::now()->subDays(50),
            ],

            // Fatma Khelifi Certificate
            [
                'user_id' => $students->where('name', 'Fatma Khelifi')->first()?->id,
                'course_id' => $courses->where('title', 'Database Design')->first()?->id,
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Fatma Khelifi')->first()?->id)
                    ->where('course_id', $courses->where('title', 'Database Design')->first()?->id)->first()?->id,
                'certificate_id' => 'CERT-ESSECT-' . str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT),
                'issued_date' => Carbon::now()->subDays(30),
                'score' => 87,
                'grade' => 'Très Bien',
                'status' => 'issued',
                'template_data' => json_encode([
                    'student_name' => 'Fatma Khelifi',
                    'course_name' => 'Database Design',
                    'completion_date' => Carbon::now()->subDays(30)->format('d/m/Y'),
                    'score' => '87%',
                    'evaluation' => '3/3 Réussi',
                    'organization' => 'GOLD LMS',
                    'direction' => 'DIRECTION ESSECT'
                ]),
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(30),
            ],

            // Mohamed Trabelsi Certificate
            [
                'user_id' => $students->where('name', 'Mohamed Trabelsi')->first()?->id,
                'course_id' => $courses->where('title', 'Node.js Backend')->first()?->id,
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Mohamed Trabelsi')->first()?->id)
                    ->where('course_id', $courses->where('title', 'Node.js Backend')->first()?->id)->first()?->id,
                'certificate_id' => 'CERT-ESSECT-' . str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT),
                'issued_date' => Carbon::now()->subDays(40),
                'score' => 91,
                'grade' => 'Excellent',
                'status' => 'issued',
                'template_data' => json_encode([
                    'student_name' => 'Mohamed Trabelsi',
                    'course_name' => 'Node.js Backend',
                    'completion_date' => Carbon::now()->subDays(40)->format('d/m/Y'),
                    'score' => '91%',
                    'evaluation' => '3/3 Réussi',
                    'organization' => 'GOLD LMS',
                    'direction' => 'DIRECTION ESSECT'
                ]),
                'created_at' => Carbon::now()->subDays(40),
                'updated_at' => Carbon::now()->subDays(40),
            ],

            // Lina Touati Certificate
            [
                'user_id' => $students->where('name', 'Lina Touati')->first()?->id,
                'course_id' => $courses->where('title', 'React.js Fundamentals')->first()?->id,
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Lina Touati')->first()?->id)
                    ->where('course_id', $courses->where('title', 'React.js Fundamentals')->first()?->id)->first()?->id,
                'certificate_id' => 'CERT-ESSECT-' . str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT),
                'issued_date' => Carbon::now()->subDays(45),
                'score' => 88,
                'grade' => 'Très Bien',
                'status' => 'issued',
                'template_data' => json_encode([
                    'student_name' => 'Lina Touati',
                    'course_name' => 'React.js Fundamentals',
                    'completion_date' => Carbon::now()->subDays(45)->format('d/m/Y'),
                    'score' => '88%',
                    'evaluation' => '3/3 Réussi',
                    'organization' => 'GOLD LMS',
                    'direction' => 'DIRECTION ESSECT'
                ]),
                'created_at' => Carbon::now()->subDays(45),
                'updated_at' => Carbon::now()->subDays(45),
            ],
        ];

        foreach ($certificates as $certificate) {
            if ($certificate['user_id'] && $certificate['course_id'] && $certificate['enrollment_id']) {
                Certificate::create($certificate);
            }
        }

        $this->command->info('Certificate data seeded successfully!');
    }
}
