<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);

        return view('reports.index', [
            'reportData' => $this->buildReportData($request->user(), $filters),
            'selectedPeriod' => $filters['period'],
            'reportStartDate' => $filters['start_date'] ?? '',
            'reportEndDate' => $filters['end_date'] ?? '',
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $reportData = $this->buildReportData($request->user(), $this->validatedFilters($request));
        $fileName = 'attendance-report-'.$reportData['start_date'].'-'.$reportData['end_date'].'.csv';

        return response()->streamDownload(function () use ($reportData): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Attendance Report']);
            fputcsv($stream, ['Period', $reportData['period']]);
            fputcsv($stream, ['Start date', $reportData['start_date']]);
            fputcsv($stream, ['End date', $reportData['end_date']]);
            fputcsv($stream, []);
            foreach ([
                'Total present' => $reportData['summary']['present'],
                'Total late' => $reportData['summary']['late'],
                'Total absent' => $reportData['summary']['absent'],
                'Attendance rate' => $reportData['summary']['attendance_rate'],
                'Punctuality rate' => $reportData['summary']['punctuality_rate'],
                'Total work hours' => $reportData['summary']['work_hours'],
                'Overtime hours' => $reportData['summary']['overtime_hours'],
                'Average check-in' => $reportData['summary']['average_check_in'],
            ] as $label => $value) {
                fputcsv($stream, [$label, $value]);
            }
            fputcsv($stream, []);
            fputcsv($stream, ['attendance_date', 'is_workday', 'status', 'check_in_at', 'check_out_at', 'work_duration_minutes', 'overtime_minutes']);

            foreach ($reportData['days'] as $day) {
                fputcsv($stream, array_map(fn (mixed $value): mixed => $this->safeCsvValue($value), [
                    $day['date'], $day['is_workday'] ? 'Yes' : 'No', $day['status'],
                    $day['check_in_at'], $day['check_out_at'], $day['work_duration_minutes'], $day['overtime_minutes'],
                ]));
            }
            fclose($stream);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8', 'X-Content-Type-Options' => 'nosniff']);
    }

    /** @return array{period: string, start_date?: string, end_date?: string} */
    private function validatedFilters(Request $request): array
    {
        $filters = $request->validate([
            'period' => ['sometimes', 'in:This Week,This Month,Last Month,Custom Date Range'],
            'start_date' => ['required_if:period,Custom Date Range', 'nullable', 'date_format:Y-m-d'],
            'end_date' => ['required_if:period,Custom Date Range', 'nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);
        $filters['period'] ??= 'This Month';

        if ($filters['period'] === 'Custom Date Range') {
            $start = Carbon::parse($filters['start_date'], config('app.timezone'));
            $end = Carbon::parse($filters['end_date'], config('app.timezone'));

            if ($start->diffInDays($end) > 366) {
                throw ValidationException::withMessages(['end_date' => 'Custom report ranges may not exceed 367 calendar days.']);
            }
        }

        return $filters;
    }

    /** @param array{period: string, start_date?: string, end_date?: string} $filters
     * @return array<string, mixed>
     */
    private function buildReportData(User $user, array $filters): array
    {
        $timezone = config('app.timezone');
        $today = Carbon::today($timezone);
        [$startDate, $endDate] = match ($filters['period']) {
            'This Week' => [$today->copy()->startOfWeek(Carbon::MONDAY), $today->copy()->endOfWeek(Carbon::SUNDAY)],
            'Last Month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'Custom Date Range' => [Carbon::parse($filters['start_date'], $timezone), Carbon::parse($filters['end_date'], $timezone)],
            default => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
        };
        $lastDate = $endDate->lessThan($today) ? $endDate->copy() : $today->copy();
        $records = $startDate->greaterThan($lastDate)
            ? collect()
            : $user->attendanceRecords()->whereBetween('attendance_date', [$startDate->toDateString(), $lastDate->toDateString()])
                ->whereNotNull('check_in_at')->orderBy('attendance_date')
                ->get(['id', 'attendance_date', 'check_in_at', 'check_out_at', 'status']);
        $schedules = $startDate->greaterThan($lastDate)
            ? collect()
            : $user->schedules()->whereBetween('schedule_date', [$startDate->toDateString(), $lastDate->toDateString()])
                ->orderBy('start_time')->get(['schedule_date', 'type', 'start_time', 'end_time'])
                ->groupBy(fn (WorkSchedule $schedule): string => $schedule->schedule_date->toDateString());
        $leaves = $startDate->greaterThan($lastDate)
            ? collect()
            : LeaveRequest::query()->where('user_id', $user->id)->where('status', 'approved')
                ->whereDate('start_date', '<=', $lastDate->toDateString())->whereDate('end_date', '>=', $startDate->toDateString())
                ->get(['start_date', 'end_date']);
        $leaveDates = [];
        foreach ($leaves as $leave) {
            $leaveStart = $leave->start_date->copy()->max($startDate);
            $leaveEnd = $leave->end_date->copy()->min($lastDate);
            for ($date = $leaveStart; $date->lessThanOrEqualTo($leaveEnd); $date->addDay()) {
                $leaveDates[$date->toDateString()] = true;
            }
        }

        $recordsByDate = $records->keyBy(fn (AttendanceRecord $record): string => $record->attendance_date->toDateString());
        $days = [];
        $present = $late = $absent = $expectedWorkdays = $totalWorkSeconds = $totalOvertimeSeconds = $checkInSeconds = $checkInCount = 0;

        if (! $startDate->greaterThan($lastDate)) {
            for ($date = $startDate->copy(); $date->lessThanOrEqualTo($lastDate); $date->addDay()) {
                $key = $date->toDateString();
                $record = $recordsByDate->get($key);
                $dateSchedules = $schedules->get($key, collect());
                $isWorkday = ($date->isWeekday() || $dateSchedules->isNotEmpty()) && ! isset($leaveDates[$key]);
                $status = 'none';
                $workSeconds = $overtimeSeconds = null;

                if ($record !== null) {
                    $shift = $dateSchedules->first(fn (WorkSchedule $schedule): bool => strcasecmp($schedule->type, 'Workshift') === 0);
                    $isLate = $shift !== null
                        ? $record->check_in_at->greaterThan(Carbon::parse($key.' '.$shift->start_time, $timezone))
                        : strtolower($record->status) === 'late';
                    $status = $isLate ? 'late' : 'present';
                    $isLate ? $late++ : $present++;
                    $checkInSeconds += ((int) $record->check_in_at->format('H') * 3600)
                        + ((int) $record->check_in_at->format('i') * 60) + (int) $record->check_in_at->format('s');
                    $checkInCount++;

                    if ($record->check_out_at !== null) {
                        $duration = $record->check_out_at->getTimestamp() - $record->check_in_at->getTimestamp();
                        if ($duration > 0 && $duration <= 86400) {
                            $workSeconds = $duration;
                            $totalWorkSeconds += $duration;
                            if ($shift !== null) {
                                $shiftStart = Carbon::parse($key.' '.$shift->start_time, $timezone);
                                $shiftEnd = Carbon::parse($key.' '.$shift->end_time, $timezone);
                                if (! $shiftEnd->greaterThan($shiftStart)) {
                                    $shiftEnd->addDay();
                                }
                                $overtimeSeconds = max(0, $duration - ($shiftEnd->getTimestamp() - $shiftStart->getTimestamp()));
                                $totalOvertimeSeconds += $overtimeSeconds;
                            }
                        }
                    }
                    if ($isWorkday || $record !== null) {
                        $expectedWorkdays++;
                    }
                } elseif ($isWorkday && $date->lessThan($today)) {
                    $status = 'absent';
                    $absent++;
                    $expectedWorkdays++;
                }

                $days[] = [
                    'date' => $key, 'label' => $date->format('d M'), 'is_workday' => $isWorkday, 'status' => $status,
                    'check_in_at' => $record?->check_in_at?->format('H:i'), 'check_out_at' => $record?->check_out_at?->format('H:i'),
                    'work_duration_minutes' => $workSeconds === null ? null : intdiv($workSeconds, 60),
                    'overtime_minutes' => $overtimeSeconds === null ? null : intdiv($overtimeSeconds, 60),
                    'work_hours' => $workSeconds === null ? 0 : round($workSeconds / 3600, 2),
                    'overtime_hours' => $overtimeSeconds === null ? 0 : round($overtimeSeconds / 3600, 2),
                ];
            }
        }

        $attended = $present + $late;

        return [
            'period' => $filters['period'], 'start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString(),
            'summary' => [
                'present' => $present, 'late' => $late, 'absent' => $absent, 'expected_workdays' => $expectedWorkdays,
                'attendance_rate' => $expectedWorkdays === 0 ? '0.0%' : number_format($attended / $expectedWorkdays * 100, 1).'%',
                'punctuality_rate' => $attended === 0 ? '0.0%' : number_format($present / $attended * 100, 1).'%',
                'work_hours' => $this->formatHours($totalWorkSeconds), 'work_hours_decimal' => round($totalWorkSeconds / 3600, 2),
                'overtime_hours' => $this->formatHours($totalOvertimeSeconds),
                'average_check_in' => $checkInCount === 0 ? '--:--' : gmdate('H:i', (int) round($checkInSeconds / $checkInCount)),
            ],
            'days' => $days,
        ];
    }

    private function formatHours(int $seconds): string
    {
        return intdiv($seconds, 3600).'h '.intdiv($seconds % 3600, 60).'m';
    }

    private function safeCsvValue(mixed $value): mixed
    {
        return is_string($value) && preg_match('/^\s*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
    }
}
