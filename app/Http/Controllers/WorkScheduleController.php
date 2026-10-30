<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $timezone = config('app.timezone');
        $now = CarbonImmutable::now($timezone);
        $today = $now->startOfDay();
        $weekStart = $today->startOfWeek(CarbonImmutable::MONDAY);
        $weekEnd = $weekStart->endOfWeek(CarbonImmutable::SUNDAY);

        $schedules = $request->user()->schedules()
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->get();

        $todaySchedules = $schedules->filter(
            fn (WorkSchedule $schedule): bool => $schedule->schedule_date->toDateString() === $today->toDateString()
        )->values();
        $weekSchedules = $schedules->filter(function (WorkSchedule $schedule) use ($weekStart, $weekEnd): bool {
            $date = $schedule->schedule_date->toDateString();

            return $date >= $weekStart->toDateString() && $date <= $weekEnd->toDateString();
        })->values();
        $upcomingSchedules = $schedules->filter(
            fn (WorkSchedule $schedule): bool => $this->startsAt($schedule, $timezone)->isAfter($now)
        )->values();

        $todayShift = $todaySchedules->first(
            fn (WorkSchedule $schedule): bool => strcasecmp($schedule->type, 'Workshift') === 0
        ) ?? $todaySchedules->first();

        $weeklyMinutes = $weekSchedules->sum(fn (WorkSchedule $schedule): int => $this->durationMinutes($schedule));
        $scheduleData = $schedules->map(fn (WorkSchedule $schedule): array => $this->scheduleData($schedule, $timezone))->values();

        return view('schedules.index', [
            'scheduleData' => $scheduleData,
            'scheduleStats' => [
                'todayShift' => $todayShift === null ? null : $this->timeRange($todayShift),
                'weeklyShifts' => $weekSchedules->count(),
                'upcomingShifts' => $upcomingSchedules->count(),
                'weeklyHours' => $this->formatDuration($weeklyMinutes),
            ],
            'todayDate' => $today->toDateString(),
            'weekStart' => $weekStart->toDateString(),
            'weekEnd' => $weekEnd->toDateString(),
            'upcomingEvents' => $upcomingSchedules->take(5)
                ->map(fn (WorkSchedule $schedule): array => $this->scheduleData($schedule, $timezone))
                ->values(),
        ]);
    }

    public function create(): View
    {
        return view('schedules.create', ['schedule' => null]);
    }

    public function edit(Request $request, WorkSchedule $schedule): View
    {
        return view('schedules.create', [
            'schedule' => $this->resolveOwnedSchedule($request, $schedule),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedScheduleData($request);

        try {
            $request->user()->schedules()->create($data);
        } catch (QueryException $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Jadwal gagal ditambahkan. Silakan coba lagi.');
        }

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(Request $request, WorkSchedule $schedule): RedirectResponse
    {
        $schedule = $this->resolveOwnedSchedule($request, $schedule);
        $data = $this->validatedScheduleData($request);

        try {
            $schedule->update($data);
        } catch (QueryException $exception) {
            report($exception);

            return back()->withInput()->with('error', 'Jadwal gagal diperbarui. Silakan coba lagi.');
        }

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Request $request, WorkSchedule $schedule): RedirectResponse
    {
        $schedule = $this->resolveOwnedSchedule($request, $schedule);

        try {
            $schedule->delete();
        } catch (QueryException $exception) {
            report($exception);

            return redirect()->route('schedules.index')->with('error', 'Jadwal gagal dihapus. Silakan coba lagi.');
        }

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    /** @return array{name: string, schedule_date: string, type: string, start_time: string, end_time: string, location: string} */
    private function validatedScheduleData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'schedule_date' => ['required', 'date_format:Y-m-d'],
            'type' => ['required', 'in:Workshift,Training,Meeting,Break'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => ['required', 'string', 'max:255'],
        ]);
    }

    private function resolveOwnedSchedule(Request $request, WorkSchedule $schedule): WorkSchedule
    {
        return $request->user()->schedules()->whereKey($schedule->getKey())->firstOrFail();
    }

    /** @return array<string, int|string> */
    private function scheduleData(WorkSchedule $schedule, string $timezone): array
    {
        return [
            'id' => $schedule->id,
            'name' => $schedule->name ?: $schedule->type,
            'type' => $schedule->type,
            'filterType' => $schedule->type === 'Workshift' ? 'Work Shift' : $schedule->type,
            'date' => $schedule->schedule_date->toDateString(),
            'dateLabel' => $schedule->schedule_date->timezone($timezone)->format('D, d M Y'),
            'start' => substr($schedule->start_time, 0, 5),
            'end' => substr($schedule->end_time, 0, 5),
            'location' => $schedule->location,
            'status' => ucfirst($schedule->status),
            'durationMinutes' => $this->durationMinutes($schedule),
        ];
    }

    private function startsAt(WorkSchedule $schedule, string $timezone): CarbonImmutable
    {
        return CarbonImmutable::parse(
            $schedule->schedule_date->toDateString().' '.substr($schedule->start_time, 0, 8),
            $timezone,
        );
    }

    private function timeRange(WorkSchedule $schedule): string
    {
        return substr($schedule->start_time, 0, 5).' - '.substr($schedule->end_time, 0, 5);
    }

    private function durationMinutes(WorkSchedule $schedule): int
    {
        [$startHours, $startMinutes] = array_map('intval', explode(':', substr($schedule->start_time, 0, 5)));
        [$endHours, $endMinutes] = array_map('intval', explode(':', substr($schedule->end_time, 0, 5)));

        return max(0, (($endHours * 60) + $endMinutes) - (($startHours * 60) + $startMinutes));
    }

    private function formatDuration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $remainingMinutes === 0 ? "{$hours}h" : "{$hours}h {$remainingMinutes}m";
    }
}
