<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuizSession;
use App\Models\ExamSession;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Exam;
use App\Models\Enrollment;
use Carbon\Carbon;

class QuizSessionSeeder extends Seeder
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

        $quizzes = Quiz::all();
        $exams = Exam::all();
        $enrollments = Enrollment::all();

        if ($students->isEmpty() || $quizzes->isEmpty()) {
            $this->command->info('No students or quizzes found. Please run UserSeeder and QuizSeeder first.');
            return;
        }

        // Quiz Sessions
        $quizSessions = [
            // Rihem Kochti - HTML & CSS Quiz
            [
                'user_id' => $students->where('name', 'Rihem Kochti')->first()?->id,
                'quiz_id' => $quizzes->first()?->id,
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Rihem Kochti')->first()?->id)->first()?->id,
                'score' => 95,
                'total_questions' => 3,
                'correct_answers' => 3,
                'time_taken' => 12, // minutes
                'status' => 'completed',
                'started_at' => Carbon::now()->subDays(25),
                'completed_at' => Carbon::now()->subDays(25)->addMinutes(12),
                'created_at' => Carbon::now()->subDays(25),
                'updated_at' => Carbon::now()->subDays(25),
            ],
            [
                'user_id' => $students->where('name', 'Rihem Kochti')->first()?->id,
                'quiz_id' => $quizzes->skip(1)->first()?->id, // JavaScript Quiz
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Rihem Kochti')->first()?->id)->skip(1)->first()?->id,
                'score' => 85,
                'total_questions' => 2,
                'correct_answers' => 2,
                'time_taken' => 18,
                'status' => 'completed',
                'started_at' => Carbon::now()->subDays(15),
                'completed_at' => Carbon::now()->subDays(15)->addMinutes(18),
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            [
                'user_id' => $students->where('name', 'Rihem Kochti')->first()?->id,
                'quiz_id' => $quizzes->skip(2)->first()?->id, // React Quiz
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Rihem Kochti')->first()?->id)->skip(2)->first()?->id,
                'score' => 92,
                'total_questions' => 2,
                'correct_answers' => 2,
                'time_taken' => 20,
                'status' => 'completed',
                'started_at' => Carbon::now()->subDays(40),
                'completed_at' => Carbon::now()->subDays(40)->addMinutes(20),
                'created_at' => Carbon::now()->subDays(40),
                'updated_at' => Carbon::now()->subDays(40),
            ],

            // Ahmed Benali Sessions
            [
                'user_id' => $students->where('name', 'Ahmed Benali')->first()?->id,
                'quiz_id' => $quizzes->first()?->id, // HTML Quiz
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Ahmed Benali')->first()?->id)->first()?->id,
                'score' => 88,
                'total_questions' => 3,
                'correct_answers' => 3,
                'time_taken' => 14,
                'status' => 'completed',
                'started_at' => Carbon::now()->subDays(20),
                'completed_at' => Carbon::now()->subDays(20)->addMinutes(14),
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(20),
            ],
            [
                'user_id' => $students->where('name', 'Ahmed Benali')->first()?->id,
                'quiz_id' => $quizzes->skip(3)->first()?->id, // Python Quiz
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Ahmed Benali')->first()?->id)->skip(1)->first()?->id,
                'score' => 95,
                'total_questions' => 2,
                'correct_answers' => 2,
                'time_taken' => 25,
                'status' => 'completed',
                'started_at' => Carbon::now()->subDays(55),
                'completed_at' => Carbon::now()->subDays(55)->addMinutes(25),
                'created_at' => Carbon::now()->subDays(55),
                'updated_at' => Carbon::now()->subDays(55),
            ],

            // Fatma Khelifi Sessions
            [
                'user_id' => $students->where('name', 'Fatma Khelifi')->first()?->id,
                'quiz_id' => $quizzes->skip(1)->first()?->id, // JavaScript Quiz
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Fatma Khelifi')->first()?->id)->first()?->id,
                'score' => 75,
                'total_questions' => 2,
                'correct_answers' => 1,
                'time_taken' => 19,
                'status' => 'completed',
                'started_at' => Carbon::now()->subDays(10),
                'completed_at' => Carbon::now()->subDays(10)->addMinutes(19),
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(10),
            ],

            // Mohamed Trabelsi Sessions
            [
                'user_id' => $students->where('name', 'Mohamed Trabelsi')->first()?->id,
                'quiz_id' => $quizzes->skip(2)->first()?->id, // React Quiz
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Mohamed Trabelsi')->first()?->id)->first()?->id,
                'score' => 89,
                'total_questions' => 2,
                'correct_answers' => 2,
                'time_taken' => 22,
                'status' => 'completed',
                'started_at' => Carbon::now()->subDays(30),
                'completed_at' => Carbon::now()->subDays(30)->addMinutes(22),
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(30),
            ],

            // Lina Touati Sessions
            [
                'user_id' => $students->where('name', 'Lina Touati')->first()?->id,
                'quiz_id' => $quizzes->skip(2)->first()?->id, // React Quiz
                'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Lina Touati')->first()?->id)->first()?->id,
                'score' => 92,
                'total_questions' => 2,
                'correct_answers' => 2,
                'time_taken' => 21,
                'status' => 'completed',
                'started_at' => Carbon::now()->subDays(50),
                'completed_at' => Carbon::now()->subDays(50)->addMinutes(21),
                'created_at' => Carbon::now()->subDays(50),
                'updated_at' => Carbon::now()->subDays(50),
            ],
        ];

        foreach ($quizSessions as $session) {
            if ($session['user_id'] && $session['quiz_id'] && $session['enrollment_id']) {
                QuizSession::create($session);
            }
        }

        // Exam Sessions
        if (!$exams->isEmpty()) {
            $examSessions = [
                // Rihem Kochti - HTML Exam
                [
                    'user_id' => $students->where('name', 'Rihem Kochti')->first()?->id,
                    'exam_id' => $exams->first()?->id,
                    'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Rihem Kochti')->first()?->id)->first()?->id,
                    'score' => 92,
                    'total_questions' => 3,
                    'correct_answers' => 3,
                    'time_taken' => 55,
                    'status' => 'completed',
                    'started_at' => Carbon::now()->subDays(20),
                    'completed_at' => Carbon::now()->subDays(20)->addMinutes(55),
                    'created_at' => Carbon::now()->subDays(20),
                    'updated_at' => Carbon::now()->subDays(20),
                ],
                [
                    'user_id' => $students->where('name', 'Rihem Kochti')->first()?->id,
                    'exam_id' => $exams->skip(2)->first()?->id, // React Exam
                    'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Rihem Kochti')->first()?->id)->skip(2)->first()?->id,
                    'score' => 95,
                    'total_questions' => 3,
                    'correct_answers' => 3,
                    'time_taken' => 70,
                    'status' => 'completed',
                    'started_at' => Carbon::now()->subDays(35),
                    'completed_at' => Carbon::now()->subDays(35)->addMinutes(70),
                    'created_at' => Carbon::now()->subDays(35),
                    'updated_at' => Carbon::now()->subDays(35),
                ],

                // Ahmed Benali - Python Exam
                [
                    'user_id' => $students->where('name', 'Ahmed Benali')->first()?->id,
                    'exam_id' => $exams->skip(3)->first()?->id, // Python Exam
                    'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Ahmed Benali')->first()?->id)->skip(1)->first()?->id,
                    'score' => 89,
                    'total_questions' => 2,
                    'correct_answers' => 2,
                    'time_taken' => 110,
                    'status' => 'completed',
                    'started_at' => Carbon::now()->subDays(50),
                    'completed_at' => Carbon::now()->subDays(50)->addMinutes(110),
                    'created_at' => Carbon::now()->subDays(50),
                    'updated_at' => Carbon::now()->subDays(50),
                ],

                // Lina Touati - React Exam
                [
                    'user_id' => $students->where('name', 'Lina Touati')->first()?->id,
                    'exam_id' => $exams->skip(2)->first()?->id, // React Exam
                    'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Lina Touati')->first()?->id)->first()?->id,
                    'score' => 88,
                    'total_questions' => 3,
                    'correct_answers' => 3,
                    'time_taken' => 68,
                    'status' => 'completed',
                    'started_at' => Carbon::now()->subDays(45),
                    'completed_at' => Carbon::now()->subDays(45)->addMinutes(68),
                    'created_at' => Carbon::now()->subDays(45),
                    'updated_at' => Carbon::now()->subDays(45),
                ],

                // Mohamed Trabelsi - Node.js Exam
                [
                    'user_id' => $students->where('name', 'Mohamed Trabelsi')->first()?->id,
                    'exam_id' => $exams->skip(2)->first()?->id, // React Exam (as substitute)
                    'enrollment_id' => $enrollments->where('user_id', $students->where('name', 'Mohamed Trabelsi')->first()?->id)->skip(1)->first()?->id,
                    'score' => 91,
                    'total_questions' => 3,
                    'correct_answers' => 3,
                    'time_taken' => 72,
                    'status' => 'completed',
                    'started_at' => Carbon::now()->subDays(40),
                    'completed_at' => Carbon::now()->subDays(40)->addMinutes(72),
                    'created_at' => Carbon::now()->subDays(40),
                    'updated_at' => Carbon::now()->subDays(40),
                ],
            ];

            foreach ($examSessions as $session) {
                if ($session['user_id'] && $session['exam_id'] && $session['enrollment_id']) {
                    ExamSession::create($session);
                }
            }
        }

        $this->command->info('Quiz and Exam session data seeded successfully!');
    }
}
