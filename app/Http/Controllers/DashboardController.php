<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSetting;
use App\Models\LeaveRequest;
use App\Models\PermissionRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        if (auth()->user()->isAdmin()) {
            $today = CarbonImmutable::today(config('app.timezone'));
            $activeWorkers = User::query()->where('role', 'worker')->where('is_active', true);
            $totalActiveWorkers = (clone $activeWorkers)->count();
            $onLeaveWorkerIds = LeaveRequest::query()->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->pluck('user_id')->unique();
            $checkedIn = AttendanceRecord::query()
                ->with(['user:id', 'user.schedules' => fn ($query) => $query->whereDate('schedule_date', $today)->where('type', 'Workshift')])
                ->whereDate('attendance_date', $today)
                ->whereNotNull('check_in_at')
                ->get();
            $gracePeriod = (int) (AttendanceSetting::query()->value('late_grace_period_minutes') ?? 0);
            $lateToday = $checkedIn->filter(function (AttendanceRecord $record) use ($gracePeriod, $today): bool {
                $workshift = $record->user?->schedules->first();
                if ($workshift === null) {
                    return strtolower($record->status) === 'late';
                }

                $scheduledStart = CarbonImmutable::parse($today->toDateString().' '.$workshift->start_time, config('app.timezone'))
                    ->addMinutes($gracePeriod);

                return $record->check_in_at?->greaterThan($scheduledStart) ?? false;
            })->count();
            $presentToday = $checkedIn->count() - $lateToday;

            return view('admin.dashboard', [
                'totalWorkers' => User::query()->where('role', 'worker')->count(),
                'activeWorkers' => $totalActiveWorkers,
                'presentToday' => $presentToday,
                'lateToday' => $lateToday,
                'absentToday' => max(0, $totalActiveWorkers - $presentToday - $lateToday - $onLeaveWorkerIds->count()),
                'onLeaveToday' => $onLeaveWorkerIds->count(),
                'pendingLeaves' => LeaveRequest::query()->where('status', 'pending')->count(),
                'pendingPermissions' => PermissionRequest::query()->where('status', 'pending')->count(),
                'pendingLeaveRequests' => LeaveRequest::query()->with('user:id,name,job_title,division')->where('status', 'pending')->latest()->limit(4)->get(),
                'pendingPermissionRequests' => PermissionRequest::query()->with('user:id,name,job_title,division')->where('status', 'pending')->latest()->limit(4)->get(),
                'recentAttendance' => AttendanceRecord::query()->with('user:id,name,job_title,division')->whereDate('attendance_date', today())->latest('check_in_at')->limit(6)->get(),
            ]);
        }

        return view('dashboard.index');
    }
}
