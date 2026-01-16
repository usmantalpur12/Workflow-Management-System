<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HrController;
use Illuminate\Support\Facades\Route;

// Login Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Default Route
Route::get('/', function () {
    return redirect('/login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // HR Dashboard routes
    Route::prefix('hr')->name('hr.')->group(function () {
        Route::get('/', [HrController::class, 'dashboard'])->name('dashboard');
        Route::get('/assign-employees', [HrController::class, 'showAssignEmployees'])->name('assign-employees');
        Route::post('/projects/assign-employees', [HrController::class, 'assignEmployeesToProject'])->name('projects.assign-employees');
        Route::get('/projects/{projectId}/assigned-employees', [HrController::class, 'getAssignedEmployees'])->name('projects.assigned-employees');
        Route::delete('/projects/{projectId}/employees/{userId}', [HrController::class, 'removeEmployeeFromProject'])->name('projects.remove-employee');
    });

    // Department Routes (Super Admin Only)
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('create');
        Route::post('/', [DepartmentController::class, 'store'])->name('store');
        
        // Routes that require department access
        Route::middleware(['auth', \App\Http\Middleware\CheckDepartmentAccess::class])->group(function () {
            Route::get('/{department}/edit', [DepartmentController::class, 'edit'])->name('edit');
            Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');
            Route::get('/{department}', [DepartmentController::class, 'show'])->name('show');
            Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
        });
    });

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // User Management Routes (Super Admin Only)
    Route::resource('users', UserController::class);

    // Attendance Routes
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('/attendances/clock-in', [AttendanceController::class, 'clockIn'])->name('attendances.clock-in');
    Route::post('/attendances/clock-out', [AttendanceController::class, 'clockOut'])->name('attendances.clock-out');
    Route::get('/attendances/export-excel', [AttendanceController::class, 'exportExcel'])->name('attendances.export-excel');
    Route::get('/attendances/export-pdf', [AttendanceController::class, 'exportPdf'])->name('attendances.export-pdf');
    Route::post('/attendances/{attendance}/lock', [AttendanceController::class, 'lock'])->name('attendances.lock');
    Route::get('/attendances/notifications', [AttendanceController::class, 'getNotifications'])->name('attendances.notifications');

    // Project Routes
    Route::resource('projects', ProjectController::class);

    // Project Department Management
    Route::get('projects/{project}/assign-departments', [ProjectController::class, 'assignDepartments'])->name('projects.assign-departments');
    Route::put('projects/{project}/update-departments', [ProjectController::class, 'updateDepartments'])->name('projects.update-departments');

    // Project Department User Management
    Route::get('projects/{project}/departments/{department}/assign-users', [ProjectController::class, 'showAssignDepartmentUsers'])->name('projects.assign-department-users');
    Route::post('projects/{project}/departments/{department}/assign-users', [ProjectController::class, 'assignDepartmentUsers'])->name('projects.assign-department-users.update');
    Route::post('/projects/{project}/assign', [ProjectController::class, 'assignUser'])->name('projects.assign');
    Route::post('/tasks/{task}/progress', [ProjectController::class, 'updateProgress'])->name('tasks.progress');

    // Chat Routes
    Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
    Route::get('/chats/advanced', [ChatController::class, 'advanced'])->name('chats.advanced');
    Route::get('/chats/messages/{userId}', [ChatController::class, 'getMessages'])->name('chats.messages');
    Route::post('/chats', [ChatController::class, 'send'])->name('chats.send');
    Route::post('/chats/typing', [ChatController::class, 'typing'])->name('chats.typing');
});
