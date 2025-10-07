<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyGroupController;
use App\Http\Controllers\CompanyBranchController;
use App\Http\Controllers\DeviceMasterController;
use App\Http\Controllers\ReIdMasterController;
use App\Http\Controllers\CctvLayoutController;
use App\Http\Controllers\EventLogController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect('/login');
    });
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User CRUD
    Route::resource('users', UserController::class);

    // Company Groups CRUD (Admin only - middleware in controller)
    Route::resource('company-groups', CompanyGroupController::class);

    // Company Branches CRUD
    Route::resource('company-branches', CompanyBranchController::class);

    // Device Masters CRUD
    Route::resource('device-masters', DeviceMasterController::class);

    // Person (Re-ID) Management (Read-only for operators, full for admin)
    Route::get('/re-id-masters', [ReIdMasterController::class, 'index'])->name('re-id-masters.index');
    Route::get('/re-id-masters/{reId}', [ReIdMasterController::class, 'show'])->name('re-id-masters.show');
    Route::patch('/re-id-masters/{reId}', [ReIdMasterController::class, 'update'])->name('re-id-masters.update');

    // CCTV Layout Management (Admin only - middleware in controller)
    Route::resource('cctv-layouts', CctvLayoutController::class);

    // Event Logs (Read-only)
    Route::get('/event-logs', [EventLogController::class, 'index'])->name('event-logs.index');
    Route::get('/event-logs/{eventLog}', [EventLogController::class, 'show'])->name('event-logs.show');
});
