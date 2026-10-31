<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\ActivityNotification;
use App\Services\ActivityNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    private const LEAVE_TYPES = ['Annual Leave', 'Sick Leave', 'Personal Leave', 'Unpaid Leave'];

    public function index(Request $request): View
    {
        $timezone = config('app.timezone');
        $today = CarbonImmutable::today($timezone);
        $yearStart = $today->startOfYear();
        $yearEnd = $today->endOfYear();
        $leaveRequests = $request->user()->leaveRequests()->latest()->get();

        $yearRequests = $leaveRequests->filter(function (LeaveRequest $leave) use ($yearStart, $yearEnd): bool {
            $startDate = $leave->start_date->toDateString();

            return $startDate >= $yearStart->toDateString() && $startDate <= $yearEnd->toDateString();
        });
        $approvedRequests = $yearRequests->filter(fn (LeaveRequest $leave): bool => strtolower($leave->status) === 'approved');
        $upcomingLeaves = $leaveRequests
            ->filter(fn (LeaveRequest $leave): bool => strtolower($leave->status) === 'approved'
                && $leave->end_date->greaterThanOrEqualTo($today))
            ->sortBy(fn (LeaveRequest $leave): string => $leave->start_date->toDateString())
            ->values()
            ->map(fn (LeaveRequest $leave): array => $this->leaveData($leave));

        $leaveData = $leaveRequests->map(fn (LeaveRequest $leave): array => $this->leaveData($leave))->values();
        $approvedDays = $approvedRequests->sum(fn (LeaveRequest $leave): int => $this->daysWithinYear($leave, $yearStart, $yearEnd));

        return view('leave.index', [
            'leaveData' => $leaveData,
            'upcomingLeaves' => $upcomingLeaves,
            'leaveStats' => [
                'pending' => $leaveRequests->filter(fn (LeaveRequest $leave): bool => strtolower($leave->status) === 'pending')->count(),
                'approvedYtd' => $approvedRequests->count(),
                'rejectedYtd' => $yearRequests->filter(fn (LeaveRequest $leave): bool => strtolower($leave->status) === 'rejected')->count(),
                'approvedDaysYtd' => $approvedDays,
                'totalRequests' => $leaveRequests->count(),
            ],
            'availableYears' => $leaveRequests
                ->flatMap(fn (LeaveRequest $leave): array => [
                    (int) $leave->created_at->format('Y'),
                    (int) $leave->start_date->format('Y'),
                    (int) $leave->end_date->format('Y'),
                ])
                ->unique()
                ->sortDesc()
                ->values(),
        ]);
    }

    public function create(): View
    {
        return view('leave.create');
    }

    public function store(Request $request, ActivityNotificationService $notificationService): RedirectResponse
    {
        $data = $request->validate([
            'leave_type' => ['required', 'in:'.implode(',', self::LEAVE_TYPES)],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $leaveRequest = $request->user()->leaveRequests()->create($data);
        } catch (QueryException $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Pengajuan cuti gagal disimpan. Silakan coba lagi.');
        }

        $notificationService->send($request->user(), new ActivityNotification(
            title: 'Pengajuan cuti berhasil dikirim',
            description: $leaveRequest->leave_type.' diajukan untuk '.$leaveRequest->start_date->format('d M Y').' sampai '.$leaveRequest->end_date->format('d M Y').'.',
            category: 'Approval',
            priority: 'normal',
            requiresAction: false,
            url: route('leave.index', [], false),
            source: 'Leave Request',
            metadata: ['leave_request_id' => $leaveRequest->id, 'status' => $leaveRequest->status],
        ));

        User::query()->where('role', 'admin')->where('is_active', true)->each(function (User $admin) use ($notificationService, $leaveRequest, $request): void {
            $notificationService->send($admin, new ActivityNotification(
                title: 'Pengajuan cuti baru',
                description: $request->user()->name.' mengajukan '.$leaveRequest->leave_type.'.',
                category: 'Approval',
                priority: 'normal',
                requiresAction: true,
                url: route('admin.leave-requests.index', [], false),
                source: 'Leave Request',
                metadata: ['leave_request_id' => $leaveRequest->id, 'worker_id' => $request->user()->id],
            ));
        });

        return redirect()->route('leave.index')->with('success', 'Pengajuan cuti berhasil dikirim.');
    }

    public function cancel(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $ownedRequest = $request->user()->leaveRequests()
            ->whereKey($leaveRequest->getKey())
            ->firstOrFail();

        if (strtolower($ownedRequest->status) !== 'pending') {
            return redirect()->route('leave.index')
                ->with('error', 'Hanya pengajuan berstatus Pending yang dapat dibatalkan.');
        }

        try {
            $updatedRows = $request->user()->leaveRequests()
                ->whereKey($ownedRequest->getKey())
                ->where('status', $ownedRequest->status)
                ->update([
                    'status' => 'Cancelled',
                    'updated_at' => now(),
                ]);
        } catch (QueryException $exception) {
            report($exception);

            return redirect()->route('leave.index')
                ->with('error', 'Pengajuan cuti gagal dibatalkan. Silakan coba lagi.');
        }

        if ($updatedRows !== 1) {
            return redirect()->route('leave.index')
                ->with('error', 'Status pengajuan sudah berubah. Muat ulang halaman lalu coba lagi.');
        }

        return redirect()->route('leave.index')->with('success', 'Pengajuan cuti berhasil dibatalkan.');
    }

    /** @return array{id: int, leave_type: string, start_date: string, end_date: string, duration: int, reason: string, status: string, request_date: string} */
    private function leaveData(LeaveRequest $leave): array
    {
        return [
            'id' => $leave->id,
            'leave_type' => $leave->leave_type,
            'start_date' => $leave->start_date->toDateString(),
            'end_date' => $leave->end_date->toDateString(),
            'duration' => $this->duration($leave),
            'reason' => $leave->reason,
            'status' => ucfirst(strtolower($leave->status)),
            'request_date' => $leave->created_at->toDateString(),
            'review_note' => $leave->review_note,
        ];
    }

    private function duration(LeaveRequest $leave): int
    {
        return (int) $leave->start_date->diffInDays($leave->end_date) + 1;
    }

    private function daysWithinYear(LeaveRequest $leave, CarbonImmutable $yearStart, CarbonImmutable $yearEnd): int
    {
        $startDate = CarbonImmutable::parse(max($leave->start_date->toDateString(), $yearStart->toDateString()));
        $endDate = CarbonImmutable::parse(min($leave->end_date->toDateString(), $yearEnd->toDateString()));

        return $startDate->greaterThan($endDate) ? 0 : ((int) $startDate->diffInDays($endDate) + 1);
    }
}
