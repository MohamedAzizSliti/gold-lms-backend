<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShippingRuleController extends Controller
{
    /**
     * Display a listing of shipping rules.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Shipping rules retrieved successfully'
        ]);
    }

    /**
     * Store a newly created shipping rule.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'min_amount' => 'required|numeric|min:0',
            'shipping_cost' => 'required|numeric|min:0',
        ]);

        return response()->json([
            'success' => true,
            'data' => array_merge($validatedData, ['id' => rand(1000, 9999)]),
            'message' => 'Shipping rule created successfully'
        ], 201);
    }

    /**
     * Display the specified shipping rule.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ['id' => $id, 'name' => 'Sample Rule'],
            'message' => 'Shipping rule retrieved successfully'
        ]);
    }

    /**
     * Update the specified shipping rule.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ['id' => $id],
            'message' => 'Shipping rule updated successfully'
        ]);
    }

    /**
     * Remove the specified shipping rule.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Shipping rule deleted successfully'
        ]);
    }

    /**
     * Update the status of the specified shipping rule.
     *
     * @param int $id
     * @param string $status
     * @return JsonResponse
     */
    public function status(int $id, string $status): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ['id' => $id, 'status' => $status],
            'message' => 'Shipping rule status updated successfully'
        ]);
    }
}
