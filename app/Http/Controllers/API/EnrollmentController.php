<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    /**
     * Admin: Get all enrollments with filtering and pagination
     */
    public function adminIndex(Request $request)
    {
        $query = Enrollment::with(['user', 'course', 'coupon'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('enrollment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('enrollment_date', '<=', $request->date_to);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('course', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $enrollments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $enrollments->items(),
            'pagination' => [
                'current_page' => $enrollments->currentPage(),
                'last_page' => $enrollments->lastPage(),
                'per_page' => $enrollments->perPage(),
                'total' => $enrollments->total(),
            ]
        ]);
    }

    /**
     * Admin: Get enrollment statistics
     */
    public function adminStatistics(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        // Total enrollments
        $totalEnrollments = Enrollment::whereBetween('enrollment_date', [$startDate, $endDate])->count();
        
        // Active enrollments
        $activeEnrollments = Enrollment::where('status', 'active')
            ->whereBetween('enrollment_date', [$startDate, $endDate])
            ->count();
        
        // Pending enrollments
        $pendingEnrollments = Enrollment::where('status', 'pending')
            ->whereBetween('enrollment_date', [$startDate, $endDate])
            ->count();
        
        // Cancelled enrollments
        $cancelledEnrollments = Enrollment::where('status', 'cancelled')
            ->whereBetween('enrollment_date', [$startDate, $endDate])
            ->count();

        // Total revenue from enrollments
        $totalRevenue = Enrollment::whereBetween('enrollment_date', [$startDate, $endDate])
            ->where('status', 'active')
            ->sum('amount_paid');

        // Average enrollment per day
        $daysDiff = $startDate->diffInDays($endDate) + 1;
        $avgEnrollmentsPerDay = $totalEnrollments / $daysDiff;

        // Top courses by enrollment
        $topCourses = Enrollment::select('course_id', DB::raw('COUNT(*) as enrollment_count'))
            ->with('course:id,title,price')
            ->whereBetween('enrollment_date', [$startDate, $endDate])
            ->groupBy('course_id')
            ->orderBy('enrollment_count', 'desc')
            ->limit(5)
            ->get();

        // Monthly breakdown
        $monthlyBreakdown = Enrollment::select(
                DB::raw('YEAR(enrollment_date) as year'),
                DB::raw('MONTH(enrollment_date) as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount_paid) as revenue')
            )
            ->whereBetween('enrollment_date', [$startDate, $endDate])
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
                'summary' => [
                    'total_enrollments' => $totalEnrollments,
                    'active_enrollments' => $activeEnrollments,
                    'pending_enrollments' => $pendingEnrollments,
                    'cancelled_enrollments' => $cancelledEnrollments,
                    'total_revenue' => $totalRevenue,
                    'avg_enrollments_per_day' => round($avgEnrollmentsPerDay, 2),
                ],
                'top_courses' => $topCourses,
                'monthly_breakdown' => $monthlyBreakdown,
            ]
        ]);
    }

    /**
     * Admin: Get revenue summary from enrollments
     */
    public function revenueSummary(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        // Total revenue
        $totalRevenue = Enrollment::where('status', 'active')
            ->whereBetween('enrollment_date', [$startDate, $endDate])
            ->sum('amount_paid');

        // Calculate 3% for charity donations
        $charityAmount = $totalRevenue * 0.03;

        // Platform fee (assuming 5%)
        $platformFee = $totalRevenue * 0.05;

        // Instructor earnings (remaining amount)
        $instructorEarnings = $totalRevenue - $charityAmount - $platformFee;

        // Revenue by payment method
        $revenueByPaymentMethod = Enrollment::select('payment_method', DB::raw('SUM(amount_paid) as total'))
            ->where('status', 'active')
            ->whereBetween('enrollment_date', [$startDate, $endDate])
            ->groupBy('payment_method')
            ->get();

        // Daily revenue trend
        $dailyRevenue = Enrollment::select(
                DB::raw('DATE(enrollment_date) as date'),
                DB::raw('SUM(amount_paid) as revenue'),
                DB::raw('COUNT(*) as enrollments')
            )
            ->where('status', 'active')
            ->whereBetween('enrollment_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
                'summary' => [
                    'total_revenue' => $totalRevenue,
                    'charity_amount' => $charityAmount,
                    'platform_fee' => $platformFee,
                    'instructor_earnings' => $instructorEarnings,
                    'charity_percentage' => 3,
                    'platform_percentage' => 5,
                ],
                'revenue_by_payment_method' => $revenueByPaymentMethod,
                'daily_revenue' => $dailyRevenue,
            ]
        ]);
    }

    /**
     * Admin: Get specific enrollment details
     */
    public function adminShow($id)
    {
        $enrollment = Enrollment::with(['user', 'course', 'coupon', 'transactions'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $enrollment
        ]);
    }

    /**
     * Admin: Update enrollment status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,active,cancelled,completed'
        ]);

        $enrollment = Enrollment::findOrFail($id);
        $enrollment->status = $request->status;
        $enrollment->save();

        return response()->json([
            'success' => true,
            'message' => 'Enrollment status updated successfully',
            'data' => $enrollment
        ]);
    }
}
