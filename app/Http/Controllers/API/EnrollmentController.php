<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    /**
     * Enroll a student in a course
     */
    public function enroll(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'user_id' => 'sometimes|exists:users,id', // Optional, defaults to authenticated user
        ]);
        
        // Default to current user if not specified
        $user_id = $request->user_id ?? Auth::id();
        
        // Check if user has student role
        $user = User::findOrFail($user_id);
        if (!$user->hasRole('student')) {
            return response()->json(['message' => 'User must be a student to enroll in courses'], 403);
        }
        
        // Check if already enrolled
        $existingEnrollment = Enrollment::where('user_id', $user_id)
            ->where('course_id', $request->course_id)
            ->first();
            
        if ($existingEnrollment) {
            return response()->json(['message' => 'User is already enrolled in this course'], 422);
        }
        
        // Check course price and handle payment if needed
        $course = Course::findOrFail($request->course_id);
        
        // If the course is not free and there's no payment record, return error
        if ($course->price > 0 && !$request->has('payment_id')) {
            return response()->json([
                'message' => 'This course requires payment before enrollment',
                'course_price' => $course->price,
            ], 402); // 402 Payment Required
        }
        
        // Create enrollment
        $enrollment = Enrollment::create([
            'user_id' => $user_id,
            'course_id' => $request->course_id,
            'payment_id' => $request->payment_id ?? null,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);
        
        // Process charity donation (3% of course price)
        if ($course->price > 0) {
            $charityAmount = $course->price * 0.03;
            $instructorAmount = $course->price * 0.97;
            
            // Record the revenue split
            // This would normally involve a transaction and actual payment processing
            // For this example, we're just recording the intended amounts
            $revenue = $course->revenue()->create([
                'enrollment_id' => $enrollment->id,
                'course_id' => $course->id,
                'total_amount' => $course->price,
                'instructor_amount' => $instructorAmount,
                'platform_fee' => 0, // No platform fee in this case
                'charity_amount' => $charityAmount,
                'instructor_id' => $course->instructor_id,
                'payment_date' => now(),
                'payment_method' => $request->payment_method ?? 'unknown',
            ]);
        }
        
        return response()->json([
            'message' => 'Successfully enrolled in the course',
            'enrollment' => $enrollment->load('course'),
        ], 201);
    }
    
    /**
     * List all courses a student is enrolled in
     */
    public function myCourses(Request $request)
    {
        $user_id = $request->user_id ?? Auth::id();
        
        $enrollments = Enrollment::with(['course', 'course.instructor'])
            ->where('user_id', $user_id)
            ->where('status', 'active')
            ->paginate($request->per_page ?? 10);
            
        return response()->json($enrollments);
    }
    
    /**
     * Get course progress for a student
     */
    public function courseProgress(Request $request, $courseId)
    {
        $user_id = $request->user_id ?? Auth::id();
        
        // Verify enrollment
        $enrollment = Enrollment::where('user_id', $user_id)
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->firstOrFail();
            
        // Get course content
        $course = Course::with([
            'chapters',
            'chapters.contents',
            'quizzes',
            'quizzes.questions',
            'exams',
            'exams.questions'
        ])->findOrFail($courseId);
        
        // Calculate progress
        $totalItems = count($course->chapters) + count($course->quizzes) + count($course->exams);
        $completedItems = 0;
        
        // Count completed quizzes
        $completedQuizzes = $enrollment->quizSessions()
            ->where('status', 'completed')
            ->distinct('quiz_id')
            ->count();
            
        // Count completed exams
        $completedExams = $enrollment->examSessions()
            ->where('status', 'completed')
            ->distinct('exam_id')
            ->count();
            
        // Count completed chapters (based on content view)
        $completedChapters = 0; // Implement chapter completion logic
        
        $completedItems = $completedQuizzes + $completedExams + $completedChapters;
        
        $progressPercentage = $totalItems > 0 ? ($completedItems / $totalItems) * 100 : 0;
        
        return response()->json([
            'enrollment' => $enrollment,
            'progress' => [
                'percentage' => round($progressPercentage, 2),
                'completed_items' => $completedItems,
                'total_items' => $totalItems,
                'completed_chapters' => $completedChapters,
                'completed_quizzes' => $completedQuizzes,
                'completed_exams' => $completedExams,
            ]
        ]);
    }
    
    /**
     * Cancel enrollment
     */
    public function cancel(Request $request, $enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        
        // Check permissions (only own enrollments or admin)
        if (Auth::id() != $enrollment->user_id && !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $enrollment->status = 'cancelled';
        $enrollment->save();
        
        return response()->json([
            'message' => 'Enrollment cancelled successfully',
            'enrollment' => $enrollment
        ]);
    }
    
    /**
     * Get course content with exams and quizzes
     */
    public function courseContent(Request $request, $courseId)
    {
        $user_id = $request->user_id ?? Auth::id();
        
        // Verify enrollment
        $enrollment = Enrollment::where('user_id', $user_id)
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->first();
            
        if (!$enrollment && !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }
        
        // Get course content with chapters, quizzes, exams
        $course = Course::with([
            'chapters',
            'chapters.contents',
            'quizzes',
            'exams'
        ])->findOrFail($courseId);
        
        // Format quizzes with basic info (no answers)
        $quizzes = $course->quizzes->map(function($quiz) use ($enrollment) {
            // Check if user has any quiz attempts
            $attempts = 0;
            $bestScore = null;
            $status = 'not_started';
            
            if ($enrollment) {
                $sessions = $enrollment->quizSessions()
                    ->where('quiz_id', $quiz->id)
                    ->get();
                    
                $attempts = $sessions->count();
                
                if ($attempts > 0) {
                    $bestScore = $sessions->max('score');
                    $status = $sessions->where('status', 'completed')->count() > 0 ? 'completed' : 'in_progress';
                }
            }
            
            return [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'duration' => $quiz->duration,
                'questions_count' => $quiz->questions()->count(),
                'attempts' => $attempts,
                'best_score' => $bestScore,
                'status' => $status,
            ];
        });
        
        // Format exams with basic info (no answers)
        $exams = $course->exams->map(function($exam) use ($enrollment) {
            // Check if user has any exam attempts
            $attempts = 0;
            $bestScore = null;
            $status = 'not_started';
            $passed = false;
            
            if ($enrollment) {
                $sessions = $enrollment->examSessions()
                    ->where('exam_id', $exam->id)
                    ->get();
                    
                $attempts = $sessions->count();
                
                if ($attempts > 0) {
                    $bestScore = $sessions->max('score');
                    $status = $sessions->where('status', 'completed')->count() > 0 ? 'completed' : 'in_progress';
                    $passed = $sessions->where('passed', true)->count() > 0;
                }
            }
            
            return [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'duration' => $exam->duration,
                'questions_count' => $exam->questions()->count(),
                'pass_percentage' => $exam->pass_percentage,
                'attempts' => $attempts,
                'best_score' => $bestScore,
                'status' => $status,
                'passed' => $passed,
                'multi_chance' => $exam->multi_chance,
            ];
        });
        
        // Format course data
        $courseData = [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'instructor' => $course->instructor ? [
                'id' => $course->instructor->id,
                'name' => $course->instructor->name,
            ] : null,
            'chapters' => $course->chapters->map(function($chapter) {
                return [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'order' => $chapter->order,
                    'contents' => $chapter->contents,
                ];
            }),
            'quizzes' => $quizzes,
            'exams' => $exams,
            'progress' => null,
        ];
        
        // Add progress if enrolled
        if ($enrollment) {
            // Reuse logic from courseProgress method
            $totalItems = count($course->chapters) + count($course->quizzes) + count($course->exams);
            $completedQuizzes = $enrollment->quizSessions()
                ->where('status', 'completed')
                ->distinct('quiz_id')
                ->count();
                
            $completedExams = $enrollment->examSessions()
                ->where('status', 'completed')
                ->distinct('exam_id')
                ->count();
                
            $completedChapters = 0; // Implement chapter completion logic
            
            $completedItems = $completedQuizzes + $completedExams + $completedChapters;
            $progressPercentage = $totalItems > 0 ? ($completedItems / $totalItems) * 100 : 0;
            
            $courseData['progress'] = [
                'percentage' => round($progressPercentage, 2),
                'completed_items' => $completedItems,
                'total_items' => $totalItems,
            ];
            
            $courseData['enrollment'] = [
                'id' => $enrollment->id,
                'enrolled_at' => $enrollment->enrollment_date,
                'status' => $enrollment->status,
            ];
        }
        
        return response()->json($courseData);
    }
    
    /**
     * Get all completed quiz and exam results for the student
     */
    public function completedResults(Request $request)
    {
        $user_id = $request->user_id ?? Auth::id();
        
        // Check if requesting other user's results (admin/instructor only)
        if ($user_id != Auth::id() && !Auth::user()->hasAnyRole(['admin', 'instructor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Get enrollments
        $enrollments = Enrollment::where('user_id', $user_id)
            ->where('status', 'active')
            ->pluck('id');
            
        // Get completed quiz sessions
        $quizSessions = \App\Models\QuizSession::with(['quiz:id,title,course_id', 'quiz.course:id,title'])
            ->whereIn('enrollment_id', $enrollments)
            ->where('status', 'completed')
            ->latest('submitted_at')
            ->get()
            ->map(function($session) {
                return [
                    'type' => 'quiz',
                    'id' => $session->id,
                    'session_id' => $session->id,
                    'title' => $session->quiz->title ?? 'Unknown Quiz',
                    'course' => $session->quiz->course->title ?? 'Unknown Course',
                    'course_id' => $session->quiz->course_id ?? null,
                    'quiz_id' => $session->quiz_id,
                    'score' => $session->score,
                    'correct_answers' => $session->correct_answers,
                    'wrong_answers' => $session->wrong_answers,
                    'total_questions' => $session->correct_answers + $session->wrong_answers,
                    'passed' => $session->passed,
                    'completed_at' => $session->submitted_at,
                ];
            });
            
        // Get completed exam sessions
        $examSessions = \App\Models\ExamSession::with(['exam:id,title,course_id,pass_percentage', 'exam.course:id,title'])
            ->whereIn('enrollment_id', $enrollments)
            ->where('status', 'completed')
            ->latest('submitted_at')
            ->get()
            ->map(function($session) {
                return [
                    'type' => 'exam',
                    'id' => $session->id,
                    'session_id' => $session->id,
                    'title' => $session->exam->title ?? 'Unknown Exam',
                    'course' => $session->exam->course->title ?? 'Unknown Course',
                    'course_id' => $session->exam->course_id ?? null,
                    'exam_id' => $session->exam_id,
                    'score' => $session->score,
                    'pass_percentage' => $session->exam->pass_percentage ?? 70,
                    'correct_answers' => $session->correct_answers,
                    'wrong_answers' => $session->wrong_answers,
                    'total_questions' => $session->correct_answers + $session->wrong_answers,
                    'passed' => $session->passed,
                    'completed_at' => $session->submitted_at,
                ];
            });
            
        // Merge and sort by completion date
        $results = $quizSessions->concat($examSessions)
            ->sortByDesc('completed_at')
            ->values();
            
        return response()->json([
            'results' => $results,
            'stats' => [
                'total' => count($results),
                'exams_count' => count($examSessions),
                'quizzes_count' => count($quizSessions),
                'passed_count' => $results->where('passed', true)->count(),
                'failed_count' => $results->where('passed', false)->count(),
            ]
        ]);
    }
}
