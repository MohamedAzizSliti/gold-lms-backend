<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShippingController extends Controller
{
    /**
     * Display a listing of shipping methods.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Mock shipping methods data
        $shippingMethods = [
            [
                'id' => 1,
                'name' => 'Standard Shipping',
                'description' => 'Standard delivery within 5-7 business days',
                'cost' => 0.00,
                'estimated_days' => '5-7',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Express Shipping',
                'description' => 'Fast delivery within 2-3 business days',
                'cost' => 15.00,
                'estimated_days' => '2-3',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $shippingMethods,
            'message' => 'Shipping methods retrieved successfully'
        ]);
    }

    /**
     * Store a newly created shipping method.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'cost' => 'required|numeric|min:0',
            'estimated_days' => 'required|string|max:50',
            'status' => 'sometimes|string|in:active,inactive',
        ]);

        // In a real application, you would create the shipping method in database
        // For now, just return success response
        return response()->json([
            'success' => true,
            'data' => array_merge($validatedData, ['id' => rand(1000, 9999)]),
            'message' => 'Shipping method created successfully'
        ], 201);
    }

    /**
     * Display the specified shipping method.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        // Mock shipping method data
        $shippingMethod = [
            'id' => $id,
            'name' => 'Standard Shipping',
            'description' => 'Standard delivery within 5-7 business days',
            'cost' => 0.00,
            'estimated_days' => '5-7',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return response()->json([
            'success' => true,
            'data' => $shippingMethod,
            'message' => 'Shipping method retrieved successfully'
        ]);
    }

    /**
     * Update the specified shipping method.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Validate the request
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:500',
            'cost' => 'sometimes|numeric|min:0',
            'estimated_days' => 'sometimes|string|max:50',
            'status' => 'sometimes|string|in:active,inactive',
        ]);

        // In a real application, you would update the shipping method in database
        // For now, just return success response
        return response()->json([
            'success' => true,
            'data' => array_merge(['id' => $id], $validatedData),
            'message' => 'Shipping method updated successfully'
        ]);
    }

    /**
     * Remove the specified shipping method.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // In a real application, you would delete the shipping method from database
        // For now, just return success response
        return response()->json([
            'success' => true,
            'message' => 'Shipping method deleted successfully'
        ]);
    }

    /**
     * Update the status of the specified shipping method.
     *
     * @param int $id
     * @param string $status
     * @return JsonResponse
     */
    public function status(int $id, string $status): JsonResponse
    {
        // Validate status
        if (!in_array($status, ['active', 'inactive'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status. Must be active or inactive.'
            ], 400);
        }

        // In a real application, you would update the status in database
        // For now, just return success response
        return response()->json([
            'success' => true,
            'data' => ['id' => $id, 'status' => $status],
            'message' => 'Shipping method status updated successfully'
        ]);
    }
}
