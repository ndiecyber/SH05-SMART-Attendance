<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\LeaveRequest;
use App\Models\PermissionRequest;
use App\Notifications\ActivityNotification;
use App\Services\ActivityNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function leaveIndex(Request $request): View
    {
        $status = $request->validate(['status' => ['nullable', 'in:all,pending,approved,rejected']])['status'] ?? 'all';
        $requests = LeaveRequest::query()->with('user:id,name,email,job_title,division,avatar_path')
            ->when($status !== 'all', fn (Builder $query) => $query->whereRaw('LOWER(status) = ?', [$status]))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.approvals.index', ['type' => 'leave', 'requests' => $requests, 'status' => $status]);
    }

    public function permissionIndex(Request $request): View
    {
        $status = $request->validate(['status' => ['nullable', 'in:all,pending,approved,rejected']])['status'] ?? 'all';
        $requests = PermissionRequest::query()->with('user:id,name,email,job_title,division,avatar_path')
            ->when($status !== 'all', fn (Builder $query) => $query->whereRaw('LOWER(status) = ?', [$status]))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.approvals.index', ['type' => 'permission', 'requests' => $requests, 'status' => $status]);
    }

    public function decideLeave(Request $request, LeaveRequest $leaveRequest, ActivityNotificationService $notifications): RedirectResponse
    {
        return $this->decide($request, $leaveRequest, 'leave', $notifications);
    }

    public function decidePermission(Request $request, PermissionRequest $permissionRequest, ActivityNotificationService $notifications): RedirectResponse
    {
        return $this->decide($request, $permissionRequest, 'permission', $notifications);
    }

    private function decide(Request $request, LeaveRequest|PermissionRequest $approvalRequest, string $type, ActivityNotificationService $notifications): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:2000', 'required_if:decision,rejected'],
        ]);

        $recipient = DB::transaction(function () use ($approvalRequest, $request, $data, $type) {
            $locked = $approvalRequest->newQuery()->lockForUpdate()->findOrFail($approvalRequest->getKey());
            if (strtolower($locked->status) !== 'pending') {
                abort(409, 'Permintaan ini sudah diproses oleh Admin lain.');
            }
            $locked->forceFill([
                'status' => $data['decision'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_note' => $data['review_note'] ?? null,
            ])->save();
            AdminAuditLog::query()->create([
                'admin_id' => $request->user()->id,
                'action' => 'request.'.$data['decision'],
                'auditable_type' => $type === 'leave' ? LeaveRequest::class : PermissionRequest::class,
                'auditable_id' => $locked->id,
                'before_values' => ['status' => 'pending'],
                'after_values' => ['status' => $data['decision'], 'review_note' => $data['review_note'] ?? null],
            ]);

            return $locked->user;
        });

        $requestLabel = $type === 'leave' ? 'cuti' : 'izin';
        $notifications->send($recipient, new ActivityNotification(
            title: 'Pengajuan '.ucfirst($requestLabel).' '.($data['decision'] === 'approved' ? 'disetujui' : 'ditolak'),
            description: $data['review_note'] ?? 'Pengajuan Anda telah diproses oleh Admin.',
            category: 'Approval',
            priority: 'normal',
            requiresAction: false,
            url: $type === 'leave' ? route('leave.index', [], false) : route('permissions.index', [], false),
            source: 'Admin Approval',
            metadata: ['request_id' => $approvalRequest->id, 'status' => $data['decision']],
        ));

        return back()->with('success', 'Keputusan pengajuan berhasil disimpan.');
    }
}
