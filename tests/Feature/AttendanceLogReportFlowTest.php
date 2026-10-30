<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceLogReportFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_attendance_log_page_uses_only_the_authenticated_users_saved_records(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->createAttendance($user, '2026-10-30', '08:30', '17:00', 'present', 'Main Office');
        $this->createAttendance($otherUser, '2026-10-30', '07:00', '16:00', 'present', 'Private Office');

        $response = $this->actingAs($user)->get(route('attendance.logs'));

        $response->assertOk()
            ->assertSee('window.smartAttendanceLogData', false)
            ->assertSee('Main Office')
            ->assertDontSee('Private Office')
            ->assertSee('id="openImportModal"', false)
            ->assertSee('Drag and drop your CSV file here')
            ->assertSee('window.attendanceExportUrl', false);
    }

    public function test_attendance_log_csv_export_applies_filters_and_is_scoped_to_the_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->createAttendance($user, '2026-10-29', '08:30', '17:00', 'present', "=HYPERLINK('bad')");
        $this->createAttendance($user, '2026-10-30', '09:00', '17:00', 'late', 'Late Office');
        $this->createAttendance($otherUser, '2026-10-29', '07:00', '16:00', 'present', 'Other User Office');

        $response = $this->actingAs($user)->get(route('attendance.logs.export', [
            'start_date' => '2026-10-29',
            'end_date' => '2026-10-29',
            'status' => 'present',
        ]));

        $response->assertOk()->assertDownload('attendance-log-'.today()->toDateString().'.csv');
        $csv = $response->streamedContent();
        $this->assertStringContainsString("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString("'=HYPERLINK(", $csv);
        $this->assertStringNotContainsString('Late Office', $csv);
        $this->assertStringNotContainsString('Other User Office', $csv);
        $this->assertStringContainsString('break_start_at', $csv);
    }

    public function test_report_summarizes_present_late_absent_and_completed_hours_for_the_selected_period(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-31 12:00:00', config('app.timezone')));
        $user = User::factory()->create();
        foreach (['2026-10-29', '2026-10-30'] as $date) {
            $user->schedules()->create([
                'schedule_date' => $date,
                'type' => 'Workshift',
                'start_time' => '08:30',
                'end_time' => '17:30',
                'location' => 'Office',
            ]);
        }
        $this->createAttendance($user, '2026-10-29', '08:30', '17:30', 'present');
        $this->createAttendance($user, '2026-10-30', '08:45', '18:15', 'late');

        $response = $this->actingAs($user)->get(route('reports.index', [
            'period' => 'Custom Date Range',
            'start_date' => '2026-10-28',
            'end_date' => '2026-10-30',
        ]));

        $response->assertOk()->assertSee('Attendance by Day')->assertSee('window.smartReportData', false);
        $report = $response->viewData('reportData');
        $this->assertSame(1, $report['summary']['present']);
        $this->assertSame(1, $report['summary']['late']);
        $this->assertSame(1, $report['summary']['absent']);
        $this->assertSame(3, $report['summary']['expected_workdays']);
        $this->assertSame('66.7%', $report['summary']['attendance_rate']);
        $this->assertSame('50.0%', $report['summary']['punctuality_rate']);
        $this->assertSame('18h 30m', $report['summary']['work_hours']);
        $this->assertSame('0h 30m', $report['summary']['overtime_hours']);
    }

    public function test_report_csv_contains_the_same_selected_period_summary_as_the_page(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-02 12:00:00', config('app.timezone')));
        $user = User::factory()->create();
        $this->createAttendance($user, '2026-10-29', '08:30', '17:30', 'present');

        $response = $this->actingAs($user)->get(route('reports.export', [
            'period' => 'Custom Date Range',
            'start_date' => '2026-10-29',
            'end_date' => '2026-10-30',
        ]));

        $response->assertOk()->assertDownload('attendance-report-2026-10-29-2026-10-30.csv');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Attendance Report', $csv);
        $this->assertStringContainsString('"Total present",1', $csv);
        $this->assertStringContainsString('attendance_date,is_workday,status', $csv);
        $this->assertStringContainsString('2026-10-29', $csv);
        $this->assertStringContainsString('2026-10-30,Yes,absent', $csv);
    }

    public function test_attendance_csv_import_adds_valid_rows_skips_duplicates_and_returns_to_the_log(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'attendance.csv',
            "attendance_date,check_in_at,check_out_at\n2026-10-29,2026-10-29 08:00,2026-10-29 17:00\n2026-10-29,2026-10-29 08:00,2026-10-29 17:00\n",
        );

        $this->actingAs($user)
            ->post(route('attendance.import'), ['file' => $file, 'return_to' => 'attendance.logs'])
            ->assertRedirect(route('attendance.logs'))
            ->assertSessionHas('attendanceImportSummary.imported', 1)
            ->assertSessionHas('attendanceImportSummary.skipped', 1);

        $this->assertDatabaseCount('attendance_records', 1);
        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'attendance_date' => '2026-10-29',
            'status' => 'present',
        ]);

        $this->actingAs($user)->get(route('attendance.logs'))->assertSee('CSV import selesai');
    }

    public function test_attendance_csv_import_rejects_an_incompatible_header_without_creating_records(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('wrong.csv', "date,clock_in,clock_out\n2026-10-29,08:00,17:00\n");

        $this->actingAs($user)
            ->from(route('attendance.logs'))
            ->post(route('attendance.import'), ['file' => $file, 'return_to' => 'attendance.logs'])
            ->assertRedirect(route('attendance.logs'))
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('attendance_records', 0);
    }

    private function createAttendance(
        User $user,
        string $date,
        string $checkIn,
        ?string $checkOut,
        string $status,
        ?string $location = 'Office',
    ): AttendanceRecord {
        return $user->attendanceRecords()->create([
            'attendance_date' => $date,
            'check_in_at' => $date.' '.$checkIn,
            'check_out_at' => $checkOut === null ? null : $date.' '.$checkOut,
            'status' => $status,
            'location' => $location,
            'ip_address' => '8.8.8.8',
            'latitude' => -6.2,
            'longitude' => 106.8,
        ]);
    }
}
