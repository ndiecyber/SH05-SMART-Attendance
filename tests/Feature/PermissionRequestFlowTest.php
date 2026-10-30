<?php

namespace Tests\Feature;

use App\Models\PermissionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_pending_permission_request(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('permissions.store'), [
                'permission_type' => 'WFH',
                'permission_date' => '2026-10-30',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'reason' => 'Remote work due to home maintenance.',
            ])
            ->assertRedirect(route('permissions.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('permission_requests', [
            'user_id' => $user->id,
            'permission_type' => 'WFH',
            'permission_date' => '2026-10-30',
            'status' => 'pending',
        ]);
    }

    public function test_permission_form_is_connected_to_store_route_and_validates_time_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('permissions.create'))
            ->assertOk()
            ->assertSee('action="'.route('permissions.store').'"', false)
            ->assertSee('name="permission_type"', false)
            ->assertSee('name="permission_date"', false)
            ->assertSee('name="start_time"', false)
            ->assertSee('name="end_time"', false)
            ->assertSee('name="reason"', false);

        $this->from(route('permissions.create'))
            ->post(route('permissions.store'), [
                'permission_type' => 'WFH',
                'permission_date' => '2026-10-30',
                'start_time' => '09:00',
                'end_time' => '09:00',
                'reason' => 'Invalid same-time range.',
            ])
            ->assertRedirect(route('permissions.create'))
            ->assertSessionHasErrors('end_time');

        $this->assertDatabaseCount('permission_requests', 0);
    }

    public function test_permission_history_is_scoped_to_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownRequest = $this->createPermissionRequest($user, ['reason' => 'My own request']);
        $this->createPermissionRequest($otherUser, ['reason' => 'Private other request']);

        $response = $this->actingAs($user)->get(route('permissions.index'));

        $response->assertOk()
            ->assertSee('My own request')
            ->assertDontSee('Private other request')
            ->assertSee('No team members assigned yet.')
            ->assertDontSee('Khansa Putri')
            ->assertDontSee('Ajib Pratama');

        $this->assertSame([$ownRequest->id], $response->viewData('permissionRequests')->pluck('id')->all());
    }

    public function test_owner_can_cancel_a_pending_request_and_record_remains_in_history(): void
    {
        $user = User::factory()->create();
        $permissionRequest = $this->createPermissionRequest($user);

        $this->actingAs($user)
            ->patch(route('permissions.cancel', $permissionRequest))
            ->assertRedirect(route('permissions.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('permission_requests', [
            'id' => $permissionRequest->id,
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);

        $this->actingAs($user)
            ->get(route('permissions.index'))
            ->assertOk()
            ->assertSee('Cancelled');
    }

    public function test_user_cannot_cancel_another_users_request(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $permissionRequest = $this->createPermissionRequest($owner);

        $this->actingAs($otherUser)
            ->patch(route('permissions.cancel', $permissionRequest))
            ->assertForbidden();

        $this->assertDatabaseHas('permission_requests', [
            'id' => $permissionRequest->id,
            'status' => 'pending',
        ]);
    }

    public function test_only_pending_permission_requests_can_be_cancelled(): void
    {
        $user = User::factory()->create();
        $permissionRequest = $this->createPermissionRequest($user, ['status' => 'approved']);

        $this->actingAs($user)
            ->patch(route('permissions.cancel', $permissionRequest))
            ->assertStatus(422);

        $this->assertDatabaseHas('permission_requests', [
            'id' => $permissionRequest->id,
            'status' => 'approved',
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function createPermissionRequest(User $user, array $attributes = []): PermissionRequest
    {
        return $user->permissionRequests()->create(array_merge([
            'permission_type' => 'WFH',
            'permission_date' => '2026-10-30',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'reason' => 'Permission request reason.',
            'status' => 'pending',
        ], $attributes));
    }
}
