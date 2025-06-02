<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Course;

class ProductController extends Controller
{
    /**
     * Get product (course) by slug.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function getProductBySlug(string $slug): JsonResponse
    {
        try {
            // Try to find the course by slug
            $course = Course::where('slug', $slug)
                ->with(['category', 'instructor', 'chapters', 'reviews'])
                ->first();

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $course,
                'message' => 'Course retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving course: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Get all products (courses).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Course::with(['category', 'instructor']);

            // Apply filters if provided
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $courses = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $courses,
                'message' => 'Courses retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving courses: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
