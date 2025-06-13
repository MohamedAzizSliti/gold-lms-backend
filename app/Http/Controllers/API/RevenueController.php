<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Revenue;
use App\Models\Course;
use App\Models\Enrollment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    /**
     * Get instructor revenue summary
     */
    public function instructorSummary(Request $request)
    {
        // Check if user is instructor
        if (!Auth::user()->hasRole('instructor') && !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $instructor_id = $request->instructor_id ?? Auth::id();
        
        // Check permissions (own data or admin)
        if (Auth::id() !== $instructor_id && !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get date range filters
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        // Get revenue data
        $revenues = Revenue::where('instructor_id', $instructor_id)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();

        // Calculate totals
        $totalRevenue = $revenues->sum('total_amount');
        $instructorEarnings = $revenues->sum('instructor_amount');
        $charityContributions = $revenues->sum('charity_amount');
        $platformFees = $revenues->sum('platform_fee');

        // Get enrollments count
        $enrollmentsCount = Enrollment::whereIn('id', $revenues->pluck('enrollment_id'))
            ->where('status', 'active')
            ->count();

        // Get monthly breakdown
        $monthlyBreakdown = Revenue::where('instructor_id', $instructor_id)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->select(
                DB::raw('YEAR(payment_date) as year'),
                DB::raw('MONTH(payment_date) as month'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('SUM(instructor_amount) as instructor_amount'),
                DB::raw('SUM(charity_amount) as charity_amount')
            )
            ->groupBy(DB::raw('YEAR(payment_date)'), DB::raw('MONTH(payment_date)'))
            ->orderBy(DB::raw('YEAR(payment_date)'))
            ->orderBy(DB::raw('MONTH(payment_date)'))
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'month_name' => $date->format('F'),
                    'total' => $item->total,
                    'instructor_amount' => $item->instructor_amount,
                    'charity_amount' => $item->charity_amount,
                ];
            });

        // Get course-wise breakdown
        $courseBreakdown = Revenue::where('instructor_id', $instructor_id)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->join('enrollments', 'revenues.enrollment_id', '=', 'enrollments.id')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->select(
                'courses.id',
                'courses.title',
                DB::raw('COUNT(enrollments.id) as enrollments_count'),
                DB::raw('SUM(revenues.total_amount) as total_revenue'),
                DB::raw('SUM(revenues.instructor_amount) as instructor_revenue'),
                DB::raw('SUM(revenues.charity_amount) as charity_amount')
            )
            ->groupBy('courses.id', 'courses.title')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return response()->json([
            'summary' => [
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
                'total_revenue' => $totalRevenue,
                'instructor_earnings' => $instructorEarnings,
                'charity_contributions' => $charityContributions,
                'platform_fees' => $platformFees,
                'enrollments_count' => $enrollmentsCount,
            ],
            'monthly_breakdown' => $monthlyBreakdown,
            'course_breakdown' => $courseBreakdown,
        ]);
    }
    
    /**
     * Get charity contributions summary (admin only)
     */
    public function charityContributions(Request $request)
    {
        // Only admin can view this
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get date range filters
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        // Get total charity contributions
        $totalContributions = Revenue::whereBetween('payment_date', [$startDate, $endDate])
            ->sum('charity_amount');

        // Get monthly breakdown
        $monthlyBreakdown = Revenue::whereBetween('payment_date', [$startDate, $endDate])
            ->select(
                DB::raw('YEAR(payment_date) as year'),
                DB::raw('MONTH(payment_date) as month'),
                DB::raw('SUM(charity_amount) as charity_amount')
            )
            ->groupBy(DB::raw('YEAR(payment_date)'), DB::raw('MONTH(payment_date)'))
            ->orderBy(DB::raw('YEAR(payment_date)'))
            ->orderBy(DB::raw('MONTH(payment_date)'))
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'month_name' => $date->format('F'),
                    'charity_amount' => $item->charity_amount,
                ];
            });
            
        // Get instructor breakdown
        $instructorBreakdown = Revenue::whereBetween('payment_date', [$startDate, $endDate])
            ->join('users', 'revenues.instructor_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                DB::raw('SUM(revenues.charity_amount) as charity_amount')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('charity_amount', 'desc')
            ->get();

        return response()->json([
            'summary' => [
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
                'total_contributions' => $totalContributions,
            ],
            'monthly_breakdown' => $monthlyBreakdown,
            'instructor_breakdown' => $instructorBreakdown,
        ]);
    }
    
    /**
     * Get instructor course revenues
     */
    public function courseRevenues($courseId, Request $request)
    {
        $course = Course::findOrFail($courseId);
        
        // Check permissions (own course or admin)
        if (Auth::id() !== $course->instructor_id && !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get date range filters
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        // Get enrollments for the course
        $enrollments = Enrollment::where('course_id', $courseId)
            ->whereBetween('enrollment_date', [$startDate, $endDate])
            ->get();

        // Get revenue data
        $revenues = Revenue::whereIn('enrollment_id', $enrollments->pluck('id'))
            ->get();

        // Calculate totals
        $totalRevenue = $revenues->sum('total_amount');
        $instructorEarnings = $revenues->sum('instructor_amount');
        $charityContributions = $revenues->sum('charity_amount');

        // Get detailed breakdown
        $revenueDetails = Revenue::whereIn('enrollment_id', $enrollments->pluck('id'))
            ->join('enrollments', 'revenues.enrollment_id', '=', 'enrollments.id')
            ->join('users', 'enrollments.user_id', '=', 'users.id')
            ->select(
                'revenues.id',
                'revenues.enrollment_id',
                'revenues.total_amount',
                'revenues.instructor_amount',
                'revenues.charity_amount',
                'revenues.payment_date',
                'revenues.payment_method',
                'users.id as student_id',
                'users.name as student_name'
            )
            ->orderBy('revenues.payment_date', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'course' => $course,
            'summary' => [
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
                'total_revenue' => $totalRevenue,
                'instructor_earnings' => $instructorEarnings,
                'charity_contributions' => $charityContributions,
                'enrollments_count' => count($enrollments),
            ],
            'revenue_details' => $revenueDetails,
        ]);
    }
}
