<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_empty_attendance_history_shows_empty_statistics(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00', 'Asia/Jakarta');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('attendance.check-in'))
            ->assertOk()
            ->assertViewHas('attendanceStatistics', [
                'current_streak' => '0 Days',
                'on_time_rate' => '0%',
                'average_check_in' => '--:--',
                'weekly_work_hours' => '0h 0m',
            ]);
    }

    public function test_statistics_use_only_the_authenticated_users_records_and_matching_workshifts(): void
    {
        Carbon::setTestNow('2026-10-14 10:00:00', 'Asia/Jakarta');
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        foreach ([
            ['2026-10-12', '07:55:00', '17:00:00'],
            ['2026-10-13', '08:10:00', '17:00:00'],
            ['2026-10-14', '08:00:00', null],
        ] as [$date, $checkIn, $checkOut]) {
            $this->createAttendance($user, $date, $checkIn, $checkOut);
            $this->createWorkshift($user, $date, '08:00:00');
        }

        $this->createAttendance($otherUser, '2026-10-14', '08:30:00');
        $this->createWorkshift($otherUser, '2026-10-14', '08:00:00');

        $this->actingAs($user)
            ->get(route('attendance.check-in'))
            ->assertOk()
            ->assertViewHas('attendanceStatistics', [
                'current_streak' => '3 Days',
                'on_time_rate' => '67%',
                'average_check_in' => '08:02',
                'weekly_work_hours' => '17h 55m',
            ]);

        $this->actingAs($otherUser)
            ->get(route('attendance.check-in'))
            ->assertViewHas('attendanceStatistics', [
                'current_streak' => '1 Day',
                'on_time_rate' => '0%',
                'average_check_in' => '08:30',
                'weekly_work_hours' => '0h 0m',
            ]);
    }

    public function test_streak_skips_weekends_without_a_schedule_and_counts_scheduled_weekend_attendance(): void
    {
        Carbon::setTestNow('2026-10-19 10:00:00', 'Asia/Jakarta');
        $user = User::factory()->create();
        $this->createAttendance($user, '2026-10-16', '08:00:00');
        $this->createAttendance($user, '2026-10-18', '08:00:00');
        $this->createAttendance($user, '2026-10-19', '08:00:00');
        $this->createWorkshift($user, '2026-10-18', '08:00:00');

        $this->actingAs($user)
            ->get(route('attendance.check-in'))
            ->assertViewHas('attendanceStatistics.current_streak', '3 Days');
    }

    public function test_average_handles_midnight_and_reset_actions_refresh_statistics(): void
    {
        Carbon::setTestNow('2026-10-02 10:00:00', 'Asia/Jakarta');
        $user = User::factory()->create();
        $this->createAttendance($user, '2026-10-01', '23:55:00');
        $todayRecord = $this->createAttendance($user, '2026-10-02', '00:05:00', '08:05:00');

        $this->actingAs($user)
            ->get(route('attendance.check-in'))
            ->assertViewHas('attendanceStatistics.average_check_in', '00:00')
            ->assertViewHas('attendanceStatistics.weekly_work_hours', '8h 0m');

        $this->delete(route('attendance.reset-check-out'))->assertRedirect(route('attendance.check-in'));
        $this->get(route('attendance.check-in'))
            ->assertViewHas('attendanceStatistics.weekly_work_hours', '0h 0m')
            ->assertViewHas('todayRecord', fn (?AttendanceRecord $record): bool => $record?->check_in_at !== null && $record->check_out_at === null);

        $this->delete(route('attendance.reset-check-in'))->assertRedirect(route('attendance.check-in'));
        $this->assertDatabaseMissing('attendance_records', ['id' => $todayRecord->id]);
        $this->get(route('attendance.check-in'))
            ->assertViewHas('attendanceStatistics.weekly_work_hours', '0h 0m');
    }

    private function createAttendance(User $user, string $date, string $checkIn, ?string $checkOut = null): AttendanceRecord
    {
        return AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => $date,
            'check_in_at' => $date.' '.$checkIn,
            'check_out_at' => $checkOut === null ? null : $date.' '.$checkOut,
            'status' => 'present',
        ]);
    }

    private function createWorkshift(User $user, string $date, string $startTime): WorkSchedule
    {
        return WorkSchedule::create([
            'user_id' => $user->id,
            'schedule_date' => $date,
            'type' => 'Workshift',
            'start_time' => $startTime,
            'end_time' => '17:00:00',
            'location' => 'Office',
        ]);
    }
}
