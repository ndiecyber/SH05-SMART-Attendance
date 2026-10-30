<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceImportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:csv,txt', 'extensions:csv'],
            'return_to' => ['sometimes', 'in:attendance.index,attendance.logs'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages(['file' => 'File CSV tidak dapat dibaca.']);
        }

        try {
            $header = fgetcsv($handle);

            if ($header === false) {
                throw ValidationException::withMessages(['file' => 'File CSV kosong atau header tidak ditemukan.']);
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
            $header = array_map(fn (string $column): string => strtolower(trim($column)), $header);
            $requiredHeaders = ['attendance_date', 'check_in_at', 'check_out_at'];

            if (count($header) !== count($requiredHeaders) || array_diff($requiredHeaders, $header) !== []) {
                throw ValidationException::withMessages([
                    'file' => 'Header harus berisi attendance_date, check_in_at, check_out_at.',
                ]);
            }

            $columnIndexes = array_flip($header);
            $user = $request->user();
            $today = Carbon::today(config('app.timezone'));
            $seenDates = [];
            $rows = [];
            $errors = [];
            $line = 1;

            while (($values = fgetcsv($handle)) !== false) {
                $line++;
                if ($values === [null] || count(array_filter($values, fn ($value): bool => trim((string) $value) !== '')) === 0) {
                    continue;
                }
                if ($line > 5001) {
                    $errors[] = ['line' => $line, 'reason' => 'File melebihi batas 5.000 baris.'];
                    break;
                }
                if (count($values) !== count($requiredHeaders)) {
                    $errors[] = ['line' => $line, 'reason' => 'Jumlah kolom pada baris ini tidak sesuai header CSV.'];

                    continue;
                }

                $row = [];
                foreach ($requiredHeaders as $column) {
                    $row[$column] = trim((string) ($values[$columnIndexes[$column]] ?? ''));
                }

                $attendanceDate = $this->parseDate($row['attendance_date']);
                $checkInAt = $this->parseTimestamp($row['check_in_at']);
                $checkOutAt = $row['check_out_at'] === '' ? null : $this->parseTimestamp($row['check_out_at']);
                $reason = null;

                if ($attendanceDate === null) {
                    $reason = 'attendance_date harus berformat YYYY-MM-DD.';
                } elseif ($attendanceDate->greaterThan($today)) {
                    $reason = 'Tanggal attendance tidak boleh di masa mendatang.';
                } elseif ($checkInAt === null) {
                    $reason = 'check_in_at wajib dan harus berformat YYYY-MM-DD HH:MM[:SS].';
                } elseif ($checkInAt->toDateString() !== $attendanceDate->toDateString()) {
                    $reason = 'Tanggal check_in_at harus sama dengan attendance_date.';
                } elseif ($checkOutAt === null && $row['check_out_at'] !== '') {
                    $reason = 'Format check_out_at harus YYYY-MM-DD HH:MM[:SS].';
                } elseif ($checkOutAt !== null && ($checkOutAt->lessThanOrEqualTo($checkInAt)
                    || $checkOutAt->greaterThan($checkInAt->copy()->addDay()))) {
                    $reason = 'check_out_at harus setelah check_in_at dan maksimal 24 jam sesudahnya.';
                }

                if ($reason !== null) {
                    $errors[] = ['line' => $line, 'reason' => $reason];

                    continue;
                }

                $dateKey = $attendanceDate->toDateString();
                if (isset($seenDates[$dateKey])) {
                    $errors[] = ['line' => $line, 'reason' => 'Tanggal duplikat dalam file; record sebelumnya dipertahankan.'];

                    continue;
                }

                $seenDates[$dateKey] = true;
                $rows[$dateKey] = [
                    'attendance_date' => $dateKey,
                    'check_in_at' => $checkInAt,
                    'check_out_at' => $checkOutAt,
                    'line' => $line,
                ];
            }
        } finally {
            fclose($handle);
        }

        $existingDates = $user->attendanceRecords()
            ->whereIn('attendance_date', array_keys($rows))
            ->pluck('attendance_date')
            ->map(fn ($date): string => Carbon::parse($date)->toDateString())
            ->all();
        foreach ($existingDates as $date) {
            $errors[] = ['line' => $rows[$date]['line'], 'reason' => 'Attendance untuk tanggal ini sudah ada; record lama tidak ditimpa.'];
            unset($rows[$date]);
        }

        $schedulesByDate = $user->schedules()
            ->whereIn('schedule_date', array_keys($rows))
            ->orderBy('start_time')
            ->get(['schedule_date', 'type', 'start_time'])
            ->groupBy(fn (WorkSchedule $schedule): string => $schedule->schedule_date->toDateString());
        $importedCount = DB::transaction(function () use ($user, $rows, $schedulesByDate): int {
            foreach ($rows as $date => $row) {
                $workshift = $schedulesByDate->get($date, collect())
                    ->first(fn (WorkSchedule $schedule): bool => strcasecmp($schedule->type, 'Workshift') === 0);
                $isLate = $workshift !== null && $row['check_in_at']->greaterThan(
                    Carbon::parse($date.' '.$workshift->start_time, config('app.timezone')),
                );

                $user->attendanceRecords()->create([
                    'attendance_date' => $date,
                    'check_in_at' => $row['check_in_at'],
                    'check_out_at' => $row['check_out_at'],
                    'status' => $isLate ? 'late' : 'present',
                ]);
            }

            return count($rows);
        });

        return redirect()->route($request->input('return_to', 'attendance.index'))->with('attendanceImportSummary', [
            'imported' => $importedCount,
            'skipped' => count($errors),
            'errors' => array_slice($errors, 0, 50),
            'error_list_truncated' => count($errors) > 50,
        ]);
    }

    private function parseDate(string $value): ?Carbon
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone(config('app.timezone')));
        $parseErrors = DateTimeImmutable::getLastErrors();

        if ($date === false || ($parseErrors !== false && ($parseErrors['warning_count'] > 0 || $parseErrors['error_count'] > 0))
            || $date->format('Y-m-d') !== $value) {
            return null;
        }

        return Carbon::instance($date);
    }

    private function parseTimestamp(string $value): ?Carbon
    {
        foreach (['!Y-m-d H:i:s', '!Y-m-d H:i'] as $format) {
            $timestamp = DateTimeImmutable::createFromFormat($format, $value, new DateTimeZone(config('app.timezone')));
            $parseErrors = DateTimeImmutable::getLastErrors();
            if ($timestamp !== false && ($parseErrors === false || ($parseErrors['warning_count'] === 0 && $parseErrors['error_count'] === 0))) {
                return Carbon::instance($timestamp);
            }
        }

        return null;
    }
}
