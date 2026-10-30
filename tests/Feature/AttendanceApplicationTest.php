<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\PermissionRequest;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_signs_the_user_in_and_protects_app_pages(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test Employee',
            'email' => 'employee@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'employee@example.test']);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_user_can_log_out_and_log_back_in(): void
    {
        $user = User::factory()->create(['password' => 'password123']);
        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password123'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_employee_can_create_leave_permission_and_schedule_records(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('leave.store'), [
            'leave_type' => 'Annual Leave', 'start_date' => '2026-10-01', 'end_date' => '2026-10-02', 'reason' => 'Family event',
        ])->assertRedirect(route('leave.index'));
        $this->assertDatabaseHas('leave_requests', ['user_id' => $user->id, 'leave_type' => 'Annual Leave']);

        $this->post(route('permissions.store'), [
            'permission_type' => 'WFH', 'permission_date' => '2026-10-03', 'start_time' => '09:00', 'end_time' => '17:00', 'reason' => 'Remote work',
        ])->assertRedirect(route('permissions.index'));
        $this->assertDatabaseHas('permission_requests', ['user_id' => $user->id, 'permission_type' => 'WFH']);

        $this->post(route('schedules.store'), [
            'name' => 'Weekend Shift', 'schedule_date' => '2026-10-04', 'type' => 'Workshift', 'start_time' => '07:00', 'end_time' => '15:00', 'location' => 'Office A',
        ])->assertRedirect(route('schedules.index'));
        $this->assertDatabaseHas('work_schedules', ['user_id' => $user->id, 'location' => 'Office A']);
    }

    public function test_invalid_leave_date_range_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('leave.create'))
            ->post(route('leave.store'), [
                'leave_type' => 'Annual Leave', 'start_date' => '2026-10-05', 'end_date' => '2026-10-02', 'reason' => 'Invalid range',
            ])
            ->assertSessionHasErrors('end_date');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_request_forms_use_csrf_protected_backend_routes(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('leave.create'))
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('action="'.route('leave.store').'"', false);
        $this->get(route('permissions.create'))
            ->assertOk()
            ->assertSee('action="'.route('permissions.store').'"', false);
        $this->get(route('schedules.create'))
            ->assertOk()
            ->assertSee('action="'.route('schedules.store').'"', false);
    }

    public function test_check_in_saves_location_time_and_photo(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        config([
            'attendance.office.latitude' => -6.2,
            'attendance.office.longitude' => 106.816666,
            'attendance.radius_meters' => 50,
            'attendance.gps_warning_accuracy_meters' => 200,
            'attendance.office_allowed_ip' => '8.8.8.8',
        ]);

        $this->actingAs($user)->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->postJson(route('attendance.record'), [
            'action' => 'checkin',
            'latitude' => '-6.2000000',
            'longitude' => '106.8166660',
            'accuracy' => 5,
            'location' => 'Jakarta',
            'photo' => UploadedFile::fake()->image('attendance.png'),
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('allowed', true)
            ->assertJsonPath('action', 'checkin');

        $record = AttendanceRecord::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertNotNull($record->check_in_at);
        $this->assertNotNull($record->check_in_photo_path);
        Storage::disk('local')->assertExists($record->check_in_photo_path);
    }

    public function test_check_in_page_exposes_the_record_endpoint_and_csrf_token(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('attendance.check-in'))
            ->assertSee('<meta name="csrf-token"', false)
            ->assertSee('<meta name="attendance-endpoint" content="'.route('attendance.record').'"', false)
            ->assertDontSee('/attendance/undefined');
    }

    public function test_attendance_calendar_receives_statuses_from_saved_records(): void
    {
        $user = User::factory()->create();
        AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now(),
            'status' => 'present',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.index'))
            ->assertSee('window.smartAttendanceData', false)
            ->assertSee(today()->toDateString(), false)
            ->assertSee('present', false);
    }

    public function test_recent_check_ins_and_last_sixteen_history_records_use_saved_attendance(): void
    {
        $user = User::factory()->create();
        $oldestDate = today()->subDays(17);

        for ($daysAgo = 17; $daysAgo >= 0; $daysAgo--) {
            AttendanceRecord::create([
                'user_id' => $user->id,
                'attendance_date' => today()->subDays($daysAgo)->toDateString(),
                'check_in_at' => today()->subDays($daysAgo)->setTime(8, 0),
                'status' => 'present',
            ]);
        }

        $this->actingAs($user)
            ->get(route('attendance.check-in'))
            ->assertOk()
            ->assertSee('Recent Check-ins')
            ->assertSee(today()->format('d M Y'))
            ->assertDontSee($oldestDate->format('d M Y'));

        $this->get(route('attendance.index'))
            ->assertOk()
            ->assertSee('Last 16 Attendance Records')
            ->assertSee(today()->subDays(15)->format('d M Y'))
            ->assertDontSee(today()->subDays(16)->format('d M Y'))
            ->assertDontSee('10 Jul 2026');
    }

    public function test_attendance_log_uses_fourteen_real_calendar_days_without_validation_overview(): void
    {
        $user = User::factory()->create();
        AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now()->setTime(8, 0),
            'status' => 'present',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.logs'));
        $response->assertOk()
            ->assertSee('Last 14 Days')
            ->assertSee('Activity Trend')
            ->assertDontSee('Validation Overview')
            ->assertDontSee('2026-07-14', false);

        $last14Days = $response->viewData('last14Days');
        $this->assertCount(14, $last14Days);
        $this->assertSame(today()->subDays(13)->toDateString(), $last14Days->first()['date']);
        $this->assertSame(today()->toDateString(), $last14Days->last()['date']);
        $this->assertNull($last14Days->first()['check_in_at']);
        $this->assertSame('08:00', $last14Days->last()['check_in_at']);
    }

    public function test_attendance_logs_receive_check_in_and_check_out_from_saved_records(): void
    {
        $user = User::factory()->create();
        AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now()->setTime(8, 0),
            'check_out_at' => now()->setTime(17, 0),
            'status' => 'present',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.logs'))
            ->assertSee('window.smartAttendanceLogData', false)
            ->assertSee('"event":"Check In"', false)
            ->assertSee('"event":"Check Out"', false)
            ->assertDontSee('2026-07-14', false);
    }

    public function test_check_in_and_check_out_update_the_same_attendance_record(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        config([
            'attendance.office.latitude' => -6.2,
            'attendance.office.longitude' => 106.816666,
            'attendance.radius_meters' => 50,
            'attendance.gps_warning_accuracy_meters' => 200,
            'attendance.office_allowed_ip' => '8.8.8.8',
        ]);

        $this->actingAs($user)->withServerVariables(['REMOTE_ADDR' => '8.8.8.8']);
        $payload = [
            'latitude' => '-6.2000000',
            'longitude' => '106.8166660',
            'accuracy' => 5,
            'location' => 'Jakarta',
        ];

        $checkInResponse = $this->postJson(route('attendance.record'), [
            ...$payload,
            'action' => 'checkin',
            'photo' => UploadedFile::fake()->image('check-in.png'),
        ])->assertOk()->assertJsonPath('action', 'checkin');

        $record = AttendanceRecord::query()->where('user_id', $user->id)->firstOrFail();
        $recordId = $record->id;
        $checkInResponse->assertJsonPath('attendance.id', $recordId);
        $this->assertNotNull($checkInResponse->json('attendance.check_in_at'));

        $checkOutResponse = $this->postJson(route('attendance.record'), [
            ...$payload,
            'action' => 'checkout',
            'photo' => UploadedFile::fake()->image('check-out.png'),
        ])->assertOk()
            ->assertJsonPath('action', 'checkout')
            ->assertJsonPath('attendance.status', 'present')
            ->assertJsonPath('attendance.id', $recordId);
        $this->assertNotNull($checkOutResponse->json('attendance.check_out_at'));

        $updatedRecord = $record->fresh();
        $this->assertSame($recordId, $updatedRecord->id);
        $this->assertNotNull($updatedRecord->check_in_at);
        $this->assertNotNull($updatedRecord->check_out_at);
        $this->assertDatabaseCount('attendance_records', 1);

        $this->get(route('attendance.logs'))
            ->assertSee('"event":"Check In"', false)
            ->assertSee('"event":"Check Out"', false);
    }

    public function test_check_out_closes_an_existing_attendance_record(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        config([
            'attendance.office.latitude' => -6.2,
            'attendance.office.longitude' => 106.816666,
            'attendance.radius_meters' => 50,
            'attendance.gps_warning_accuracy_meters' => 200,
            'attendance.office_allowed_ip' => '8.8.8.8',
        ]);
        AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now()->subHour(),
            'status' => 'present',
        ]);

        $this->actingAs($user)->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->postJson(route('attendance.record'), [
            'action' => 'checkout',
            'latitude' => '-6.2000000',
            'longitude' => '106.8166660',
            'accuracy' => 5,
            'location' => 'Jakarta',
            'photo' => UploadedFile::fake()->image('attendance.png'),
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('allowed', true)
            ->assertJsonPath('action', 'checkout');

        $record = AttendanceRecord::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertNotNull($record->check_out_at);
        $this->assertNotNull($record->check_out_photo_path);
        Storage::disk('local')->assertExists($record->check_out_photo_path);

        $this->postJson(route('attendance.record'), [
            'action' => 'checkout',
            'latitude' => '-6.2000000',
            'longitude' => '106.8166660',
            'accuracy' => 5,
            'location' => 'Jakarta',
            'photo' => UploadedFile::fake()->image('duplicate-check-out.png'),
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'Check-out memerlukan check-in yang belum ditutup.');

        $this->assertDatabaseCount('attendance_records', 1);
    }

    public function test_duplicate_check_in_is_rejected_without_creating_another_record(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        config([
            'attendance.office.latitude' => -6.2,
            'attendance.office.longitude' => 106.816666,
            'attendance.radius_meters' => 50,
            'attendance.gps_warning_accuracy_meters' => 200,
            'attendance.office_allowed_ip' => '8.8.8.8',
        ]);
        $this->actingAs($user)->withServerVariables(['REMOTE_ADDR' => '8.8.8.8']);
        $payload = [
            'action' => 'checkin',
            'latitude' => '-6.2000000',
            'longitude' => '106.8166660',
            'accuracy' => 5,
            'location' => 'Jakarta',
        ];

        $this->postJson(route('attendance.record'), [
            ...$payload,
            'photo' => UploadedFile::fake()->image('first-check-in.png'),
        ])->assertOk();

        $this->postJson(route('attendance.record'), [
            ...$payload,
            'photo' => UploadedFile::fake()->image('duplicate-check-in.png'),
        ])->assertConflict()
            ->assertJsonPath('message', 'Check-in hari ini sudah tersimpan.');

        $this->assertDatabaseCount('attendance_records', 1);
    }

    public function test_new_day_check_in_creates_another_record_and_preserves_previous_day(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $previousDayRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'attendance_date' => today()->subDay()->toDateString(),
            'check_in_at' => now()->subDay()->setTime(8, 0),
            'check_out_at' => now()->subDay()->setTime(17, 0),
            'status' => 'present',
        ]);
        config([
            'attendance.office.latitude' => -6.2,
            'attendance.office.longitude' => 106.816666,
            'attendance.radius_meters' => 50,
            'attendance.gps_warning_accuracy_meters' => 200,
            'attendance.office_allowed_ip' => '8.8.8.8',
        ]);

        $this->actingAs($user)->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->postJson(route('attendance.record'), [
                'action' => 'checkin',
                'latitude' => '-6.2000000',
                'longitude' => '106.8166660',
                'accuracy' => 5,
                'location' => 'Jakarta',
                'photo' => UploadedFile::fake()->image('new-day-check-in.png'),
            ])->assertOk()
            ->assertJsonPath('attendance.attendance_date', today()->toDateString());

        $this->assertDatabaseCount('attendance_records', 2);
        $this->assertDatabaseHas('attendance_records', [
            'id' => $previousDayRecord->id,
        ]);
        $this->assertSame(today()->toDateString(), $user->attendanceRecords()->whereDate('attendance_date', today())->firstOrFail()->attendance_date->toDateString());
    }

    public function test_user_can_update_profile_details(): void
    {
        $user = User::factory()->create();
        Storage::fake('local');
        $oldAvatarPath = 'avatars/old-avatar.png';
        Storage::disk('local')->put($oldAvatarPath, 'old image');
        $user->update(['avatar_path' => $oldAvatarPath]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Employee',
            'nickname' => 'Rel',
            'email' => 'updated@example.test',
            'phone' => '+62 812 3456 7890',
            'job_title' => 'Staff',
            'bio' => 'Attendance team member.',
            'avatar' => UploadedFile::fake()->image('new-avatar.png'),
        ])->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Updated Employee', $user->name);
        $this->assertSame('Rel', $user->nickname);
        $this->assertSame('updated@example.test', $user->email);
        $this->assertSame('+62 812 3456 7890', $user->phone);
        $this->assertSame('Staff', $user->job_title);
        $this->assertSame('Attendance team member.', $user->bio);
        $this->assertNotSame($oldAvatarPath, $user->avatar_path);
        Storage::disk('local')->assertExists($user->avatar_path);
        Storage::disk('local')->assertMissing($oldAvatarPath);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Employee',
            'nickname' => 'Rel',
            'email' => 'updated@example.test',
            'job_title' => 'Staff',
            'avatar_path' => $user->avatar_path,
        ]);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->post(route('login.store'), [
            'email' => 'updated@example.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))
            ->assertSee('Welcome, Rel')
            ->assertSee('Updated Employee');
    }

    public function test_profile_update_without_an_avatar_keeps_the_current_photo(): void
    {
        Storage::fake('local');
        $avatarPath = 'avatars/current-avatar.png';
        Storage::disk('local')->put($avatarPath, 'current image');
        $user = User::factory()->create(['avatar_path' => $avatarPath]);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Name',
            'nickname' => 'New Nickname',
            'email' => $user->email,
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame($avatarPath, $user->fresh()->avatar_path);
        Storage::disk('local')->assertExists($avatarPath);
    }

    public function test_name_and_nickname_can_be_updated_independently(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Full Name',
            'nickname' => 'Original Nickname',
        ]);
        $this->actingAs($user);

        $this->put(route('profile.update'), [
            'name' => 'Updated Full Name',
            'email' => $user->email,
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame('Updated Full Name', $user->fresh()->name);
        $this->assertSame('Original Nickname', $user->fresh()->nickname);

        $this->put(route('profile.update'), [
            'name' => 'Updated Full Name',
            'nickname' => 'Updated Nickname',
            'email' => $user->email,
        ])->assertRedirect(route('profile.edit'));

        $this->assertSame('Updated Full Name', $user->fresh()->name);
        $this->assertSame('Updated Nickname', $user->fresh()->nickname);
    }

    public function test_profile_update_rejects_duplicate_email_without_saving_other_fields(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);
        User::factory()->create(['email' => 'taken@example.test']);

        $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'name' => 'Changed Name',
            'nickname' => 'Changed Nickname',
            'email' => 'taken@example.test',
        ])->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('email');

        $this->assertSame('Original Name', $user->fresh()->name);
        $this->assertNull($user->fresh()->nickname);
    }

    public function test_authenticated_pages_use_the_current_users_profile_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Muhammad Farrel',
            'nickname' => 'Rel',
            'job_title' => 'Product Intern',
        ]);
        $this->actingAs($user);

        foreach ([
            'dashboard',
            'attendance.index',
            'attendance.check-in',
            'attendance.logs',
            'leave.index',
            'permissions.index',
            'schedules.index',
            'reports.index',
            'profile.edit',
        ] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('Welcome, Rel')
                ->assertSee('Muhammad Farrel')
                ->assertSee('Product Intern')
                ->assertSee(route('profile.avatar'), false)
                ->assertDontSee('Farrel Zaidan')
                ->assertDontSee('pinimg.com');
        }

        $profileResponse = $this->get(route('profile.edit'));
        $profileResponse->assertSee('57%');
        $this->assertSame(57, $profileResponse->viewData('profileCompleteness'));
    }

    public function test_welcome_uses_the_full_name_when_nickname_is_empty(): void
    {
        $user = User::factory()->create([
            'name' => 'Full Name Fallback',
            'nickname' => null,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Welcome, Full Name Fallback');
    }

    public function test_profile_avatar_is_served_from_the_authenticated_users_private_storage(): void
    {
        Storage::fake('local');
        $avatarPath = 'avatars/current-avatar.png';
        Storage::disk('local')->put($avatarPath, 'stored image bytes');
        $user = User::factory()->create(['avatar_path' => $avatarPath]);

        $response = $this->actingAs($user)
            ->get(route('profile.avatar'))
            ->assertOk();

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_profile_avatar_requires_authentication(): void
    {
        $this->get(route('profile.avatar'))->assertRedirect(route('login'));
    }

    public function test_authenticated_feature_pages_render(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        LeaveRequest::create(['user_id' => $user->id, 'leave_type' => 'Annual Leave', 'start_date' => '2026-10-01', 'end_date' => '2026-10-02', 'reason' => 'Family event']);
        PermissionRequest::create(['user_id' => $user->id, 'permission_type' => 'WFH', 'permission_date' => '2026-10-03', 'start_time' => '09:00', 'end_time' => '17:00', 'reason' => 'Remote work']);
        WorkSchedule::create(['user_id' => $user->id, 'schedule_date' => '2026-10-04', 'type' => 'Workshift', 'start_time' => '07:00', 'end_time' => '15:00', 'location' => 'Office A']);
        AttendanceRecord::create(['user_id' => $user->id, 'attendance_date' => '2026-10-05', 'check_in_at' => '2026-10-05 08:00:00', 'status' => 'present']);

        foreach (['attendance.index', 'attendance.check-in', 'attendance.logs', 'leave.index', 'leave.create', 'permissions.index', 'permissions.create', 'schedules.index', 'schedules.create', 'reports.index', 'profile.edit'] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }
}
