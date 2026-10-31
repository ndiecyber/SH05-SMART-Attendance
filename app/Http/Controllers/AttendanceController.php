<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSetting;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Notifications\ActivityNotification;
use App\Services\ActivityNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $today = Carbon::today(config('app.timezone'));
        $historyRecords = $user->attendanceRecords()
            ->whereNotNull('check_in_at')
            ->orderByDesc('attendance_date')
            ->orderByDesc('check_in_at')
            ->orderByDesc('id')
            ->limit(16)
            ->get();
        $historySchedules = $user->schedules()
            ->whereIn('schedule_date', $historyRecords->pluck('attendance_date')->map(fn (Carbon $date): string => $date->toDateString())->unique())
            ->orderBy('start_time')
            ->get(['schedule_date', 'type', 'start_time'])
            ->groupBy(fn (WorkSchedule $schedule): string => $schedule->schedule_date->toDateString());
        $historyRows = $historyRecords->map(function (AttendanceRecord $record) use ($historySchedules): array {
            $status = $this->attendanceStatus($record, $historySchedules);
            $durationSeconds = $record->check_out_at === null
                ? null
                : $record->check_out_at->getTimestamp() - $record->check_in_at->getTimestamp();

            return [
                'record' => $record,
                'status' => $status,
                'duration' => $durationSeconds !== null && $durationSeconds > 0
                    ? intdiv($durationSeconds, 3600).'h '.intdiv($durationSeconds % 3600, 60).'m'
                    : null,
            ];
        });
        $period = 'This Month';
        $dashboardData = $this->buildAttendanceDashboardData($user, $today, $today, $period);

        return view('attendance.index', [
            'historyRows' => $historyRows,
            'dashboardData' => $dashboardData,
            'selectedPeriod' => $period,
            'attendanceToday' => $today->toDateString(),
        ]);
    }

    public function attendanceData(Request $request): JsonResponse
    {
        $data = $request->validate([
            'month' => ['sometimes', 'date_format:Y-m'],
            'anchor_date' => ['sometimes', 'date_format:Y-m-d'],
            'period' => ['sometimes', 'in:This Week,This Month,Last Month'],
        ]);
        $timezone = config('app.timezone');
        $today = Carbon::today($timezone);
        $month = Carbon::createFromFormat('!Y-m', $data['month'] ?? $today->format('Y-m'), $timezone);
        $anchorDate = isset($data['anchor_date'])
            ? Carbon::createFromFormat('!Y-m-d', $data['anchor_date'], $timezone)
            : $today->copy();

        if ($anchorDate->format('Y-m') !== $month->format('Y-m')) {
            $anchorDate = $month->copy();
        }

        return response()->json($this->buildAttendanceDashboardData(
            $request->user(),
            $month,
            $anchorDate,
            $data['period'] ?? 'This Month',
        ));
    }

    public function checkInPage(Request $request): View
    {
        $today = $request->user()->attendanceRecords()->whereDate('attendance_date', today())->first();
        $officeCoordinates = $this->officeCoordinates();
        $network = $this->networkValidation($request);
        $remoteAddress = $request->server('REMOTE_ADDR');
        $forwardedFor = $request->header('X-Forwarded-For');
        $realIpHeader = $request->header('X-Real-IP');
        $localEnvironment = $network['local_environment'];

        return view('attendance.check-in', [
            'todayRecord' => $today,
            'attendanceStatistics' => $this->attendanceStatistics($request->user()),
            'recentRecords' => $request->user()->attendanceRecords()
                ->orderByDesc('attendance_date')
                ->orderByDesc('check_in_at')
                ->limit(5)
                ->get(),
            'officeCoordinates' => $officeCoordinates,
            'officeRadiusMeters' => AttendanceSetting::query()->value('radius_meters') ?? config('attendance.radius_meters'),
            'gpsWarningAccuracyMeters' => AttendanceSetting::query()->value('gps_accuracy_threshold_meters') ?? config('attendance.gps_warning_accuracy_meters'),
            'networkConfigured' => $network['network_configured'],
            'networkIpDetected' => $network['network_ip_detected'],
            'networkAllowed' => $network['network_allowed'],
            'requestIp' => $network['request_ip'],
            'detectedPublicIp' => $network['detected_public_ip'],
            'networkFailureMessage' => $network['failure_message'],
            'networkLocalEnvironment' => $localEnvironment,
            'networkDebug' => $localEnvironment ? [
                'request_ip' => $network['request_ip'],
                'detected_public_ip' => $network['detected_public_ip'],
                'remote_addr' => $remoteAddress,
                'x_forwarded_for' => $forwardedFor,
                'x_real_ip' => $realIpHeader,
                'office_allowed_ip' => $network['office_allowed_ip'],
                'ip_matches' => $network['network_allowed'],
                'status' => $network['network_allowed'] ? 'VALID' : 'INVALID',
                'message' => $network['failure_message'],
            ] : null,
        ]);
    }

    public function logs(Request $request): View
    {
        $records = $request->user()->attendanceRecords()
            ->orderByDesc('attendance_date')
            ->orderByDesc('check_in_at')
            ->orderByDesc('id')
            ->get();
        $attendanceEvents = $records->flatMap(fn (AttendanceRecord $record): array => array_values(array_filter([
            $this->attendanceEvent($record, true),
            $this->attendanceEvent($record, false),
        ])))->values();
        $recordsByDate = $records->keyBy(fn (AttendanceRecord $record): string => $record->attendance_date->format('Y-m-d'));
        $today = today();
        $last14Days = collect(range(13, 0))->map(function (int $daysAgo) use ($today, $recordsByDate): array {
            $date = $today->copy()->subDays($daysAgo);
            $record = $recordsByDate->get($date->format('Y-m-d'));

            return [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D, d M'),
                'relative_label' => $daysAgo === 0 ? 'Today' : ($daysAgo === 1 ? 'Yesterday' : $date->diffForHumans($today, true).' ago'),
                'check_in_at' => $record?->check_in_at?->format('H:i'),
                'check_out_at' => $record?->check_out_at?->format('H:i'),
                'status' => $record?->check_in_at ? $record->status : null,
            ];
        })->values();

        return view('attendance.logs', [
            'records' => $records,
            'attendanceEvents' => $attendanceEvents,
            'last14Days' => $last14Days,
        ]);
    }

    public function exportLogs(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'event' => ['nullable', 'in:checkIn,checkOut,break,other'],
            'type' => ['nullable', 'in:gps,network,photo,other'],
            'status' => ['nullable', 'in:present,late,absent'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        if (! empty($filters['start_date']) && ! empty($filters['end_date'])
            && Carbon::parse($filters['end_date'])->lessThan(Carbon::parse($filters['start_date']))) {
            throw ValidationException::withMessages(['end_date' => 'The end date must be on or after the start date.']);
        }

        $records = $request->user()->attendanceRecords()->orderByDesc('attendance_date')->orderByDesc('check_in_at');

        if (! empty($filters['start_date'])) {
            $records->whereDate('attendance_date', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $records->whereDate('attendance_date', '<=', $filters['end_date']);
        }
        if (($filters['event'] ?? null) === 'checkIn') {
            $records->whereNotNull('check_in_at');
        } elseif (($filters['event'] ?? null) === 'checkOut') {
            $records->whereNotNull('check_out_at');
        } elseif (in_array($filters['event'] ?? null, ['break', 'other'], true)) {
            $records->whereRaw('1 = 0');
        }
        if (! empty($filters['status'])) {
            if ($filters['status'] === 'absent') {
                $records->whereRaw('1 = 0');
            } else {
                $records->where('status', $filters['status']);
            }
        }
        if (! empty($filters['location'])) {
            $records->where('location', 'like', '%'.$filters['location'].'%');
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $records->where(function ($query) use ($search): void {
                $query->where('location', 'like', '%'.$search.'%')
                    ->orWhere('ip_address', 'like', '%'.$search.'%')
                    ->orWhereDate('attendance_date', $search);

                if (str_contains(strtolower($search), 'check in')) {
                    $query->orWhereNotNull('check_in_at');
                }
                if (str_contains(strtolower($search), 'check out')) {
                    $query->orWhereNotNull('check_out_at');
                }
                if (str_contains(strtolower($search), 'not recorded')) {
                    $query->orWhereNotNull('check_in_at')->orWhereNotNull('check_out_at');
                }
            });
        }
        if (($filters['type'] ?? null) === 'gps') {
            $records->whereNotNull('latitude')->whereNotNull('longitude');
        } elseif (($filters['type'] ?? null) === 'network') {
            $records->whereNotNull('ip_address');
        } elseif (($filters['type'] ?? null) === 'photo') {
            $records->where(function ($query): void {
                $query->whereNotNull('check_in_photo_path')->orWhereNotNull('check_out_photo_path');
            });
        } elseif (($filters['type'] ?? null) === 'other') {
            $records->whereNull('latitude')->whereNull('longitude')
                ->whereNull('ip_address')->whereNull('check_in_photo_path')->whereNull('check_out_photo_path');
        }

        $userName = $request->user()->name;
        $fileName = 'attendance-log-'.Carbon::today(config('app.timezone'))->toDateString().'.csv';

        return response()->streamDownload(function () use ($records, $userName): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['employee_name', 'attendance_date', 'check_in_at', 'break_start_at', 'break_end_at', 'check_out_at', 'status', 'work_duration_minutes', 'location', 'ip_address', 'verification_methods']);

            foreach ($records->cursor() as $record) {
                $workDurationMinutes = null;
                if ($record->check_in_at !== null && $record->check_out_at !== null) {
                    $durationSeconds = $record->check_out_at->getTimestamp() - $record->check_in_at->getTimestamp();
                    $workDurationMinutes = $durationSeconds > 0 && $durationSeconds <= 86400
                        ? intdiv($durationSeconds, 60)
                        : null;
                }

                $verificationMethods = [];
                if ($record->latitude !== null && $record->longitude !== null) {
                    $verificationMethods[] = 'GPS';
                }
                if ($record->ip_address !== null) {
                    $verificationMethods[] = 'Network';
                }
                if ($record->check_in_photo_path !== null || $record->check_out_photo_path !== null) {
                    $verificationMethods[] = 'Photo';
                }

                $values = [
                    $userName,
                    $record->attendance_date->toDateString(),
                    $record->check_in_at?->format('Y-m-d H:i:s'),
                    '',
                    '',
                    $record->check_out_at?->format('Y-m-d H:i:s'),
                    $record->status,
                    $workDurationMinutes,
                    $record->location,
                    $record->ip_address,
                    implode(', ', $verificationMethods),
                ];
                fputcsv($stream, array_map(fn (mixed $value): mixed => $this->safeCsvValue($value), $values));
            }

            fclose($stream);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function safeCsvValue(mixed $value): mixed
    {
        if (! is_string($value) || preg_match('/^\s*[=+\-@]/u', $value) !== 1) {
            return $value;
        }

        return "'".$value;
    }

    public function resetCheckIn(Request $request): RedirectResponse
    {
        abort_unless($this->attendanceResetEnabled(), 404);

        $record = $request->user()->attendanceRecords()
            ->whereDate('attendance_date', today())
            ->first();

        abort_unless($record?->check_in_at !== null, 409);

        if ($record !== null) {
            $photoPaths = array_filter([
                $record->check_in_photo_path,
                $record->check_out_photo_path,
            ]);
            $record->delete();

            foreach ($photoPaths as $photoPath) {
                Storage::disk('local')->delete($photoPath);
            }
        }

        return redirect()->route('attendance.check-in')
            ->with('status', 'Check In dan Check Out hari ini berhasil direset.');
    }

    public function resetCheckOut(Request $request): RedirectResponse
    {
        abort_unless($this->attendanceResetEnabled(), 404);

        $record = $request->user()->attendanceRecords()
            ->whereDate('attendance_date', today())
            ->first();

        abort_unless(
            $record?->check_in_at !== null && $record->check_out_at !== null,
            409,
        );

        if ($record?->check_out_at !== null) {
            $photoPath = $record->check_out_photo_path;
            $record->check_out_at = null;
            $record->check_out_photo_path = null;
            if ($record->check_in_at === null) {
                $record->delete();
            } else {
                $record->save();
            }

            if ($photoPath !== null) {
                Storage::disk('local')->delete($photoPath);
            }
        }

        return redirect()->route('attendance.check-in')
            ->with('status', 'Check Out hari ini berhasil direset. Data Check In tetap dipertahankan.');
    }

    public function record(Request $request, ActivityNotificationService $notificationService): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:checkin,checkout'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['required', 'numeric', 'min:0'],
            'client_public_ip' => ['nullable', 'string', 'max:45'],
            'location' => ['nullable', 'string', 'max:500'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'latitude.required' => 'Lokasi GPS tidak tersedia. Izinkan akses lokasi lalu coba lagi.',
            'latitude.numeric' => 'Koordinat latitude GPS tidak valid.',
            'latitude.between' => 'Koordinat latitude GPS tidak valid.',
            'longitude.required' => 'Lokasi GPS tidak tersedia. Izinkan akses lokasi lalu coba lagi.',
            'longitude.numeric' => 'Koordinat longitude GPS tidak valid.',
            'longitude.between' => 'Koordinat longitude GPS tidak valid.',
            'accuracy.required' => 'Akurasi GPS tidak tersedia. Ambil lokasi kembali lalu coba lagi.',
            'accuracy.numeric' => 'Akurasi GPS tidak valid.',
            'accuracy.min' => 'Akurasi GPS tidak valid.',
            'photo.required' => 'Foto absensi wajib diambil sebelum dikirim.',
        ]);

        $attendanceSettings = AttendanceSetting::query()->first();
        $gpsRequired = $attendanceSettings?->gps_verification_required ?? true;
        $ipRequired = $attendanceSettings?->ip_verification_required ?? true;
        $officeCoordinates = $this->officeCoordinates();
        $officeIp = $this->officeAllowedIp();
        $radius = (float) ($attendanceSettings?->radius_meters ?? config('attendance.radius_meters'));

        if (($gpsRequired && $officeCoordinates === null) || ($ipRequired && $officeIp === null) || ! is_finite($radius) || $radius <= 0) {
            return response()->json([
                'success' => false,
                'allowed' => false,
                'message' => 'Absensi belum dapat dilakukan. Konfigurasi lokasi atau jaringan kantor belum lengkap.',
            ], 503);
        }

        $distance = $officeCoordinates === null ? null : $this->distanceInMeters(
            (float) $data['latitude'],
            (float) $data['longitude'],
            $officeCoordinates['latitude'],
            $officeCoordinates['longitude'],
        );
        $gpsValid = ! $gpsRequired || ($distance !== null && $distance <= $radius);
        $network = $this->networkValidation($request, $data['client_public_ip'] ?? null);
        $networkAllowed = ! $ipRequired || $network['network_allowed'];
        $attendanceAllowed = $gpsValid && $networkAllowed;

        if (! $attendanceAllowed) {
            $messages = [];

            if (! $gpsValid) {
                $messages[] = $distance === null
                    ? 'GPS tidak valid: lokasi kantor belum dikonfigurasi.'
                    : 'GPS tidak valid: jarak Anda '.round($distance, 1).' meter dari kantor; batasnya '.$radius.' meter.';
            }

            if (! $networkAllowed) {
                $messages[] = $network['failure_message'];
            }

            $response = [
                'success' => false,
                'allowed' => false,
                'message' => implode(' ', $messages),
                'gps_valid' => $gpsValid,
                'network_valid' => $networkAllowed,
                'network_ip_detected' => $network['network_ip_detected'],
                'network_message' => $network['failure_message'],
                'distance_from_office' => $distance === null ? null : round($distance, 2),
                'radius_meters' => $radius,
            ];

            if (app()->environment(['local', 'development'])) {
                $response['debug'] = $this->attendanceDebugData(
                    (float) $data['latitude'],
                    (float) $data['longitude'],
                    (float) $data['accuracy'],
                    $distance ?? 0.0,
                    $radius,
                    $network['request_ip'],
                    $network['detected_public_ip'],
                    $network['office_allowed_ip'],
                    $gpsValid,
                    $networkAllowed,
                );
            }

            return response()->json($response, 422);
        }

        $record = $request->user()->attendanceRecords()->firstOrNew(['attendance_date' => today()]);
        if ($data['action'] === 'checkin') {
            if ($record->exists && $record->check_in_at) {
                return response()->json([
                    'success' => false,
                    'allowed' => false,
                    'gps_valid' => true,
                    'network_valid' => true,
                    'message' => 'Check-in hari ini sudah tersimpan.',
                ], 409);
            }
            if ($record->exists && $record->check_out_at) {
                return response()->json([
                    'success' => false,
                    'allowed' => false,
                    'gps_valid' => true,
                    'network_valid' => true,
                    'message' => 'Check-out hari ini masih tersimpan. Reset Check Out terlebih dahulu sebelum mengulang Check In.',
                ], 409);
            }
            $record->check_in_at = now();
            $record->status = 'present';
        } else {
            if (! $record->exists || ! $record->check_in_at || $record->check_out_at) {
                return response()->json([
                    'success' => false,
                    'allowed' => false,
                    'gps_valid' => true,
                    'network_valid' => true,
                    'message' => 'Check-out memerlukan check-in yang belum ditutup.',
                ], 422);
            }
            $record->check_out_at = now();
        }

        $record->latitude = $data['latitude'];
        $record->longitude = $data['longitude'];
        $record->location = $data['location'] ?? null;
        $record->distance_from_office = $distance === null ? null : round($distance, 2);
        $record->location_accuracy = round((float) $data['accuracy'], 2);
        $record->ip_address = $network['detected_public_ip'];
        $photoPath = $request->file('photo')->store('attendance', 'local');
        if ($data['action'] === 'checkin') {
            $record->check_in_photo_path = $photoPath;
        } else {
            $record->check_out_photo_path = $photoPath;
        }
        $record->save();

        $actionLabel = $data['action'] === 'checkin' ? 'Check-in' : 'Check-out';
        $actionTimestamp = $data['action'] === 'checkin' ? $record->check_in_at : $record->check_out_at;
        $notificationService->send($request->user(), new ActivityNotification(
            title: $actionLabel.' berhasil dicatat',
            description: $actionLabel.' tanggal '.$record->attendance_date->format('d M Y').' tersimpan pada '.$actionTimestamp?->format('H:i').' WIB.',
            category: 'Attendance',
            priority: 'normal',
            requiresAction: false,
            url: route('attendance.check-in', [], false),
            source: 'Smart Attendance',
            metadata: ['attendance_record_id' => $record->id, 'action' => $data['action']],
        ));

        User::query()->where('role', 'admin')->where('is_active', true)->each(function (User $admin) use ($notificationService, $request, $actionLabel, $actionTimestamp): void {
            $notificationService->send($admin, new ActivityNotification(
                title: $request->user()->name.' '.$actionLabel.' berhasil',
                description: 'Tercatat pada '.$actionTimestamp?->format('d M Y H:i').' WIB.',
                category: 'Attendance',
                priority: 'normal',
                requiresAction: false,
                url: route('admin.worker-attendance.index', [], false),
                source: 'Smart Attendance',
                metadata: ['worker_id' => $request->user()->id, 'attendance_record_id' => $record->id, 'action' => $data['action']],
            ));
        });

        return response()->json([
            'success' => true,
            'allowed' => true,
            'message' => 'Absensi berhasil dicatat.',
            'action' => $data['action'],
            'check_in_at' => $record->check_in_at?->format('H:i'),
            'check_out_at' => $record->check_out_at?->format('H:i'),
            'status' => $record->status,
            'gps_valid' => true,
            'network_valid' => true,
            'network_ip_detected' => $network['network_ip_detected'],
            'network_message' => null,
            'distance_from_office' => $distance === null ? null : round($distance, 2),
            'location_accuracy' => round((float) $data['accuracy'], 2),
            'attendance' => [
                'id' => $record->id,
                'attendance_date' => $record->attendance_date->toDateString(),
                'check_in_at' => $record->check_in_at?->format('Y-m-d H:i:s'),
                'check_out_at' => $record->check_out_at?->format('Y-m-d H:i:s'),
                'status' => $record->status,
                'location' => $record->location,
            ],
            'statistics' => $this->attendanceStatistics($request->user()),
            'debug' => app()->environment(['local', 'development'])
                ? $this->attendanceDebugData(
                    (float) $data['latitude'],
                    (float) $data['longitude'],
                    (float) $data['accuracy'],
                    $distance,
                    $radius,
                    $network['request_ip'],
                    $network['detected_public_ip'],
                    $network['office_allowed_ip'],
                    true,
                    true,
                )
                : null,
        ]);
    }

    /**
     * @return array{
     *     id: string,
     *     time: string,
     *     date: string,
     *     rawDate: string,
     *     event: string,
     *     location: string,
     *     device: string,
     *     ip: string,
     *     verify: list<string>,
     *     status: string,
     *     anomaly: null
     * }|null
     */
    private function attendanceEvent(AttendanceRecord $record, bool $isCheckIn): ?array
    {
        $timestamp = $isCheckIn ? $record->check_in_at : $record->check_out_at;

        if ($timestamp === null) {
            return null;
        }

        $photoPath = $isCheckIn ? $record->check_in_photo_path : $record->check_out_photo_path;
        $verificationMethods = [];

        if ($record->latitude !== null && $record->longitude !== null) {
            $verificationMethods[] = 'GPS';
        }

        if ($record->ip_address !== null) {
            $verificationMethods[] = 'Network';
        }

        if ($photoPath !== null) {
            $verificationMethods[] = 'Photo';
        }

        return [
            'id' => 'attendance-'.$record->id.'-'.($isCheckIn ? 'checkin' : 'checkout'),
            'time' => $timestamp->format('H:i:s'),
            'date' => $record->attendance_date->format('Y-m-d'),
            'rawDate' => $record->attendance_date->format('Y-m-d'),
            'event' => $isCheckIn ? 'Check In' : 'Check Out',
            'location' => $record->location ?? 'Not recorded',
            'device' => 'Not recorded',
            'ip' => $record->ip_address ?? 'Not recorded',
            'verify' => $verificationMethods,
            'status' => $record->status,
            'anomaly' => null,
        ];
    }

    /** @return array{latitude: float, longitude: float}|null */
    private function officeCoordinates(): ?array
    {
        $settings = AttendanceSetting::query()->first();
        $latitude = $settings?->office_latitude ?? config('attendance.office.latitude');
        $longitude = $settings?->office_longitude ?? config('attendance.office.longitude');

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return null;
        }

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;

        if (! is_finite($latitude) || ! is_finite($longitude)
            || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        return ['latitude' => $latitude, 'longitude' => $longitude];
    }

    private function officeAllowedIp(): ?string
    {
        $settings = AttendanceSetting::query()->first();
        $allowedIps = $settings?->allowed_ips ?? [config('attendance.office_allowed_ip')];
        $ip = trim((string) collect($allowedIps)->first(fn ($candidate): bool => $this->isPublicIp($candidate)));

        return $this->isPublicIp($ip) ? $ip : null;
    }

    /**
     * @return array{
     *     request_ip: ?string,
     *     detected_public_ip: ?string,
     *     office_allowed_ip: ?string,
     *     network_configured: bool,
     *     network_ip_detected: bool,
     *     network_allowed: bool,
     *     local_environment: bool,
     *     failure_message: ?string
     * }
     */
    private function networkValidation(Request $request, ?string $clientPublicIp = null): array
    {
        $requestIp = $request->ip();
        $officeIp = $this->officeAllowedIp();
        $settings = AttendanceSetting::query()->first();
        $allowedIps = collect($settings?->allowed_ips ?? [config('attendance.office_allowed_ip')])
            ->filter(fn ($candidate): bool => $this->isPublicIp($candidate));
        $localEnvironment = app()->environment(['local', 'development']);
        /**
         * Browser IP is used only for local development. Production validates the
         * peer IP Laravel received and ignores client-supplied IP values.
         */
        $detectedPublicIp = $localEnvironment
            ? ($this->isPublicIp($clientPublicIp) ? $clientPublicIp : null)
            : ($this->isPublicIp($requestIp) ? $requestIp : null);

        $networkAllowed = $detectedPublicIp !== null
            && $allowedIps->contains(fn (string $allowedIp): bool => hash_equals($allowedIp, $detectedPublicIp));

        return [
            'request_ip' => $requestIp,
            'detected_public_ip' => $detectedPublicIp,
            'office_allowed_ip' => $officeIp,
            'network_configured' => $officeIp !== null,
            'network_ip_detected' => $detectedPublicIp !== null,
            'network_allowed' => $networkAllowed,
            'local_environment' => $localEnvironment,
            'failure_message' => $networkAllowed
                ? null
                : $this->networkFailureMessage($detectedPublicIp, $officeIp, $localEnvironment),
        ];
    }

    private function isPublicIp(?string $ip): bool
    {
        return is_string($ip)
            && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    private function networkFailureMessage(
        ?string $detectedPublicIp,
        ?string $officeIp,
        bool $showDetectedIp,
    ): string {
        if ($officeIp === null) {
            return 'Network tidak valid: OFFICE_ALLOWED_IP belum diatur sebagai IP publik yang valid.';
        }

        if ($detectedPublicIp === null) {
            if ($showDetectedIp) {
                return 'Network tidak valid: public IP tidak dapat dideteksi dari browser.';
            }

            return 'Network tidak valid: public IP request tidak dapat diverifikasi.';
        }

        if ($showDetectedIp) {
            return "Network tidak valid: public IP terdeteksi {$detectedPublicIp}, berbeda dari OFFICE_ALLOWED_IP.";
        }

        return 'Network tidak valid: public IP tidak cocok dengan OFFICE_ALLOWED_IP.';
    }

    /** @return array<string, float|int|string> */
    private function attendanceDebugData(
        float $latitude,
        float $longitude,
        float $accuracy,
        float $distance,
        float $radius,
        ?string $requestIp,
        ?string $detectedPublicIp,
        ?string $officeIp,
        bool $gpsAllowed,
        bool $networkAllowed,
    ): array {
        return [
            'user_latitude' => $latitude,
            'user_longitude' => $longitude,
            'gps_accuracy' => $accuracy,
            'office_latitude' => (float) (AttendanceSetting::query()->value('office_latitude') ?? config('attendance.office.latitude')),
            'office_longitude' => (float) (AttendanceSetting::query()->value('office_longitude') ?? config('attendance.office.longitude')),
            'distance_from_office' => round($distance, 2),
            'radius_meters' => $radius,
            'gps_status' => $gpsAllowed ? 'VALID' : 'INVALID',
            'request_ip' => $requestIp ?? 'tidak terdeteksi',
            'detected_public_ip' => $detectedPublicIp ?? 'tidak tersedia',
            'detected_ip' => $detectedPublicIp ?? 'tidak tersedia',
            'office_allowed_ip' => $officeIp ?? 'belum dikonfigurasi',
            'network_status' => $networkAllowed ? 'VALID' : 'INVALID',
            'attendance_status' => $gpsAllowed && $networkAllowed ? 'ALLOWED' : 'DENIED',
        ];
    }

    private function attendanceResetEnabled(): bool
    {
        return app()->environment(['local', 'development', 'testing']);
    }

    /**
     * @return array{
     *     calendar: array<string, array{status: string, check_in_at: ?string, check_out_at: ?string}>,
     *     summary: array{period: string, present: int, late: int, absent: int, rate: string},
     *     trend: array{labels: list<string>, present: list<int>, late: list<int>, absent: list<int>, has_attendance: bool}
     * }
     */
    private function buildAttendanceDashboardData(User $user, Carbon $monthDate, Carbon $anchorDate, string $period): array
    {
        $timezone = config('app.timezone');
        $today = Carbon::today($timezone);
        $monthStart = $monthDate->copy()->startOfMonth();
        $monthEnd = $monthDate->copy()->endOfMonth();
        [$periodStart, $periodEnd] = match ($period) {
            'This Week' => [
                $anchorDate->copy()->startOfWeek(Carbon::MONDAY),
                $anchorDate->copy()->endOfWeek(Carbon::SUNDAY),
            ],
            'Last Month' => [
                $monthStart->copy()->subMonth()->startOfMonth(),
                $monthStart->copy()->subMonth()->endOfMonth(),
            ],
            default => [$monthStart->copy(), $monthEnd->copy()],
        };
        $effectivePeriodEnd = $periodStart->greaterThan($today)
            ? null
            : ($periodEnd->lessThan($today) ? $periodEnd->copy() : $today->copy());
        $queryStart = $monthStart->copy();
        $queryEnd = $monthEnd->lessThan($today) ? $monthEnd->copy() : $today->copy();

        if ($effectivePeriodEnd !== null) {
            if ($periodStart->lessThan($queryStart)) {
                $queryStart = $periodStart->copy();
            }
            if ($effectivePeriodEnd->greaterThan($queryEnd)) {
                $queryEnd = $effectivePeriodEnd->copy();
            }
        }

        $records = $queryStart->greaterThan($queryEnd)
            ? collect()
            : $user->attendanceRecords()
                ->whereBetween('attendance_date', [$queryStart->toDateString(), $queryEnd->toDateString()])
                ->whereNotNull('check_in_at')
                ->orderBy('attendance_date')
                ->get(['id', 'attendance_date', 'check_in_at', 'check_out_at', 'status']);
        $schedulesByDate = $queryStart->greaterThan($queryEnd)
            ? collect()
            : $user->schedules()
                ->whereBetween('schedule_date', [$queryStart->toDateString(), $queryEnd->toDateString()])
                ->orderBy('start_time')
                ->get(['schedule_date', 'type', 'start_time'])
                ->groupBy(fn (WorkSchedule $schedule): string => $schedule->schedule_date->toDateString());
        $approvedLeaves = $queryStart->greaterThan($queryEnd)
            ? collect()
            : LeaveRequest::query()
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $queryEnd->toDateString())
                ->whereDate('end_date', '>=', $queryStart->toDateString())
                ->get(['start_date', 'end_date']);
        $recordsByDate = $records->keyBy(fn (AttendanceRecord $record): string => $record->attendance_date->toDateString());
        $leaveDates = [];

        foreach ($approvedLeaves as $leave) {
            for ($date = $leave->start_date->copy(); $date->lessThanOrEqualTo($leave->end_date); $date->addDay()) {
                $leaveDates[$date->toDateString()] = true;
            }
        }

        $calendarData = [];
        for ($date = $monthStart->copy(); $date->lessThanOrEqualTo($monthEnd); $date->addDay()) {
            $dateKey = $date->toDateString();
            $record = $recordsByDate->get($dateKey);
            $status = $record === null
                ? ($date->lessThan($today)
                    && $this->isExpectedWorkday($date, $schedulesByDate)
                    && ! isset($leaveDates[$dateKey]) ? 'absent' : 'none')
                : $this->attendanceStatus($record, $schedulesByDate);

            $calendarData[$dateKey] = [
                'status' => $status,
                'check_in_at' => $record?->check_in_at?->format('H:i'),
                'check_out_at' => $record?->check_out_at?->format('H:i'),
            ];
        }

        $present = 0;
        $late = 0;
        $absent = 0;
        $expectedWorkdays = 0;
        $hasAttendanceInPeriod = false;
        $trendLabels = $period === 'This Week'
            ? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
            : ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'];
        $trendPresent = array_fill(0, count($trendLabels), 0);
        $trendLate = array_fill(0, count($trendLabels), 0);
        $trendAbsent = array_fill(0, count($trendLabels), 0);

        if ($effectivePeriodEnd !== null) {
            for ($date = $periodStart->copy(); $date->lessThanOrEqualTo($effectivePeriodEnd); $date->addDay()) {
                $dateKey = $date->toDateString();
                $record = $recordsByDate->get($dateKey);
                $isWorkday = $this->isExpectedWorkday($date, $schedulesByDate)
                    && ! isset($leaveDates[$dateKey]);
                $status = $record === null
                    ? ($isWorkday && $date->lessThan($today) ? 'absent' : 'none')
                    : $this->attendanceStatus($record, $schedulesByDate);
                $trendIndex = $period === 'This Week'
                    ? $date->dayOfWeekIso - 1
                    : min(intdiv($date->day - 1, 7), count($trendLabels) - 1);

                if ($isWorkday && ($date->lessThan($today) || $record !== null)) {
                    $expectedWorkdays++;
                }
                if ($record !== null) {
                    $hasAttendanceInPeriod = true;
                }
                if ($status === 'present') {
                    $present++;
                    $trendPresent[$trendIndex]++;
                } elseif ($status === 'late') {
                    $late++;
                    $trendLate[$trendIndex]++;
                } elseif ($status === 'absent') {
                    $absent++;
                    $trendAbsent[$trendIndex]++;
                }
            }
        }

        return [
            'calendar' => $calendarData,
            'summary' => [
                'period' => $period,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'rate' => $expectedWorkdays === 0
                    ? '0.0%'
                    : number_format(($present + $late) / $expectedWorkdays * 100, 1).'%',
            ],
            'trend' => [
                'labels' => $trendLabels,
                'present' => $trendPresent,
                'late' => $trendLate,
                'absent' => $trendAbsent,
                'has_attendance' => $hasAttendanceInPeriod,
            ],
        ];
    }

    /** @param Collection<string, Collection<int, WorkSchedule>> $schedulesByDate */
    private function attendanceStatus(AttendanceRecord $record, Collection $schedulesByDate): string
    {
        $date = $record->attendance_date->toDateString();
        $workshift = $schedulesByDate->get($date, collect())
            ->first(fn (WorkSchedule $schedule): bool => strcasecmp($schedule->type, 'Workshift') === 0);

        if ($workshift !== null && $record->check_in_at !== null) {
            $gracePeriodMinutes = (int) (AttendanceSetting::query()->value('late_grace_period_minutes') ?? 0);
            $scheduledStart = Carbon::parse($date.' '.$workshift->start_time, config('app.timezone'))
                ->addMinutes($gracePeriodMinutes);

            return $record->check_in_at->greaterThan($scheduledStart) ? 'late' : 'present';
        }

        return strtolower($record->status) === 'late' ? 'late' : 'present';
    }

    /** @param Collection<string, Collection<int, WorkSchedule>> $schedulesByDate */
    private function isExpectedWorkday(Carbon $date, Collection $schedulesByDate): bool
    {
        return $date->isWeekday() || $schedulesByDate->has($date->toDateString());
    }

    /**
     * @return array{
     *     current_streak: string,
     *     on_time_rate: string,
     *     average_check_in: string,
     *     weekly_work_hours: string
     * }
     */
    private function attendanceStatistics(User $user): array
    {
        $today = Carbon::today(config('app.timezone'));
        $monthStart = $today->copy()->startOfMonth();
        $attendanceRecords = $user->attendanceRecords()
            ->whereNotNull('check_in_at')
            ->whereDate('attendance_date', '<=', $today->toDateString())
            ->orderBy('attendance_date')
            ->get(['attendance_date', 'check_in_at', 'check_out_at']);

        $firstAttendanceDate = $attendanceRecords->first()?->attendance_date?->toDateString() ?? $today->toDateString();
        $schedulesByDate = $user->schedules()
            ->whereBetween('schedule_date', [$firstAttendanceDate, $today->toDateString()])
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->get(['schedule_date', 'type', 'start_time'])
            ->groupBy(fn ($schedule): string => $schedule->schedule_date->toDateString());
        $attendanceDates = $attendanceRecords
            ->mapWithKeys(fn (AttendanceRecord $record): array => [$record->attendance_date->toDateString() => true]);

        $monthRecords = $attendanceRecords->filter(fn (AttendanceRecord $record): bool => $record->attendance_date->betweenIncluded($monthStart, $today)
        );
        $onTimeCount = 0;
        $scheduledCheckInCount = 0;

        foreach ($monthRecords as $record) {
            $workshift = $schedulesByDate->get($record->attendance_date->toDateString(), collect())
                ->first(fn ($schedule): bool => strcasecmp($schedule->type, 'Workshift') === 0);

            if ($workshift === null || $workshift->start_time === null) {
                continue;
            }

            $scheduledCheckInCount++;
            $gracePeriodMinutes = (int) (AttendanceSetting::query()->value('late_grace_period_minutes') ?? 0);
            $scheduledStart = Carbon::parse(
                $record->attendance_date->toDateString().' '.$workshift->start_time,
                config('app.timezone'),
            )->addMinutes($gracePeriodMinutes);

            if ($record->check_in_at->lessThanOrEqualTo($scheduledStart)) {
                $onTimeCount++;
            }
        }

        $averageCheckIn = $this->averageCheckInTime($monthRecords);
        $weeklyStart = $today->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weeklyRecords = $user->attendanceRecords()
            ->whereBetween('attendance_date', [$weeklyStart, $today->toDateString()])
            ->whereNotNull('check_in_at')
            ->whereNotNull('check_out_at')
            ->get(['check_in_at', 'check_out_at']);
        $weeklySeconds = $weeklyRecords->sum(function (AttendanceRecord $record): int {
            $duration = $record->check_out_at->getTimestamp() - $record->check_in_at->getTimestamp();

            return max(0, $duration);
        });

        return [
            'current_streak' => $this->currentAttendanceStreak($today, $attendanceDates, $schedulesByDate),
            'on_time_rate' => ($scheduledCheckInCount === 0 ? 0 : (int) round($onTimeCount / $scheduledCheckInCount * 100)).'%',
            'average_check_in' => $averageCheckIn,
            'weekly_work_hours' => intdiv($weeklySeconds, 3600).'h '.intdiv($weeklySeconds % 3600, 60).'m',
        ];
    }

    /** @param Collection<int, AttendanceRecord> $records */
    private function averageCheckInTime(Collection $records): string
    {
        if ($records->isEmpty()) {
            return '--:--';
        }

        $sinTotal = 0.0;
        $cosTotal = 0.0;
        $secondsTotal = 0;

        foreach ($records as $record) {
            $secondsAfterMidnight = ((int) $record->check_in_at->format('H') * 3600)
                + ((int) $record->check_in_at->format('i') * 60)
                + (int) $record->check_in_at->format('s');
            $angle = 2 * M_PI * $secondsAfterMidnight / 86400;
            $sinTotal += sin($angle);
            $cosTotal += cos($angle);
            $secondsTotal += $secondsAfterMidnight;
        }

        if (hypot($sinTotal, $cosTotal) < 0.000001) {
            $averageSeconds = (int) round($secondsTotal / $records->count());
        } else {
            $averageAngle = atan2($sinTotal, $cosTotal);
            $averageSeconds = (int) round(fmod(($averageAngle / (2 * M_PI) + 1) * 86400, 86400));
        }

        $averageMinutes = (int) round($averageSeconds / 60) % 1440;

        return sprintf('%02d:%02d', intdiv($averageMinutes, 60), $averageMinutes % 60);
    }

    /**
     * @param  Collection<string, bool>  $attendanceDates
     * @param  Collection<string, Collection<int, mixed>>  $schedulesByDate
     */
    private function currentAttendanceStreak(Carbon $today, Collection $attendanceDates, Collection $schedulesByDate): string
    {
        $cursor = $today->copy();

        if ($this->isScheduledWorkday($cursor, $schedulesByDate)
            && ! $attendanceDates->has($cursor->toDateString())) {
            $cursor->subDay();
        }

        $streak = 0;

        while ($cursor->lessThanOrEqualTo($today)) {
            $date = $cursor->toDateString();

            if (! $this->isScheduledWorkday($cursor, $schedulesByDate)) {
                $cursor->subDay();

                continue;
            }

            if (! $attendanceDates->has($date)) {
                break;
            }

            $streak++;
            $cursor->subDay();
        }

        return $streak.' '.($streak === 1 ? 'Day' : 'Days');
    }

    /** @param Collection<string, Collection<int, mixed>> $schedulesByDate */
    private function isScheduledWorkday(Carbon $date, Collection $schedulesByDate): bool
    {
        return $date->isWeekday() || $schedulesByDate->has($date->toDateString());
    }

    private function distanceInMeters(float $latitude, float $longitude, float $officeLatitude, float $officeLongitude): float
    {
        $earthRadius = 6_371_000;
        $latitudeDelta = deg2rad($officeLatitude - $latitude);
        $longitudeDelta = deg2rad($officeLongitude - $longitude);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitude)) * cos(deg2rad($officeLatitude)) * sin($longitudeDelta / 2) ** 2;
        $a = min(1, max(0, $a));

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
