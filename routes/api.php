<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ChatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Users API
    Route::apiResource('users', UserController::class);
    
    // Projects API
    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects/{project}/assign', [ProjectController::class, 'assignUser']);
    
    // Attendance API
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::post('/attendances/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendances/clock-out', [AttendanceController::class, 'clockOut']);
    Route::get('/attendances/notifications', [AttendanceController::class, 'getNotifications']);
    
    // Chat API
    Route::get('/chats', [ChatController::class, 'index']);
    Route::get('/chats/messages/{userId}', [ChatController::class, 'getMessages']);
    Route::post('/chats', [ChatController::class, 'send']);
    Route::get('/chats/unread-count', [ChatController::class, 'getUnreadCount']);
    Route::post('/chats/mark-read/{userId}', [ChatController::class, 'markAsRead']);
    
    // Dashboard API
    Route::get('/dashboard/stats', [App\Http\Controllers\DashboardController::class, 'getStats']);
    Route::get('/dashboard/activities', [App\Http\Controllers\DashboardController::class, 'getActivities']);
}); 