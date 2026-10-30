<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_dashboard_calendar_and_statistics_use_own_attendance_and_shift_data(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00', 'Asia/Jakarta');
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->createWorkshift($user, '2026-10-24', '08:00:00');
        $this->createWorkshift($user, '2026-10-28', '08:00:00');
        $this->createWorkshift($user, '2026-10-29', '08:00:00');
        $this->createAttendance($user, '2026-10-28', '07:55:00', '17:00:00');
        $this->createAttendance($user, '2026-10-29', '08:10:00', '17:00:00');
        $this->createAttendance($otherUser, '2026-10-30', '08:00:00');
        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => 'Annual Leave',
            'start_date' => '2026-10-27',
            'end_date' => '2026-10-27',
            'reason' => 'Approved leave',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->getJson(route('attendance.data', [
                'month' => '2026-10',
                'anchor_date' => '2026-10-30',
                'period' => 'This Month',
            ]))
            ->assertOk()
            ->assertJsonPath('calendar.2026-10-28.status', 'present')
            ->assertJsonPath('calendar.2026-10-28.check_in_at', '07:55')
            ->assertJsonPath('calendar.2026-10-29.status', 'late')
            ->assertJsonPath('calendar.2026-10-29.check_out_at', '17:00')
            ->assertJsonPath('calendar.2026-10-27.status', 'none')
            ->assertJsonPath('calendar.2026-10-24.status', 'absent')
            ->assertJsonPath('calendar.2026-10-30.status', 'none')
            ->assertJsonPath('summary.present', 1)
            ->assertJsonPath('summary.late', 1)
            ->assertJsonPath('summary.absent', 19)
            ->assertJsonPath('summary.rate', '9.5%')
            ->assertJsonPath('trend.has_attendance', true);
    }

    public function test_period_filter_rebuilds_summary_for_week_and_previous_month(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00', 'Asia/Jakarta');
        $user = User::factory()->create();
        $this->createAttendance($user, '2026-10-26', '08:00:00');
        $this->createAttendance($user, '2026-09-30', '08:00:00');

        $this->actingAs($user)
            ->getJson(route('attendance.data', [
                'month' => '2026-10',
                'anchor_date' => '2026-10-30',
                'period' => 'This Week',
            ]))
            ->assertOk()
            ->assertJsonPath('summary.period', 'This Week')
            ->assertJsonPath('summary.present', 1)
            ->assertJsonPath('summary.absent', 3)
            ->assertJsonPath('trend.labels.0', 'Mon');

        $this->actingAs($user)
            ->getJson(route('attendance.data', [
                'month' => '2026-10',
                'anchor_date' => '2026-10-30',
                'period' => 'Last Month',
            ]))
            ->assertOk()
            ->assertJsonPath('summary.period', 'Last Month')
            ->assertJsonPath('summary.present', 1)
            ->assertJsonPath('summary.absent', 21)
            ->assertJsonPath('calendar.2026-10-26.status', 'present');
    }

    public function test_attendance_page_renders_the_latest_sixteen_personal_records(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00', 'Asia/Jakarta');
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        for ($day = 1; $day <= 17; $day++) {
            $date = sprintf('2026-10-%02d', $day);
            $this->createAttendance($user, $date, '08:00:00', '16:00:00');
        }
        $this->createAttendance($otherUser, '2026-10-30', '09:00:00');

        $this->actingAs($user)
            ->get(route('attendance.index'))
            ->assertOk()
            ->assertViewHas('historyRows', function ($historyRows): bool {
                return $historyRows->count() === 16
                    && $historyRows->first()['record']->attendance_date->toDateString() === '2026-10-17'
                    && $historyRows->last()['record']->attendance_date->toDateString() === '2026-10-02';
            })
            ->assertSee('This Month')
            ->assertSee('17 Oct 2026');
    }

    private function createAttendance(User $user, string $date, string $checkIn, ?string $checkOut = null): AttendanceRecord
    {
        return $user->attendanceRecords()->create([
            'attendance_date' => $date,
            'check_in_at' => $date.' '.$checkIn,
            'check_out_at' => $checkOut === null ? null : $date.' '.$checkOut,
            'status' => 'present',
        ]);
    }

    private function createWorkshift(User $user, string $date, string $startTime): WorkSchedule
    {
        return $user->schedules()->create([
            'schedule_date' => $date,
            'type' => 'Workshift',
            'start_time' => $startTime,
            'end_time' => '17:00:00',
            'location' => 'Office',
        ]);
    }
}
