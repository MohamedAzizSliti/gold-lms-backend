<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ThemeController extends Controller
{
    /**
     * Display a listing of themes.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Default themes for the Gold LMS
        $themes = [
            [
                'id' => 1,
                'name' => 'Default',
                'slug' => 'default',
                'description' => 'Default Gold LMS theme',
                'primary_color' => '#007bff',
                'secondary_color' => '#6c757d',
                'is_active' => true,
                'preview_image' => asset('img/themes/default.png'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Dark Mode',
                'slug' => 'dark',
                'description' => 'Dark theme for Gold LMS',
                'primary_color' => '#1a1a1a',
                'secondary_color' => '#333333',
                'is_active' => false,
                'preview_image' => asset('img/themes/dark.png'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Education Blue',
                'slug' => 'education-blue',
                'description' => 'Blue education theme',
                'primary_color' => '#2196F3',
                'secondary_color' => '#1976D2',
                'is_active' => false,
                'preview_image' => asset('img/themes/education-blue.png'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $themes,
            'message' => 'Themes retrieved successfully'
        ]);
    }

    /**
     * Display the specified theme.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        // Mock theme data - in real app, this would come from database
        $themes = [
            1 => [
                'id' => 1,
                'name' => 'Default',
                'slug' => 'default',
                'description' => 'Default Gold LMS theme',
                'primary_color' => '#007bff',
                'secondary_color' => '#6c757d',
                'is_active' => true,
                'settings' => [
                    'font_family' => 'Inter, sans-serif',
                    'border_radius' => '8px',
                    'sidebar_width' => '250px',
                    'header_height' => '60px',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        if (!isset($themes[$id])) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $themes[$id],
            'message' => 'Theme retrieved successfully'
        ]);
    }
}
