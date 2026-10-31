<?php

use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\AttendanceSettingsController;
use App\Http\Controllers\Admin\WorkerAttendanceController;
use App\Http\Controllers\Admin\WorkerController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceImportController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkScheduleController;
use App\Http\Middleware\ApplyUserPreferences;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'createLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'createRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});

Route::middleware(['auth', ApplyUserPreferences::class, 'role:worker,admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/dashboard', DashboardController::class)->name('dashboard.index');

    Route::middleware('role:worker')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/data', [AttendanceController::class, 'attendanceData'])->name('attendance.data');
        Route::post('/attendance/import', [AttendanceImportController::class, 'store'])->middleware('throttle:5,1')->name('attendance.import');
        Route::get('/attendance/check', [AttendanceController::class, 'checkInPage'])->name('attendance.check-in');
        Route::post('/attendance/record', [AttendanceController::class, 'record'])->middleware('throttle:10,1')->name('attendance.record');
        if (app()->environment(['local', 'development', 'testing'])) {
            Route::delete('/attendance/today/check-in', [AttendanceController::class, 'resetCheckIn'])->name('attendance.reset-check-in');
            Route::delete('/attendance/today/check-out', [AttendanceController::class, 'resetCheckOut'])->name('attendance.reset-check-out');
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

        Route::get('/reports', [AttendanceReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [AttendanceReportController::class, 'export'])->name('reports.export');
    });

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/{notificationId}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/{notificationId}/unread', [NotificationController::class, 'markUnread'])->name('notifications.unread');
    Route::patch('/notifications/{notificationId}/archive', [NotificationController::class, 'archive'])->name('notifications.archive');
    Route::patch('/notifications/{notificationId}/restore', [NotificationController::class, 'restore'])->name('notifications.restore');
    Route::delete('/notifications/{notificationId}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::put('/notification-preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.preferences.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [ProfileController::class, 'edit'])->name('settings.index');
    Route::put('/settings', [ProfileController::class, 'update'])->name('settings.update');
    Route::put('/settings/security', [ProfileController::class, 'updateSecurity'])->name('settings.security.update');
    Route::put('/settings/preferences', [ProfileController::class, 'updatePreferences'])->name('settings.preferences.update');
    Route::put('/settings/notifications', [ProfileController::class, 'updateNotificationPreferences'])->name('settings.notifications.update');
    Route::post('/settings/reset-preferences', [ProfileController::class, 'resetPreferences'])->name('settings.reset-preferences');
    Route::put('/settings/sidebar-preference', [ProfileController::class, 'updateSidebarPreference'])->name('settings.sidebar-preference.update');
    Route::get('/settings/export', [ProfileController::class, 'export'])->name('settings.export');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', ApplyUserPreferences::class, 'role:admin'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/workers', [WorkerController::class, 'index'])->name('workers.index');
    Route::get('/workers/{worker}', [WorkerController::class, 'show'])->name('workers.show');
    Route::patch('/workers/{worker}/status', [WorkerController::class, 'updateStatus'])->name('workers.status');
    Route::put('/workers/{worker}', [WorkerController::class, 'updateProfile'])->name('workers.update');
    Route::post('/workers/{worker}/schedules', [WorkerController::class, 'storeSchedule'])->name('workers.schedules.store');
    Route::put('/workers/{worker}/schedules/{schedule}', [WorkerController::class, 'updateSchedule'])->name('workers.schedules.update');
    Route::delete('/workers/{worker}/schedules/{schedule}', [WorkerController::class, 'destroySchedule'])->name('workers.schedules.destroy');
    Route::post('/schedules/bulk', [WorkerController::class, 'storeBulkSchedule'])->name('schedules.bulk.store');
    Route::post('/workers/admin-invitations', [WorkerController::class, 'createAdminInvitation'])->middleware('throttle:5,1')->name('workers.invitations.store');
    Route::get('/worker-attendance', [WorkerAttendanceController::class, 'index'])->name('worker-attendance.index');
    Route::get('/leave-requests', [ApprovalController::class, 'leaveIndex'])->name('leave-requests.index');
    Route::post('/leave-requests/{leaveRequest}/decision', [ApprovalController::class, 'decideLeave'])->name('leave-requests.decision');
    Route::get('/permission-requests', [ApprovalController::class, 'permissionIndex'])->name('permission-requests.index');
    Route::post('/permission-requests/{permissionRequest}/decision', [ApprovalController::class, 'decidePermission'])->name('permission-requests.decision');
    Route::get('/settings/attendance', [AttendanceSettingsController::class, 'edit'])->name('settings.attendance');
    Route::put('/settings/attendance', [AttendanceSettingsController::class, 'update'])->name('settings.attendance.update');
    Route::view('/settings', 'admin.settings.index')->name('settings.index');
});
