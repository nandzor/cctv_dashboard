<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DetectionController;
use App\Http\Middleware\ApiKeyAuth;
use Illuminate\Support\Facades\Route;

// Public API routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // User CRUD API (without names to avoid conflict with web routes)
    Route::apiResource('users', UserController::class)->names([
        'index' => 'api.users.index',
        'store' => 'api.users.store',
        'show' => 'api.users.show',
        'update' => 'api.users.update',
        'destroy' => 'api.users.destroy',
    ]);

    // Additional user endpoints
    Route::get('/users/pagination/options', [UserController::class, 'paginationOptions'])->name('api.users.pagination.options');
});

// Detection API (API Key authentication)
Route::middleware(ApiKeyAuth::class)->group(function () {
    // Detection logging
    Route::post('/detection/log', [DetectionController::class, 'store'])->name('api.detection.store');
    Route::get('/detection/status/{jobId}', [DetectionController::class, 'status'])->name('api.detection.status');

    // Detection queries
    Route::get('/detections', [DetectionController::class, 'index'])->name('api.detections.index');
    Route::get('/detection/summary', [DetectionController::class, 'summary'])->name('api.detection.summary');

    // Person (Re-ID) queries
    Route::get('/person/{reId}', [DetectionController::class, 'showPerson'])->name('api.person.show');
    Route::get('/person/{reId}/detections', [DetectionController::class, 'personDetections'])->name('api.person.detections');

    // Branch detections
    Route::get('/branch/{branchId}/detections', [DetectionController::class, 'branchDetections'])->name('api.branch.detections');
});
