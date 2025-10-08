<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\DetectionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Version 1 of the CCTV Dashboard API
| Base URL: /api/v1/
| Status: Current (Latest)
| Released: October 2025
|
*/

// Apply API version middleware to all V1 routes
Route::middleware('api.version:v1')->group(function () {

    // Public endpoints (no authentication)
    Route::post('/register', [AuthController::class, 'register'])->name('v1.register');
    Route::post('/login', [AuthController::class, 'login'])->name('v1.login');

    // Protected endpoints (Sanctum token authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('v1.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('v1.me');

        // User management endpoints
        Route::apiResource('users', UserController::class)->names([
            'index' => 'v1.users.index',
            'store' => 'v1.users.store',
            'show' => 'v1.users.show',
            'update' => 'v1.users.update',
            'destroy' => 'v1.users.destroy',
        ]);

        Route::get('/users/pagination/options', [UserController::class, 'paginationOptions'])
            ->name('v1.users.pagination.options');
    });

    // Detection API (API Key authentication)
    Route::middleware('api.key')->group(function () {
        // Detection logging
        Route::post('/detection/log', [DetectionController::class, 'store'])
            ->name('v1.detection.store');

        Route::get('/detection/status/{jobId}', [DetectionController::class, 'status'])
            ->name('v1.detection.status');

        // Detection queries
        Route::get('/detections', [DetectionController::class, 'index'])
            ->name('v1.detections.index');

        Route::get('/detection/summary', [DetectionController::class, 'summary'])
            ->name('v1.detection.summary');

        // Person (Re-ID) endpoints
        Route::get('/person/{reId}', [DetectionController::class, 'showPerson'])
            ->name('v1.person.show');

        Route::get('/person/{reId}/detections', [DetectionController::class, 'personDetections'])
            ->name('v1.person.detections');

        // Branch detections
        Route::get('/branch/{branchId}/detections', [DetectionController::class, 'branchDetections'])
            ->name('v1.branch.detections');
    });
}); // End API version middleware
