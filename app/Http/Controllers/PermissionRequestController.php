<?php

namespace App\Http\Controllers;

use App\Models\PermissionRequest;
use App\Models\User;
use App\Notifications\ActivityNotification;
use App\Services\ActivityNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PermissionRequestController extends Controller
{
    public function index(Request $request): View
    {
        $permissionRequests = $request->user()->permissionRequests()
            ->latest()
            ->get()
            ->map(fn (PermissionRequest $permissionRequest): array => [
                'id' => $permissionRequest->id,
                'type' => $permissionRequest->permission_type,
                'date' => $permissionRequest->permission_date->toDateString(),
                'startTime' => substr((string) $permissionRequest->start_time, 0, 5),
                'endTime' => substr((string) $permissionRequest->end_time, 0, 5),
                'durationMinutes' => $this->durationMinutes($permissionRequest),
                'reason' => $permissionRequest->reason,
                'reviewNote' => $permissionRequest->review_note,
                'status' => ucfirst(strtolower($permissionRequest->status)),
                'submitted' => $permissionRequest->created_at?->format('d M Y H:i'),
                'cancelUrl' => route('permissions.cancel', $permissionRequest),
            ]);

        return view('permissions.index', compact('permissionRequests'));
    }

    public function create(): View
    {
        return view('permissions.create');
    }

    public function store(Request $request, ActivityNotificationService $notificationService): RedirectResponse
    {
        $data = $request->validate([
            'permission_type' => ['required', 'in:WFH,Arrive Late,Leave Early,Pop Out,Business Trip'],
            'permission_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);
        $permissionRequest = $request->user()->permissionRequests()->create($data);

        $notificationService->send($request->user(), new ActivityNotification(
            title: 'Pengajuan permission berhasil dikirim',
            description: $permissionRequest->permission_type.' diajukan untuk '.$permissionRequest->permission_date->format('d M Y').'.',
            category: 'Approval',
            priority: 'normal',
            requiresAction: false,
            url: route('permissions.index', [], false),
            source: 'Permission',
            metadata: ['permission_request_id' => $permissionRequest->id, 'status' => $permissionRequest->status],
        ));

        User::query()->where('role', 'admin')->where('is_active', true)->each(function (User $admin) use ($notificationService, $permissionRequest, $request): void {
            $notificationService->send($admin, new ActivityNotification(
                title: 'Pengajuan permission baru',
                description: $request->user()->name.' mengajukan '.$permissionRequest->permission_type.'.',
                category: 'Approval',
                priority: 'normal',
                requiresAction: true,
                url: route('admin.permission-requests.index', [], false),
                source: 'Permission',
                metadata: ['permission_request_id' => $permissionRequest->id, 'worker_id' => $request->user()->id],
            ));
        });

        return redirect()->route('permissions.index')->with('success', 'Pengajuan izin berhasil dikirim.');
    }

    public function cancel(Request $request, PermissionRequest $permissionRequest): RedirectResponse
    {
        abort_unless($permissionRequest->user_id === $request->user()->id, 403);
        abort_unless(strtolower($permissionRequest->status) === 'pending', 422, 'Only pending permission requests can be cancelled.');

        DB::transaction(function () use ($permissionRequest): void {
            $permissionRequest->update(['status' => 'cancelled']);
        });

        return redirect()->route('permissions.index')->with('success', 'Pengajuan izin berhasil dibatalkan.');
    }

    private function durationMinutes(PermissionRequest $permissionRequest): int
    {
        $start = Carbon::parse($permissionRequest->permission_date->toDateString().' '.$permissionRequest->start_time);
        $end = Carbon::parse($permissionRequest->permission_date->toDateString().' '.$permissionRequest->end_time);

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        return (int) $start->diffInMinutes($end);
    }
}
