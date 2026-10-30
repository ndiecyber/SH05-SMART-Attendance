<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceImportTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_csv_import_adds_valid_rows_skips_duplicates_and_keeps_data_user_scoped(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00', 'Asia/Jakarta');
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $existing = $user->attendanceRecords()->create([
            'attendance_date' => '2026-10-28',
            'check_in_at' => '2026-10-28 07:45:00',
            'status' => 'present',
        ]);
        $user->schedules()->create([
            'schedule_date' => '2026-10-29',
            'type' => 'Workshift',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'location' => 'Office',
        ]);
        $csv = "attendance_date,check_in_at,check_out_at\n"
            ."2026-10-29,2026-10-29 08:10:00,2026-10-29 17:00:00\n"
            ."2026-10-28,2026-10-28 09:00:00,2026-10-28 17:00:00\n"
            ."2026-10-29,2026-10-29 08:20:00,2026-10-29 17:00:00\n"
            ."2026-10-27,invalid,\n";

        $this->actingAs($user)
            ->from(route('attendance.index'))
            ->post(route('attendance.import'), [
                'file' => UploadedFile::fake()->createWithContent('attendance.csv', $csv),
            ])
            ->assertRedirect(route('attendance.index'))
            ->assertSessionHas('attendanceImportSummary.imported', 1)
            ->assertSessionHas('attendanceImportSummary.skipped', 3);

        $this->assertDatabaseCount('attendance_records', 2);
        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'attendance_date' => '2026-10-29',
            'check_in_at' => '2026-10-29 08:10:00',
            'status' => 'late',
        ]);
        $this->assertDatabaseHas('attendance_records', [
            'id' => $existing->id,
            'check_in_at' => '2026-10-28 07:45:00',
        ]);
        $this->assertDatabaseMissing('attendance_records', ['user_id' => $otherUser->id]);

        $this->actingAs($user)
            ->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('CSV import selesai: 1 record ditambahkan, 3 dilewati.')
            ->assertSee('29 Oct 2026');
    }

    public function test_csv_import_rejects_unrecognized_headers_without_writing_attendance(): void
    {
        Carbon::setTestNow('2026-10-30 10:00:00', 'Asia/Jakarta');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('attendance.index'))
            ->post(route('attendance.import'), [
                'file' => UploadedFile::fake()->createWithContent(
                    'attendance.csv',
                    "date,checkin,checkout\n2026-10-29,2026-10-29 08:00:00,2026-10-29 17:00:00\n",
                ),
            ])
            ->assertRedirect(route('attendance.index'))
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_import_requires_authentication(): void
    {
        $this->post(route('attendance.import'), [])
            ->assertRedirect(route('login'));
    }
}
