<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| CCTV Dashboard API with versioning support
| Current version: v1 (latest)
|
*/

// API Version 1 (Current - Latest)
Route::prefix('v1')->group(function () {
    require __DIR__ . '/api_v1.php';
});

// Default API routes (redirect to latest version - v1)
Route::any('/{any}', function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found. Please use versioned endpoints.',
        'data' => [
            'available_versions' => ['v1'],
            'current_version' => 'v1',
            'base_url' => url('/api/v1'),
            'documentation' => url('/api/documentation'),
        ],
        'meta' => [
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0',
        ],
    ], 404);
})->where('any', '.*');
