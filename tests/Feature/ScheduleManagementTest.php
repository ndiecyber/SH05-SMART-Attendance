<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_form_posts_to_the_existing_schedule_route_with_required_fields(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('schedules.create'))
            ->assertOk()
            ->assertSee('action="'.route('schedules.store').'"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="schedule_date"', false)
            ->assertSee('name="start_time"', false)
            ->assertSee('name="end_time"', false)
            ->assertSee('name="location"', false)
            ->assertSee('Cancel');

        $this->assertDatabaseCount('work_schedules', 0);
    }

    public function test_authenticated_user_can_create_a_personal_schedule(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('schedules.store'), [
            'name' => 'Morning Shift',
            'schedule_date' => '2026-11-02',
            'type' => 'Workshift',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'location' => 'Office A',
        ])->assertRedirect(route('schedules.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('work_schedules', [
            'user_id' => $user->id,
            'name' => 'Morning Shift',
            'type' => 'Workshift',
            'location' => 'Office A',
        ]);
    }

    public function test_schedule_rejects_an_end_time_that_is_not_after_start_time(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from(route('schedules.create'))->post(route('schedules.store'), [
            'name' => 'Invalid Shift',
            'schedule_date' => '2026-11-02',
            'type' => 'Workshift',
            'start_time' => '17:00',
            'end_time' => '17:00',
            'location' => 'Office A',
        ])->assertRedirect(route('schedules.create'))->assertSessionHasErrors('end_time');

        $this->assertDatabaseCount('work_schedules', 0);
    }

    public function test_owner_can_edit_a_schedule_and_refresh_the_schedule_statistics(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 30)->setTime(10, 0));
        $user = User::factory()->create();
        $schedule = $user->schedules()->create([
            'name' => 'Old Shift',
            'schedule_date' => '2026-10-30',
            'type' => 'Workshift',
            'start_time' => '08:00',
            'end_time' => '12:00',
            'location' => 'Old Office',
        ]);

        $this->actingAs($user)->get(route('schedules.edit', $schedule))
            ->assertOk()
            ->assertSee('Edit Schedule')
            ->assertSee('value="Old Shift"', false)
            ->assertSee('value="Old Office"', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('Cancel');
        $this->assertDatabaseHas('work_schedules', ['id' => $schedule->id, 'name' => 'Old Shift']);

        $this->put(route('schedules.update', $schedule), [
            'name' => 'Updated Shift',
            'schedule_date' => '2026-10-31',
            'type' => 'Workshift',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'location' => 'New Office',
        ])->assertRedirect(route('schedules.index'))->assertSessionHas('success');

        $this->assertDatabaseCount('work_schedules', 1);
        $this->assertDatabaseHas('work_schedules', [
            'id' => $schedule->id,
            'user_id' => $user->id,
            'name' => 'Updated Shift',
            'schedule_date' => '2026-10-31',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'location' => 'New Office',
        ]);

        $response = $this->get(route('schedules.index'))->assertSee('Updated Shift');
        $this->assertSame('2026-10-31', $response->viewData('scheduleData')->first()['date']);
        $this->assertCount(1, $response->viewData('upcomingEvents'));
        $this->assertSame([
            'todayShift' => null,
            'weeklyShifts' => 1,
            'upcomingShifts' => 1,
            'weeklyHours' => '3h',
        ], $response->viewData('scheduleStats'));
    }

    public function test_invalid_schedule_update_keeps_the_saved_record_unchanged(): void
    {
        $user = User::factory()->create();
        $schedule = $user->schedules()->create([
            'name' => 'Original Shift',
            'schedule_date' => '2026-11-02',
            'type' => 'Workshift',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'location' => 'Office A',
        ]);

        $this->actingAs($user)->from(route('schedules.edit', $schedule))
            ->put(route('schedules.update', $schedule), [
                'name' => 'Uncommitted Change',
                'schedule_date' => '2026-11-02',
                'type' => 'Workshift',
                'start_time' => '17:00',
                'end_time' => '17:00',
                'location' => 'Office B',
            ])
            ->assertRedirect(route('schedules.edit', $schedule))
            ->assertSessionHasErrors('end_time');

        $this->assertDatabaseCount('work_schedules', 1);
        $this->assertDatabaseHas('work_schedules', [
            'id' => $schedule->id,
            'name' => 'Original Shift',
            'location' => 'Office A',
        ]);
    }

    public function test_owner_can_delete_a_schedule_after_the_confirmation_ui_is_shown(): void
    {
        $user = User::factory()->create();
        $schedule = $user->schedules()->create([
            'name' => 'Delete Me',
            'schedule_date' => '2026-11-02',
            'type' => 'Meeting',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'location' => 'Room 1',
        ]);

        $this->actingAs($user)->get(route('schedules.index'))
            ->assertOk()
            ->assertSee('Delete Schedule?')
            ->assertSee('This action cannot be undone.')
            ->assertSee('cancelDeleteSchedule')
            ->assertSee('confirmDeleteSchedule')
            ->assertSee('Cancel');
        $this->assertDatabaseHas('work_schedules', ['id' => $schedule->id]);

        $this->delete(route('schedules.destroy', $schedule))
            ->assertRedirect(route('schedules.index'))
            ->assertSessionHas('success', 'Jadwal berhasil dihapus.');

        $this->assertDatabaseMissing('work_schedules', ['id' => $schedule->id]);
        $this->assertDatabaseCount('work_schedules', 0);
        $response = $this->get(route('schedules.index'));
        $this->assertSame([
            'todayShift' => null,
            'weeklyShifts' => 0,
            'upcomingShifts' => 0,
            'weeklyHours' => '0h',
        ], $response->viewData('scheduleStats'));
    }

    public function test_user_cannot_view_update_or_delete_another_users_schedule(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $schedule = $owner->schedules()->create([
            'name' => 'Private Schedule',
            'schedule_date' => '2026-11-02',
            'type' => 'Workshift',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'location' => 'Private Office',
        ]);

        $this->actingAs($otherUser)
            ->get(route('schedules.edit', $schedule))
            ->assertNotFound();

        $this->put(route('schedules.update', $schedule), [
            'name' => 'Stolen Schedule',
            'schedule_date' => '2026-11-02',
            'type' => 'Workshift',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'location' => 'Other Office',
        ])->assertNotFound();

        $this->delete(route('schedules.destroy', $schedule))->assertNotFound();
        $this->assertDatabaseHas('work_schedules', ['id' => $schedule->id, 'name' => 'Private Schedule']);
        $this->assertDatabaseCount('work_schedules', 1);
    }

    public function test_schedule_dashboard_uses_only_the_authenticated_users_schedule_data(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 30)->setTime(10, 0));
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $user->schedules()->create([
            'name' => 'Completed Workshift',
            'schedule_date' => '2026-10-26',
            'type' => 'Workshift',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'location' => 'Office A',
        ]);
        $user->schedules()->create([
            'name' => 'Past Meeting',
            'schedule_date' => '2026-10-30',
            'type' => 'Meeting',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'location' => 'Room 1',
        ]);
        $user->schedules()->create([
            'name' => 'Afternoon Shift',
            'schedule_date' => '2026-10-30',
            'type' => 'Workshift',
            'start_time' => '13:00',
            'end_time' => '17:00',
            'location' => 'Office A',
        ]);
        $user->schedules()->create([
            'name' => 'Sunday Training',
            'schedule_date' => '2026-11-01',
            'type' => 'Training',
            'start_time' => '08:00',
            'end_time' => '09:00',
            'location' => 'Training Room',
        ]);
        $otherUser->schedules()->create([
            'name' => 'Private Schedule',
            'schedule_date' => '2026-10-30',
            'type' => 'Workshift',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'location' => 'Other Office',
        ]);

        $response = $this->actingAs($user)->get(route('schedules.index'))
            ->assertOk()
            ->assertSee('13:00 - 17:00')
            ->assertSee('Afternoon Shift')
            ->assertSee('Sunday Training')
            ->assertDontSee('Private Schedule');

        $this->assertSame([
            'todayShift' => '13:00 - 17:00',
            'weeklyShifts' => 4,
            'upcomingShifts' => 2,
            'weeklyHours' => '15h',
        ], $response->viewData('scheduleStats'));
    }
}
