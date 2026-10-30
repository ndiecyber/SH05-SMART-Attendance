<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceImportController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\PermissionRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'createLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'createRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/dashboard', DashboardController::class)->name('dashboard.index');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/data', [AttendanceController::class, 'attendanceData'])->name('attendance.data');
    Route::post('/attendance/import', [AttendanceImportController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('attendance.import');
    Route::get('/attendance/check', [AttendanceController::class, 'checkInPage'])->name('attendance.check-in');
    Route::post('/attendance/record', [AttendanceController::class, 'record'])->middleware('throttle:10,1')->name('attendance.record');
    if (app()->environment(['local', 'development', 'testing'])) {
        Route::delete('/attendance/today/check-in', [AttendanceController::class, 'resetCheckIn'])
            ->name('attendance.reset-check-in');
        Route::delete('/attendance/today/check-out', [AttendanceController::class, 'resetCheckOut'])
            ->name('attendance.reset-check-out');
    }
    Route::get('/attendance/logs', [AttendanceController::class, 'logs'])->name('attendance.logs');
    Route::get('/attendance/logs/export', [AttendanceController::class, 'exportLogs'])->name('attendance.logs.export');

    Route::get('/leave', [LeaveRequestController::class, 'index'])->name('leave.index');
    Route::get('/leave/create', [LeaveRequestController::class, 'create'])->name('leave.create');
    Route::post('/leave', [LeaveRequestController::class, 'store'])->name('leave.store');
    Route::patch('/leave/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave.cancel');

    Route::get('/permissions', [PermissionRequestController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/create', [PermissionRequestController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [PermissionRequestController::class, 'store'])->name('permissions.store');
    Route::patch('/permissions/{permissionRequest}/cancel', [PermissionRequestController::class, 'cancel'])->name('permissions.cancel');

    Route::get('/schedules', [WorkScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/create', [WorkScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [WorkScheduleController::class, 'store'])->name('schedules.store');
    Route::get('/schedules/{schedule}/edit', [WorkScheduleController::class, 'edit'])->name('schedules.edit');
    Route::put('/schedules/{schedule}', [WorkScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [WorkScheduleController::class, 'destroy'])->name('schedules.destroy');

    Route::get('/reports', [AttendanceReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [AttendanceReportController::class, 'export'])->name('reports.export');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
