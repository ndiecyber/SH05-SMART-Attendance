<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\AdminInvitation;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Notifications\ActivityNotification;
use App\Services\ActivityNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WorkerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:all,active,inactive'],
            'division' => ['nullable', 'string', 'max:120'],
            'job_title' => ['nullable', 'string', 'max:120'],
        ]);

        $workers = User::query()->where('role', 'worker')->when($filters['search'] ?? null, function ($query, string $search): void {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->orWhere('student_id', 'like', '%'.$search.'%');
            });
        })->when(($filters['status'] ?? 'all') !== 'all', fn ($query) => $query->where('is_active', ($filters['status'] ?? '') === 'active'))
            ->when($filters['division'] ?? null, fn ($query, string $division) => $query->where('division', $division))
            ->when($filters['job_title'] ?? null, fn ($query, string $jobTitle) => $query->where('job_title', $jobTitle))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.workers.index', [
            'workers' => $workers,
            'activeWorkers' => User::query()->where('role', 'worker')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']),
            'divisions' => User::query()->where('role', 'worker')->whereNotNull('division')->distinct()->orderBy('division')->pluck('division'),
            'jobTitles' => User::query()->where('role', 'worker')->whereNotNull('job_title')->distinct()->orderBy('job_title')->pluck('job_title'),
        ]);
    }

    public function updateStatus(Request $request, User $worker): RedirectResponse
    {
        abort_unless($worker->isWorker(), 404);
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $previousStatus = $worker->is_active;
        $worker->forceFill(['is_active' => $data['is_active']])->save();
        AdminAuditLog::query()->create([
            'admin_id' => $request->user()->id,
            'action' => 'worker.status_updated',
            'auditable_type' => User::class,
            'auditable_id' => $worker->id,
            'before_values' => ['is_active' => $previousStatus],
            'after_values' => ['is_active' => (bool) $worker->is_active],
        ]);

        return back()->with('success', 'Status akun Worker berhasil diperbarui.');
    }

    public function updateProfile(Request $request, User $worker): RedirectResponse
    {
        abort_unless($worker->isWorker(), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'division' => ['nullable', 'string', 'max:255'],
        ]);
        $previousValues = $worker->only(array_keys($data));
        $worker->forceFill($data)->save();
        AdminAuditLog::query()->create([
            'admin_id' => $request->user()->id,
            'action' => 'worker.profile_updated',
            'auditable_type' => User::class,
            'auditable_id' => $worker->id,
            'before_values' => $previousValues,
            'after_values' => $data,
        ]);

        return back()->with('success', 'Profil Worker berhasil diperbarui.');
    }

    public function show(User $worker): View
    {
        abort_unless($worker->isWorker(), 404);
        $worker->load(['schedules' => fn ($query) => $query->orderByDesc('schedule_date')->limit(12)]);

        return view('admin.workers.show', [
            'worker' => $worker,
            'attendanceCount' => $worker->attendanceRecords()->count(),
            'leaveRequests' => $worker->leaveRequests()->latest()->limit(5)->get(),
            'permissionRequests' => $worker->permissionRequests()->latest()->limit(5)->get(),
        ]);
    }

    public function storeSchedule(Request $request, User $worker, ActivityNotificationService $notifications): RedirectResponse
    {
        abort_unless($worker->isWorker(), 404);
        $data = $request->validate([
            'schedule_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:Workshift,Training,Meeting,Break'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => ['required', 'string', 'max:255'],
        ]);
        $schedule = DB::transaction(function () use ($worker, $data, $request): WorkSchedule {
            $schedule = $worker->schedules()->create($data + ['status' => 'scheduled']);
            AdminAuditLog::query()->create([
                'admin_id' => $request->user()->id,
                'action' => 'worker.schedule_assigned',
                'auditable_type' => WorkSchedule::class,
                'auditable_id' => $schedule->id,
                'after_values' => $schedule->only(['user_id', 'schedule_date', 'name', 'type', 'start_time', 'end_time', 'location']),
            ]);

            return $schedule;
        });
        $this->sendScheduleNotification($notifications, $worker, $schedule, 'Jadwal kerja baru diberikan');

        return back()->with('success', 'Jadwal Worker berhasil dibuat.');
    }

    public function updateSchedule(Request $request, User $worker, WorkSchedule $schedule, ActivityNotificationService $notifications): RedirectResponse
    {
        abort_unless($worker->isWorker() && $schedule->user_id === $worker->id, 404);
        $before = $schedule->only(['user_id', 'schedule_date', 'name', 'type', 'start_time', 'end_time', 'location']);
        $schedule = DB::transaction(function () use ($schedule, $request, $before): WorkSchedule {
            $schedule->update($this->validatedSchedule($request));
            AdminAuditLog::query()->create([
                'admin_id' => $request->user()->id,
                'action' => 'worker.schedule_updated',
                'auditable_type' => WorkSchedule::class,
                'auditable_id' => $schedule->id,
                'before_values' => $before,
                'after_values' => $schedule->only(['user_id', 'schedule_date', 'name', 'type', 'start_time', 'end_time', 'location']),
            ]);

            return $schedule;
        });
        $this->sendScheduleNotification($notifications, $worker, $schedule, 'Jadwal kerja diperbarui');

        return back()->with('success', 'Jadwal Worker berhasil diperbarui.');
    }

    public function destroySchedule(Request $request, User $worker, WorkSchedule $schedule, ActivityNotificationService $notifications): RedirectResponse
    {
        abort_unless($worker->isWorker() && $schedule->user_id === $worker->id, 404);
        DB::transaction(function () use ($schedule, $request): void {
            $oldValues = $schedule->only(['user_id', 'schedule_date', 'name', 'type', 'start_time', 'end_time', 'location']);
            $schedule->delete();
            AdminAuditLog::query()->create([
                'admin_id' => $request->user()->id,
                'action' => 'worker.schedule_deleted',
                'auditable_type' => WorkSchedule::class,
                'auditable_id' => $schedule->id,
                'before_values' => $oldValues,
            ]);
        });
        $this->sendScheduleNotification($notifications, $worker, $schedule, 'Jadwal kerja dihapus');

        return back()->with('success', 'Jadwal Worker berhasil dihapus.');
    }

    public function storeBulkSchedule(Request $request, ActivityNotificationService $notifications): RedirectResponse
    {
        $data = $request->validate([
            'worker_ids' => ['required', 'array', 'min:1', 'max:100'],
            'worker_ids.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
            'schedule_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:Workshift,Training,Meeting,Break'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => ['required', 'string', 'max:255'],
        ]);
        $workers = User::query()->whereIn('id', $data['worker_ids'])->where('role', 'worker')->where('is_active', true)->get();
        abort_unless($workers->count() === count($data['worker_ids']), 422, 'Pilih akun Worker aktif yang valid.');

        $schedules = DB::transaction(function () use ($request, $workers, $data) {
            return $workers->map(function (User $worker) use ($request, $data): WorkSchedule {
                $schedule = $worker->schedules()->create([
                    'schedule_date' => $data['schedule_date'],
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'location' => $data['location'],
                    'status' => 'scheduled',
                ]);
                AdminAuditLog::query()->create([
                    'admin_id' => $request->user()->id,
                    'action' => 'worker.schedule_assigned',
                    'auditable_type' => WorkSchedule::class,
                    'auditable_id' => $schedule->id,
                    'after_values' => $schedule->only(['user_id', 'schedule_date', 'name', 'type', 'start_time', 'end_time', 'location']),
                ]);

                return $schedule;
            });
        });

        $schedules->each(function (WorkSchedule $schedule) use ($notifications): void {
            $this->sendScheduleNotification($notifications, $schedule->user, $schedule, 'Jadwal kerja baru diberikan');
        });

        return back()->with('success', 'Jadwal berhasil diberikan kepada '.$workers->count().' Worker.');
    }

    /** @return array{name: string, schedule_date: string, type: string, start_time: string, end_time: string, location: string} */
    private function validatedSchedule(Request $request): array
    {
        return $request->validate([
            'schedule_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:Workshift,Training,Meeting,Break'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => ['required', 'string', 'max:255'],
        ]);
    }

    private function sendScheduleNotification(ActivityNotificationService $notifications, User $worker, WorkSchedule $schedule, string $title): void
    {
        $notifications->send($worker, new ActivityNotification(
            title: $title,
            description: $schedule->name.' · '.$schedule->schedule_date->format('d M Y').' · '.substr($schedule->start_time, 0, 5).'–'.substr($schedule->end_time, 0, 5),
            category: 'Schedule',
            priority: 'normal',
            requiresAction: false,
            url: route('schedules.index', [], false),
            source: 'Admin Schedule Management',
            metadata: ['schedule_id' => $schedule->id],
        ));
    }

    public function createAdminInvitation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'valid_hours' => ['required', 'integer', 'between:1,168'],
        ]);
        $token = Str::random(48);

        DB::transaction(function () use ($request, $data, $token): void {
            $invitation = AdminInvitation::query()->create([
                'email' => $data['email'] ?? null,
                'token_hash' => hash('sha256', $token),
                'created_by' => $request->user()->id,
                'expires_at' => now()->addHours((int) $data['valid_hours']),
            ]);
            AdminAuditLog::query()->create([
                'admin_id' => $request->user()->id,
                'action' => 'admin.invitation_created',
                'auditable_type' => AdminInvitation::class,
                'auditable_id' => $invitation->id,
                'after_values' => ['email' => $invitation->email, 'expires_at' => $invitation->expires_at->toIso8601String()],
            ]);
        });

        return back()->with('admin_invitation_token', $token)->with('success', 'Undangan Admin dibuat. Simpan kode ini sekarang; kode hanya ditampilkan sekali.');
    }
}
