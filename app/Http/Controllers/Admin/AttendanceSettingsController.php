<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\AttendanceSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AttendanceSettingsController extends Controller
{
    public function edit(): View
    {
        $settings = AttendanceSetting::query()->first() ?? new AttendanceSetting([
            'location_name' => 'LEXA Office',
            'radius_meters' => config('attendance.radius_meters', 50),
            'office_latitude' => config('attendance.office.latitude'),
            'office_longitude' => config('attendance.office.longitude'),
            'allowed_ips' => array_filter([config('attendance.office_allowed_ip')]),
        ]);

        return view('admin.settings.attendance', ['settings' => $settings]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'location_name' => ['required', 'string', 'max:120'],
            'office_latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:office_longitude'],
            'office_longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:office_latitude'],
            'office_address' => ['nullable', 'string', 'max:255'],
            'radius_meters' => ['required', 'integer', 'between:1,5000'],
            'allowed_ips' => ['nullable', 'string', 'max:2000'],
            'gps_verification_required' => ['sometimes', 'boolean'],
            'ip_verification_required' => ['sometimes', 'boolean'],
            'gps_accuracy_threshold_meters' => ['required', 'integer', 'between:1,1000'],
            'late_grace_period_minutes' => ['required', 'integer', 'between:0,180'],
        ]);

        $allowedIps = collect(preg_split('/[\s,;]+/', trim($data['allowed_ips'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))
            ->unique()->values();
        Validator::make(['allowed_ips' => $allowedIps], [
            'allowed_ips.*' => ['ip'],
        ])->validate();
        if (app()->environment('production')) {
            foreach ($allowedIps as $ip) {
                abort_if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false, 422, 'IP allowlist production harus berisi alamat IP publik.');
            }
        }

        DB::transaction(function () use ($request, $data, $allowedIps): void {
            $settings = AttendanceSetting::query()->lockForUpdate()->first() ?? new AttendanceSetting;
            $before = $settings->exists ? $settings->only([
                'location_name', 'office_latitude', 'office_longitude', 'office_address', 'radius_meters', 'allowed_ips',
                'gps_verification_required', 'ip_verification_required', 'gps_accuracy_threshold_meters', 'late_grace_period_minutes',
            ]) : null;
            $settings->fill([
                'location_name' => $data['location_name'],
                'office_latitude' => $data['office_latitude'] ?? null,
                'office_longitude' => $data['office_longitude'] ?? null,
                'office_address' => $data['office_address'] ?? null,
                'radius_meters' => $data['radius_meters'],
                'allowed_ips' => $allowedIps->all(),
                'gps_verification_required' => $request->boolean('gps_verification_required'),
                'ip_verification_required' => $request->boolean('ip_verification_required'),
                'gps_accuracy_threshold_meters' => $data['gps_accuracy_threshold_meters'],
                'late_grace_period_minutes' => $data['late_grace_period_minutes'],
                'updated_by' => $request->user()->id,
            ])->save();
            AdminAuditLog::query()->create([
                'admin_id' => $request->user()->id,
                'action' => 'attendance.settings_updated',
                'auditable_type' => AttendanceSetting::class,
                'auditable_id' => $settings->id,
                'before_values' => $before,
                'after_values' => $settings->only([
                    'location_name', 'office_latitude', 'office_longitude', 'office_address', 'radius_meters', 'allowed_ips',
                    'gps_verification_required', 'ip_verification_required', 'gps_accuracy_threshold_meters', 'late_grace_period_minutes',
                ]),
            ]);
        });

        return back()->with('success', 'Pengaturan attendance berhasil diperbarui.');
    }
}
