<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceLocationValidationTest extends TestCase
{
    use RefreshDatabase;

    private const OFFICE_IP = '8.8.8.8';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'attendance.office.latitude' => 0.0,
            'attendance.office.longitude' => 0.0,
            'attendance.radius_meters' => 50,
            'attendance.gps_warning_accuracy_meters' => 200,
            'attendance.office_allowed_ip' => self::OFFICE_IP,
        ]);

        Storage::fake('local');
    }

    public function test_attendance_is_saved_when_location_is_within_fifty_meters_and_ip_matches(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config([
            'app.env' => 'production',
        ]);
        $this->withoutMiddleware(PreventRequestForgery::class);
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => self::OFFICE_IP])
            ->postJson(route('attendance.record'), $this->attendancePayload());

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('allowed', true)
            ->assertJsonPath('message', 'Absensi berhasil dicatat.')
            ->assertJsonPath('action', 'checkin')
            ->assertJsonPath('gps_valid', true)
            ->assertJsonPath('network_valid', true);

        $record = AttendanceRecord::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame(0.0, (float) $record->distance_from_office);
        $this->assertSame(5.0, (float) $record->location_accuracy);
        $this->assertSame(self::OFFICE_IP, $record->ip_address);
        Storage::disk('local')->assertExists($record->check_in_photo_path);
    }

    public function test_attendance_is_rejected_outside_radius_even_if_ip_matches(): void
    {
        $user = User::factory()->create();
        $payload = $this->attendancePayload();
        $payload['latitude'] = 0.001;
        $payload['accuracy'] = 212;

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => self::OFFICE_IP])
            ->postJson(route('attendance.record'), $payload)
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('gps_valid', false)
            ->assertJsonPath('network_valid', true);

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_attendance_is_rejected_when_ip_does_not_match_even_if_location_is_valid(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])
            ->postJson(route('attendance.record'), $this->attendancePayload())
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('gps_valid', true)
            ->assertJsonPath('network_valid', false);

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_client_cannot_bypass_ip_check_with_a_forwarded_ip_header(): void
    {
        $user = User::factory()->create();

        $this->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])
            ->withHeaders([
                'X-Forwarded-For' => self::OFFICE_IP,
                'X-Real-IP' => self::OFFICE_IP,
            ])
            ->actingAs($user)
            ->postJson(route('attendance.record'), $this->attendancePayload())
            ->assertUnprocessable()
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('network_valid', false);

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_attendance_is_rejected_when_both_ip_and_location_are_invalid(): void
    {
        $user = User::factory()->create();
        $payload = $this->attendancePayload();
        $payload['longitude'] = 0.001;

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])
            ->postJson(route('attendance.record'), $payload)
            ->assertUnprocessable()
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('gps_valid', false)
            ->assertJsonPath('network_valid', false);

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_coordinate_submission_is_required_even_when_a_forged_distance_is_sent(): void
    {
        $user = User::factory()->create();
        $payload = $this->attendancePayload();
        unset($payload['latitude'], $payload['longitude']);
        $payload['distance_from_office'] = 0;

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => self::OFFICE_IP])
            ->postJson(route('attendance.record'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude', 'longitude']);

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_backend_recalculates_distance_and_ignores_forged_client_distance(): void
    {
        $user = User::factory()->create();
        $payload = $this->attendancePayload();
        $payload['latitude'] = 0.001;
        $payload['distance_from_office'] = 0;

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => self::OFFICE_IP])
            ->postJson(route('attendance.record'), $payload)
            ->assertUnprocessable()
            ->assertJsonPath('gps_valid', false)
            ->assertJsonPath('network_valid', true);

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_high_gps_accuracy_is_informational_when_distance_is_within_radius(): void
    {
        $user = User::factory()->create();
        $payload = $this->attendancePayload();
        $payload['longitude'] = 0.0000009;
        $payload['accuracy'] = 212;

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => self::OFFICE_IP])
            ->postJson(route('attendance.record'), $payload)
            ->assertOk()
            ->assertJsonPath('location_accuracy', 212);

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'location_accuracy' => 212,
            'distance_from_office' => 0.1,
        ]);
    }

    public function test_location_inside_radius_is_allowed_with_forty_meter_accuracy(): void
    {
        $user = User::factory()->create();
        $payload = $this->attendancePayload();
        $payload['latitude'] = 0.0003141;
        $payload['accuracy'] = 40;

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => self::OFFICE_IP])
            ->postJson(route('attendance.record'), $payload)
            ->assertOk();

        $this->assertDatabaseCount('attendance_records', 1);
    }

    public function test_attendance_at_the_fifty_meter_boundary_is_allowed(): void
    {
        $user = User::factory()->create();
        $payload = $this->attendancePayload();
        $payload['latitude'] = 0.0004496608;

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => self::OFFICE_IP])
            ->postJson(route('attendance.record'), $payload)
            ->assertOk();

        $this->assertDatabaseCount('attendance_records', 1);
    }

    public function test_attendance_page_displays_location_and_network_validation_statuses(): void
    {
        $this->actingAs(User::factory()->create())
            ->withServerVariables(['REMOTE_ADDR' => self::OFFICE_IP])
            ->get(route('attendance.check-in'))
            ->assertOk()
            ->assertSee('id="locationDistance"', false)
            ->assertSee('id="networkStatus"', false)
            ->assertSee('window.attendanceValidation', false)
            ->assertDontSee('attendanceDebugPanel')
            ->assertSee('grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]', false)
            ->assertSee('Attendance Location')
            ->assertSee('Selfie Verification')
            ->assertSee('Attendance Timeline')
            ->assertSee('class="rounded-3xl bg-white p-6 shadow-sm lg:col-span-2"', false)
            ->assertSee('class="space-y-6"', false)
            ->assertSee('Break Start')
            ->assertSee('Break End');
    }

    public function test_local_environment_hides_debug_panel_and_keeps_attendance_validation_statuses(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        config(['app.env' => 'local']);

        $this->actingAs(User::factory()->create())
            ->withServerVariables(['REMOTE_ADDR' => '192.168.1.11'])
            ->withHeaders([
                'X-Forwarded-For' => '198.51.100.99',
                'X-Real-IP' => self::OFFICE_IP,
            ])
            ->get(route('attendance.check-in'))
            ->assertOk()
            ->assertDontSee('attendanceDebugPanel')
            ->assertDontSee('Debug (local/development)')
            ->assertDontSee('User Latitude:')
            ->assertDontSee('X-Forwarded-For (untrusted):')
            ->assertDontSee('X-Real-IP (untrusted):')
            ->assertSee('id="networkStatus"', false)
            ->assertSee('id="attendanceEligibility"', false)
            ->assertSee('client-public-ip-1')
            ->assertSee('Reset Check In')
            ->assertSee('Reset Check Out');
    }

    public function test_local_private_request_ip_requires_a_matching_browser_public_ip(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        config(['app.env' => 'local']);
        $this->withoutMiddleware(PreventRequestForgery::class);
        $user = User::factory()->create();
        $payload = $this->attendancePayload();
        $payload['client_public_ip'] = '1.1.1.1';

        $response = $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '192.168.1.11'])
            ->postJson(route('attendance.record'), $payload)
            ->assertUnprocessable();

        $response
            ->assertJsonPath('gps_valid', true)
            ->assertJsonPath('network_valid', false)
            ->assertJsonPath('debug.request_ip', '192.168.1.11')
            ->assertJsonPath('debug.detected_public_ip', '1.1.1.1')
            ->assertJsonPath('debug.attendance_status', 'DENIED');
        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_production_ignores_client_supplied_public_ip_and_uses_request_ip(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['app.env' => 'production']);
        $this->withoutMiddleware(PreventRequestForgery::class);
        $user = User::factory()->create();
        $payload = $this->attendancePayload();
        $payload['client_public_ip'] = self::OFFICE_IP;

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '192.168.1.11'])
            ->postJson(route('attendance.record'), $payload)
            ->assertUnprocessable()
            ->assertJsonPath('network_valid', false)
            ->assertJsonPath('message', 'Network tidak valid: public IP request tidak dapat diverifikasi.');

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_production_does_not_render_development_network_debug_panel(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['app.env' => 'production']);

        $this->actingAs(User::factory()->create())
            ->withServerVariables(['REMOTE_ADDR' => self::OFFICE_IP])
            ->get(route('attendance.check-in'))
            ->assertOk()
            ->assertDontSee('attendanceDebugPanel')
            ->assertDontSee('Network Status:')
            ->assertDontSee('Reset Check In')
            ->assertDontSee('Reset Check Out');
    }

    public function test_local_reset_check_in_resets_both_events_and_preserves_other_users_and_history(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        config(['app.env' => 'local']);
        $this->withoutMiddleware(PreventRequestForgery::class);

        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $today = today()->toDateString();
        $yesterday = today()->subDay()->toDateString();

        Storage::disk('local')->put('attendance/today-in.jpg', 'in');
        Storage::disk('local')->put('attendance/today-out.jpg', 'out');
        Storage::disk('local')->put('attendance/yesterday-in.jpg', 'historic');
        $todayRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => $today,
            'check_in_at' => now(),
            'check_out_at' => now(),
            'check_in_photo_path' => 'attendance/today-in.jpg',
            'check_out_photo_path' => 'attendance/today-out.jpg',
            'status' => 'present',
        ]);
        $yesterdayRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => $yesterday,
            'check_in_at' => now()->subDay(),
            'check_in_photo_path' => 'attendance/yesterday-in.jpg',
            'status' => 'present',
        ]);
        $otherRecord = AttendanceRecord::create([
            'user_id' => $otherUser->id,
            'attendance_date' => $today,
            'check_in_at' => now(),
            'status' => 'present',
        ]);

        $this->actingAs($user)
            ->delete(route('attendance.reset-check-in'))
            ->assertRedirect(route('attendance.check-in'))
            ->assertSessionHas('status', 'Check In dan Check Out hari ini berhasil direset.');

        $this->assertDatabaseMissing('attendance_records', ['id' => $todayRecord->id]);
        $this->assertSame(2, AttendanceRecord::count());
        $this->assertDatabaseHas('attendance_records', ['id' => $yesterdayRecord->id]);
        $this->assertDatabaseHas('attendance_records', ['id' => $otherRecord->id]);
        Storage::disk('local')->assertMissing('attendance/today-in.jpg');
        Storage::disk('local')->assertMissing('attendance/today-out.jpg');
        Storage::disk('local')->assertExists('attendance/yesterday-in.jpg');

        $this->actingAs($user)
            ->get(route('attendance.check-in'))
            ->assertOk()
            ->assertSee('Status: Not Checked In')
            ->assertDontSee('Status: Reset Check Out first');
    }

    public function test_reset_buttons_follow_the_current_check_in_and_check_out_state(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        config(['app.env' => 'local']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.check-in'));
        $this->assertSame(true, $this->resetButtonIsDisabled($response->getContent(), 'resetCheckInBtn'));
        $this->assertSame(true, $this->resetButtonIsDisabled($response->getContent(), 'resetCheckOutBtn'));

        $record = AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now(),
            'status' => 'present',
        ]);
        $response = $this->get(route('attendance.check-in'));
        $this->assertSame(false, $this->resetButtonIsDisabled($response->getContent(), 'resetCheckInBtn'));
        $this->assertSame(true, $this->resetButtonIsDisabled($response->getContent(), 'resetCheckOutBtn'));

        $record->check_out_at = now();
        $record->save();
        $response = $this->get(route('attendance.check-in'));
        $this->assertSame(false, $this->resetButtonIsDisabled($response->getContent(), 'resetCheckInBtn'));
        $this->assertSame(false, $this->resetButtonIsDisabled($response->getContent(), 'resetCheckOutBtn'));
    }

    public function test_reset_endpoints_reject_attendance_without_required_events(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        config(['app.env' => 'local']);
        $this->withoutMiddleware(PreventRequestForgery::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete(route('attendance.reset-check-in'))
            ->assertConflict();
        $this->actingAs($user)
            ->delete(route('attendance.reset-check-out'))
            ->assertConflict();

        $record = AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => today()->toDateString(),
            'check_out_at' => now(),
            'status' => 'present',
        ]);

        $this->actingAs($user)
            ->delete(route('attendance.reset-check-in'))
            ->assertConflict();
        $this->actingAs($user)
            ->delete(route('attendance.reset-check-out'))
            ->assertConflict();

        $record->check_in_at = now()->subHour();
        $record->check_out_at = null;
        $record->save();

        $this->actingAs($user)
            ->delete(route('attendance.reset-check-out'))
            ->assertConflict();

        $record->refresh();
        $this->assertNotNull($record->check_in_at);
        $this->assertNull($record->check_out_at);

        $this->actingAs($user)
            ->delete(route('attendance.reset-check-in'))
            ->assertRedirect(route('attendance.check-in'));

        $this->assertDatabaseMissing('attendance_records', ['id' => $record->id]);
    }

    public function test_local_reset_check_out_preserves_check_in_and_only_affects_today_for_current_user(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        config(['app.env' => 'local']);
        $this->withoutMiddleware(PreventRequestForgery::class);

        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Storage::disk('local')->put('attendance/today-in.jpg', 'in');
        Storage::disk('local')->put('attendance/today-out.jpg', 'out');
        $record = AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now()->subHours(8),
            'check_out_at' => now(),
            'check_in_photo_path' => 'attendance/today-in.jpg',
            'check_out_photo_path' => 'attendance/today-out.jpg',
            'status' => 'present',
        ]);
        AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => today()->subDay()->toDateString(),
            'check_in_at' => now()->subDay(),
            'status' => 'present',
        ]);
        $otherRecord = AttendanceRecord::create([
            'user_id' => $otherUser->id,
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now(),
            'status' => 'present',
        ]);

        $this->actingAs($user)
            ->delete(route('attendance.reset-check-out'))
            ->assertRedirect(route('attendance.check-in'))
            ->assertSessionHas('status', 'Check Out hari ini berhasil direset. Data Check In tetap dipertahankan.');

        $record->refresh();
        $this->assertNotNull($record->check_in_at);
        $this->assertNull($record->check_out_at);
        $this->assertNull($record->check_out_photo_path);
        $this->assertDatabaseHas('attendance_records', ['id' => $otherRecord->id]);
        Storage::disk('local')->assertExists('attendance/today-in.jpg');
        Storage::disk('local')->assertMissing('attendance/today-out.jpg');
    }

    public function test_production_resets_are_blocked_and_leave_attendance_untouched(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['app.env' => 'production']);
        $this->withoutMiddleware(PreventRequestForgery::class);

        $user = User::factory()->create();
        $record = AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now(),
            'status' => 'present',
        ]);

        $this->actingAs($user)
            ->delete(route('attendance.reset-check-in'))
            ->assertNotFound();

        $this->actingAs($user)
            ->delete(route('attendance.reset-check-out'))
            ->assertNotFound();

        $this->assertDatabaseHas('attendance_records', ['id' => $record->id]);
    }

    public function test_attendance_is_rejected_until_office_coordinates_are_configured(): void
    {
        config([
            'attendance.office.latitude' => null,
            'attendance.office.longitude' => null,
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => self::OFFICE_IP])
            ->postJson(route('attendance.record'), $this->attendancePayload())
            ->assertServiceUnavailable()
            ->assertJsonPath('message', 'Absensi belum dapat dilakukan. Konfigurasi lokasi atau jaringan kantor belum lengkap.');

        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_localhost_attendance_is_allowed_when_browser_public_ip_matches_office_ip(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        config([
            'app.env' => 'local',
            'attendance.office_allowed_ip' => '103.156.164.22',
        ]);
        $this->withoutMiddleware(PreventRequestForgery::class);
        $user = User::factory()->create();
        $payload = $this->attendancePayload();
        $payload['client_public_ip'] = '103.156.164.22';

        $response = $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->postJson(route('attendance.record'), $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('allowed', true)
            ->assertJsonPath('gps_valid', true)
            ->assertJsonPath('network_valid', true)
            ->assertJsonPath('debug.request_ip', '127.0.0.1')
            ->assertJsonPath('debug.detected_public_ip', '103.156.164.22')
            ->assertJsonPath('debug.attendance_status', 'ALLOWED');
        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'ip_address' => '103.156.164.22',
        ]);
    }

    public function test_localhost_attendance_is_denied_when_browser_public_ip_differs(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        config([
            'app.env' => 'local',
            'attendance.office_allowed_ip' => '103.156.164.22',
        ]);
        $this->withoutMiddleware(PreventRequestForgery::class);
        $user = User::factory()->create();
        $payload = $this->attendancePayload();
        $payload['client_public_ip'] = '1.1.1.1';

        $response = $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->postJson(route('attendance.record'), $payload);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('gps_valid', true)
            ->assertJsonPath('network_valid', false)
            ->assertJsonPath('debug.request_ip', '127.0.0.1')
            ->assertJsonPath('debug.detected_public_ip', '1.1.1.1')
            ->assertJsonPath('debug.attendance_status', 'DENIED');
        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_localhost_attendance_is_denied_when_browser_public_ip_is_missing(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        config([
            'app.env' => 'local',
            'attendance.office_allowed_ip' => '103.156.164.22',
        ]);
        $this->withoutMiddleware(PreventRequestForgery::class);
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->postJson(route('attendance.record'), $this->attendancePayload());

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('gps_valid', true)
            ->assertJsonPath('network_valid', false)
            ->assertJsonPath('network_ip_detected', false)
            ->assertJsonPath('debug.request_ip', '127.0.0.1')
            ->assertJsonPath('debug.detected_public_ip', 'tidak tersedia')
            ->assertJsonPath('debug.attendance_status', 'DENIED');
        $this->assertDatabaseCount('attendance_records', 0);
    }

    public function test_checkout_with_invalid_network_does_not_update_existing_attendance(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $user = User::factory()->create();
        $record = AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now()->subHour(),
            'status' => 'present',
        ]);

        $payload = $this->attendancePayload();
        $payload['action'] = 'checkout';

        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])
            ->postJson(route('attendance.record'), $payload)
            ->assertUnprocessable()
            ->assertJsonPath('allowed', false)
            ->assertJsonPath('network_valid', false);

        $this->assertDatabaseHas('attendance_records', [
            'id' => $record->id,
            'check_out_at' => null,
        ]);
    }

    private function attendancePayload(): array
    {
        return [
            'action' => 'checkin',
            'latitude' => 0,
            'longitude' => 0,
            'accuracy' => 5,
            'location' => 'Test location',
            'photo' => UploadedFile::fake()->image('attendance.png'),
        ];
    }

    private function resetButtonIsDisabled(string $html, string $buttonId): bool
    {
        $pattern = sprintf(
            '/<button(?=[^>]*\bid="%s")(?=[^>]*\sdisabled(?:\s|>))[^>]*>/s',
            preg_quote($buttonId, '/'),
        );

        return preg_match($pattern, $html) === 1;
    }
}
