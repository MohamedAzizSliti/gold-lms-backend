<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WishlistController extends Controller
{
    /**
     * Display a listing of wishlist items.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // In a real application, you would fetch wishlist items from database
        // For now, return empty array
        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Wishlist items retrieved successfully'
        ]);
    }

    /**
     * Store a newly created wishlist item.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $validatedData = $request->validate([
            'course_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        // In a real application, you would create the wishlist item in database
        // For now, just return success response
        return response()->json([
            'success' => true,
            'data' => $validatedData,
            'message' => 'Course added to wishlist successfully'
        ], 201);
    }

    /**
     * Display the specified wishlist item.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        // In a real application, you would fetch the wishlist item from database
        // For now, return mock data
        $wishlistItem = [
            'id' => $id,
            'course_id' => 123,
            'user_id' => 456,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'data' => $wishlistItem,
            'message' => 'Wishlist item retrieved successfully'
        ]);
    }

    /**
     * Update the specified wishlist item.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Validate the request
        $validatedData = $request->validate([
            'course_id' => 'sometimes|integer',
        ]);

        // In a real application, you would update the wishlist item in database
        // For now, just return success response
        return response()->json([
            'success' => true,
            'data' => array_merge(['id' => $id], $validatedData),
            'message' => 'Wishlist item updated successfully'
        ]);
    }

    /**
     * Remove the specified wishlist item.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // In a real application, you would delete the wishlist item from database
        // For now, just return success response
        return response()->json([
            'success' => true,
            'message' => 'Course removed from wishlist successfully'
        ]);
    }
}
