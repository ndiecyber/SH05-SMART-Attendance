<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_cancel_a_pending_request_without_deleting_its_history(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('leave.store'), [
            'leave_type' => 'Annual Leave',
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-03',
            'reason' => 'Family plans',
        ])->assertRedirect(route('leave.index'))->assertSessionHas('success');

        $leaveRequest = LeaveRequest::query()->where('user_id', $user->id)->sole();
        $this->assertSame('pending', $leaveRequest->status);

        $this->get(route('leave.index'))
            ->assertOk()
            ->assertSee('id="lexaConfirmDialog"', false)
            ->assertSee('id="lexaToastRegion"', false)
            ->assertDontSee('id="cancelLeaveModal"', false);

        $this->patch(route('leave.cancel', $leaveRequest))
            ->assertRedirect(route('leave.index'))
            ->assertSessionHas('success', 'Pengajuan cuti berhasil dibatalkan.');

        $this->assertDatabaseCount('leave_requests', 1);
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'user_id' => $user->id,
            'status' => 'Cancelled',
        ]);

        $response = $this->get(route('leave.index'))
            ->assertOk()
            ->assertSee('Family plans')
            ->assertSee('Cancelled');
        $this->assertSame(0, $response->viewData('leaveStats')['pending']);
        $this->assertSame('Cancelled', $response->viewData('leaveData')->first()['status']);

        $refreshedResponse = $this->get(route('leave.index'));
        $this->assertSame('Cancelled', $refreshedResponse->viewData('leaveData')->first()['status']);
        $this->assertSame(0, $refreshedResponse->viewData('leaveStats')['pending']);
    }

    public function test_user_cannot_cancel_a_leave_request_owned_by_another_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $leaveRequest = $owner->leaveRequests()->create([
            'leave_type' => 'Personal Leave',
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-02',
            'reason' => 'Private request',
        ]);

        $this->actingAs($otherUser)
            ->patch(route('leave.cancel', $leaveRequest))
            ->assertNotFound();

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'user_id' => $owner->id,
            'status' => 'pending',
        ]);
    }

    public function test_only_pending_leave_requests_can_be_cancelled(): void
    {
        $user = User::factory()->create();

        foreach (['approved', 'rejected', 'Cancelled'] as $status) {
            $leaveRequest = $user->leaveRequests()->create([
                'leave_type' => 'Annual Leave',
                'start_date' => '2026-11-02',
                'end_date' => '2026-11-02',
                'reason' => 'Already processed',
                'status' => $status,
            ]);

            $this->actingAs($user)
                ->patch(route('leave.cancel', $leaveRequest))
                ->assertRedirect(route('leave.index'))
                ->assertSessionHas('error');

            $this->assertDatabaseHas('leave_requests', [
                'id' => $leaveRequest->id,
                'status' => $status,
            ]);
        }
    }
}
