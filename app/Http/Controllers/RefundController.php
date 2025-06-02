<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RefundController extends Controller
{
    /**
     * Display a listing of refunds.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // In a real application, you would fetch refunds from database
        // For now, return empty array
        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Refunds retrieved successfully'
        ]);
    }

    /**
     * Store a newly created refund.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $validatedData = $request->validate([
            'order_id' => 'required|integer',
            'reason' => 'required|string|max:500',
            'amount' => 'nullable|numeric|min:0',
        ]);

        // In a real application, you would create the refund in database
        // For now, just return success response
        return response()->json([
            'success' => true,
            'data' => $validatedData,
            'message' => 'Refund request submitted successfully'
        ], 201);
    }

    /**
     * Display the specified refund.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        // In a real application, you would fetch the refund from database
        // For now, return mock data
        $refund = [
            'id' => $id,
            'order_id' => 123,
            'reason' => 'Product not as described',
            'amount' => 99.99,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'data' => $refund,
            'message' => 'Refund retrieved successfully'
        ]);
    }

    /**
     * Update the specified refund.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Validate the request
        $validatedData = $request->validate([
            'status' => 'sometimes|string|in:pending,approved,rejected',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        // In a real application, you would update the refund in database
        // For now, just return success response
        return response()->json([
            'success' => true,
            'data' => array_merge(['id' => $id], $validatedData),
            'message' => 'Refund updated successfully'
        ]);
    }

    /**
     * Remove the specified refund.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // In a real application, you would delete the refund from database
        // For now, just return success response
        return response()->json([
            'success' => true,
            'message' => 'Refund deleted successfully'
        ]);
    }
}
