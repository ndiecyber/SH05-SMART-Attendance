<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminRoleSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_registration_defaults_to_worker_and_admin_role_needs_invitation(): void
    {
        $this->post(route('register.store'), [
            'name' => 'New Worker', 'email' => 'worker@example.test', 'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'worker@example.test', 'role' => 'worker']);

        auth()->logout();
        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Escalation Attempt', 'email' => 'escalate@example.test', 'password' => 'password123',
            'password_confirmation' => 'password123', 'role' => 'admin', 'admin_invitation' => 'untrusted-code',
        ])->assertRedirect(route('register'))->assertSessionHasErrors('admin_invitation');
        $this->assertDatabaseMissing('users', ['email' => 'escalate@example.test']);
    }

    public function test_admin_invitation_is_hashed_email_bound_and_consumed_once(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $this->assertSame('admin', $admin->fresh()->role);
        $this->assertTrue((bool) $admin->fresh()->is_active);
        $this->actingAs($admin)->from(route('admin.workers.index'))
            ->post(route('admin.workers.invitations.store'), ['email' => 'invited@example.test', 'valid_hours' => 24])
            ->assertRedirect(route('admin.workers.index'));

        $token = session('admin_invitation_token');
        $this->assertIsString($token);
        $this->assertDatabaseHas('admin_invitations', ['token_hash' => hash('sha256', $token), 'email' => 'invited@example.test', 'used_at' => null]);
        $this->assertDatabaseMissing('admin_invitations', ['token_hash' => $token]);

        auth()->logout();
        $this->post(route('register.store'), [
            'name' => 'Invited Admin', 'email' => 'invited@example.test', 'password' => 'password123',
            'password_confirmation' => 'password123', 'role' => 'admin', 'admin_invitation' => $token,
        ])->assertRedirect(route('admin.dashboard'));
        $newAdmin = User::query()->where('email', 'invited@example.test')->firstOrFail();
        $this->assertSame('admin', $newAdmin->role);
        $this->assertDatabaseHas('admin_invitations', ['used_by' => $newAdmin->id]);

        auth()->logout();
        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Second Admin', 'email' => 'second@example.test', 'password' => 'password123',
            'password_confirmation' => 'password123', 'role' => 'admin', 'admin_invitation' => $token,
        ])->assertRedirect(route('register'))->assertSessionHasErrors('admin_invitation');
    }

    public function test_worker_is_forbidden_from_admin_routes_and_admin_is_forbidden_from_worker_routes(): void
    {
        $worker = User::factory()->create();
        $this->actingAs($worker)->get(route('admin.workers.index'))->assertForbidden();

        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin)->get(route('attendance.check-in'))->assertForbidden();
        $this->get(route('schedules.index'))->assertForbidden();
        $this->get(route('reports.index'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.workers.index'))->assertOk()->assertSee('Worker Management');
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['password' => 'password123']);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password123'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_assign_a_schedule_to_worker_but_worker_cannot_manage_schedules(): void
    {
        $worker = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($worker)->get('/schedules/create')->assertNotFound();
        $this->actingAs($admin)->post(route('admin.workers.schedules.store', $worker), [
            'name' => 'Morning Shift', 'schedule_date' => today()->addDay()->toDateString(), 'type' => 'Workshift',
            'start_time' => '08:00', 'end_time' => '17:00', 'location' => 'LEXA Office',
        ])->assertRedirect();

        $this->assertDatabaseHas('work_schedules', ['user_id' => $worker->id, 'name' => 'Morning Shift']);
        $this->actingAs($admin)->get(route('admin.workers.show', $worker))->assertOk()->assertSee('Edit or Remove Assigned Schedules');
        $this->actingAs($worker)->get(route('schedules.index'))->assertOk()->assertDontSee('Add Schedule');
    }

    public function test_admin_can_bulk_assign_schedule_to_active_workers_only(): void
    {
        Notification::fake();
        $workers = User::factory()->count(2)->create();
        $inactiveWorker = User::factory()->create(['is_active' => false]);
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $payload = [
            'worker_ids' => $workers->modelKeys(),
            'name' => 'Team Training',
            'schedule_date' => today()->addDay()->toDateString(),
            'type' => 'Training',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'location' => 'LEXA Office',
        ];

        $this->actingAs($admin)->post(route('admin.schedules.bulk.store'), $payload)
            ->assertRedirect()->assertSessionHas('success');
        foreach ($workers as $worker) {
            $this->assertDatabaseHas('work_schedules', ['user_id' => $worker->id, 'name' => 'Team Training']);
        }
        $this->assertDatabaseCount('work_schedules', 2);

        $this->post(route('admin.schedules.bulk.store'), array_merge($payload, ['worker_ids' => [$inactiveWorker->id]]))
            ->assertUnprocessable();
        $this->actingAs($workers->first())->post(route('admin.schedules.bulk.store'), $payload)->assertForbidden();
    }

    public function test_admin_approval_records_reviewer_note_and_notifies_worker(): void
    {
        Notification::fake();
        $worker = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $leave = LeaveRequest::query()->create([
            'user_id' => $worker->id, 'leave_type' => 'Annual Leave', 'start_date' => today()->addDays(3),
            'end_date' => today()->addDays(4), 'reason' => 'Family matter',
        ]);

        $this->actingAs($admin)->post(route('admin.leave-requests.decision', $leave), [
            'decision' => 'rejected', 'review_note' => 'Staffing coverage is not available.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id, 'status' => 'rejected', 'reviewed_by' => $admin->id,
            'review_note' => 'Staffing coverage is not available.',
        ]);
        Notification::assertSentTo($worker, ActivityNotification::class);
    }

    public function test_worker_cannot_decide_leave_request(): void
    {
        $worker = User::factory()->create();
        $leave = LeaveRequest::query()->create([
            'user_id' => $worker->id, 'leave_type' => 'Annual Leave', 'start_date' => today()->addDays(3),
            'end_date' => today()->addDays(4), 'reason' => 'Family matter',
        ]);

        $this->actingAs($worker)->post(route('admin.leave-requests.decision', $leave), ['decision' => 'approved'])->assertForbidden();
        $this->assertSame('pending', $leave->fresh()->status);
    }

    public function test_admin_attendance_settings_are_saved_to_database_and_worker_cannot_change_them(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $payload = [
            'location_name' => 'LEXA HQ', 'office_latitude' => '-6.2000000', 'office_longitude' => '106.8166667',
            'office_address' => 'Jakarta', 'radius_meters' => 75, 'allowed_ips' => '8.8.8.8',
            'gps_verification_required' => 1, 'ip_verification_required' => 1,
            'gps_accuracy_threshold_meters' => 80, 'late_grace_period_minutes' => 10,
        ];

        $this->actingAs($admin)->put(route('admin.settings.attendance.update'), $payload)
            ->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('attendance_settings', [
            'location_name' => 'LEXA HQ', 'radius_meters' => 75, 'late_grace_period_minutes' => 10,
            'gps_verification_required' => true, 'ip_verification_required' => true,
        ]);

        $worker = User::factory()->create();
        $this->actingAs($worker)->put(route('admin.settings.attendance.update'), $payload)->assertForbidden();
    }

    public function test_admin_rejection_requires_a_note_and_processed_request_cannot_be_decided_again(): void
    {
        $worker = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $leave = LeaveRequest::query()->create([
            'user_id' => $worker->id, 'leave_type' => 'Annual Leave', 'start_date' => today()->addDays(3),
            'end_date' => today()->addDays(4), 'reason' => 'Family matter',
        ]);
        $this->actingAs($admin)->from(route('admin.leave-requests.index'))
            ->post(route('admin.leave-requests.decision', $leave), ['decision' => 'rejected'])
            ->assertRedirect(route('admin.leave-requests.index'))->assertSessionHasErrors('review_note');
        $this->post(route('admin.leave-requests.decision', $leave), ['decision' => 'approved'])->assertRedirect();
        $this->post(route('admin.leave-requests.decision', $leave), ['decision' => 'rejected', 'review_note' => 'Too late'])
            ->assertConflict();
    }

    public function test_inactive_worker_cannot_access_authenticated_pages(): void
    {
        $worker = User::factory()->create();
        $worker->forceFill(['is_active' => false])->save();

        $this->actingAs($worker)->get(route('dashboard'))->assertForbidden();
    }
}
