<?php

namespace App\Http\Controllers;

use App\Models\PermissionRequest;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'permission_type' => ['required', 'in:WFH,Arrive Late,Leave Early,Pop Out,Business Trip'],
            'permission_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);
        $request->user()->permissionRequests()->create($data);

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
