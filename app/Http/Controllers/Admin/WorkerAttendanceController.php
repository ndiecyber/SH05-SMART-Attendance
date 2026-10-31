<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSetting;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class WorkerAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'worker_id' => ['nullable', 'integer', 'exists:users,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'in:all,present,late'],
        ]);
        $date = $filters['date'] ?? today()->toDateString();
        $workerQuery = User::query()->where('role', 'worker')->where('is_active', true);
        $scheduledWorkerCount = User::query()->where('role', 'worker')->where('is_active', true)
            ->whereHas('schedules', fn ($query) => $query->whereDate('schedule_date', $date)->whereNotIn('type', ['Day Off', 'Off']))
            ->count();
        if ($scheduledWorkerCount > 0) {
            $workerQuery->whereHas('schedules', fn ($query) => $query->whereDate('schedule_date', $date)->whereNotIn('type', ['Day Off', 'Off']));
        }
        $workers = $workerQuery->orderBy('name')->get(['id', 'name', 'email', 'job_title', 'division', 'student_id', 'avatar_path']);
        $recordsQuery = AttendanceRecord::query()->with(['user:id,name,email,job_title,division,student_id,avatar_path', 'user.schedules' => fn ($query) => $query->whereDate('schedule_date', $date)->where('type', 'Workshift')])->whereDate('attendance_date', $date);
        if (! empty($filters['worker_id'])) {
            $selected = $workers->firstWhere('id', (int) $filters['worker_id']);
            abort_if($selected === null, 404);
            $recordsQuery->where('user_id', $selected->id);
        }
        $totalWorkers = $workers->count();
        $allDateRecords = AttendanceRecord::query()->with(['user:id', 'user.schedules' => fn ($query) => $query->whereDate('schedule_date', $date)->where('type', 'Workshift')])
            ->whereDate('attendance_date', $date)->whereNotNull('check_in_at')->get();
        $lateIds = $allDateRecords->filter(fn (AttendanceRecord $record): bool => $this->isLate($record, $date))->modelKeys();
        $present = $allDateRecords->count() - count($lateIds);
        $late = count($lateIds);
        $onLeave = LeaveRequest::query()->where('status', 'approved')->whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date)->distinct('user_id')->count('user_id');
        $allRecords = $recordsQuery->latest('check_in_at')->get();
        $recordRows = $allRecords->map(function (AttendanceRecord $record) use ($date): AttendanceRecord {
            $record->setAttribute('status_label', $this->isLate($record, $date) ? 'late' : $record->status);

            return $record;
        });
        if (($filters['status'] ?? 'all') !== 'all') {
            $recordRows = $recordRows->filter(fn (AttendanceRecord $record): bool => $record->status_label === $filters['status'])->values();
        }
        $page = LengthAwarePaginator::resolveCurrentPage();
        $records = new LengthAwarePaginator(
            $recordRows->forPage($page, 20)->values(),
            $recordRows->count(),
            20,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return view('admin.worker-attendance.index', [
            'workers' => $workers,
            'records' => $records,
            'selectedWorkerId' => (int) ($filters['worker_id'] ?? 0),
            'date' => $date,
            'summary' => [
                'workers' => $totalWorkers,
                'present' => $present,
                'late' => $late,
                'absent' => max(0, $totalWorkers - $present - $late - $onLeave),
                'on_leave' => $onLeave,
            ],
        ]);
    }

    private function isLate(AttendanceRecord $record, string $date): bool
    {
        if ($record->check_in_at === null) {
            return false;
        }
        $workshift = $record->user?->schedules->first();
        if ($workshift === null) {
            return strtolower($record->status) === 'late';
        }
        $gracePeriodMinutes = (int) (AttendanceSetting::query()->value('late_grace_period_minutes') ?? 0);
        $scheduledStart = Carbon::parse($date.' '.$workshift->start_time, config('app.timezone'))
            ->addMinutes($gracePeriodMinutes);

        return $record->check_in_at->greaterThan($scheduledStart);
    }
}
