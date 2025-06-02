<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ThemeOptionController extends Controller
{
    /**
     * Display a listing of theme options.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Default theme options for the Gold LMS
        $themeOptions = [
            'primary_color' => '#007bff',
            'secondary_color' => '#6c757d',
            'success_color' => '#28a745',
            'danger_color' => '#dc3545',
            'warning_color' => '#ffc107',
            'info_color' => '#17a2b8',
            'light_color' => '#f8f9fa',
            'dark_color' => '#343a40',
            'font_family' => 'Inter, sans-serif',
            'logo_url' => asset('img/logo.png'),
            'favicon_url' => asset('favicon.png'),
            'site_name' => config('app.name', 'Gold LMS'),
            'site_description' => 'Advanced Learning Management System with GPS Tracking',
            'footer_text' => '© 2025 Gold LMS. All rights reserved.',
            'enable_dark_mode' => true,
            'enable_rtl' => false,
            'sidebar_collapsed' => false,
            'show_breadcrumbs' => true,
            'animation_enabled' => true,
        ];

        return response()->json([
            'success' => true,
            'data' => $themeOptions,
            'message' => 'Theme options retrieved successfully'
        ]);
    }

    /**
     * Update theme options.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        // In a real application, you would save these to database
        // For now, just return success response
        
        $validatedData = $request->validate([
            'primary_color' => 'nullable|string',
            'secondary_color' => 'nullable|string',
            'font_family' => 'nullable|string',
            'logo_url' => 'nullable|url',
            'site_name' => 'nullable|string|max:255',
            'enable_dark_mode' => 'nullable|boolean',
            'enable_rtl' => 'nullable|boolean',
        ]);

        return response()->json([
            'success' => true,
            'data' => $validatedData,
            'message' => 'Theme options updated successfully'
        ]);
    }
}
