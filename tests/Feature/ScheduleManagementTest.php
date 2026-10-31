<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_can_view_assigned_schedule_but_cannot_open_schedule_editor(): void
    {
        $worker = User::factory()->create();
        $worker->schedules()->create([
            'name' => 'Morning Shift', 'schedule_date' => today()->addDay(), 'type' => 'Workshift',
            'start_time' => '08:00', 'end_time' => '17:00', 'location' => 'LEXA Office',
        ]);

        $this->actingAs($worker)->get(route('schedules.index'))
            ->assertOk()
            ->assertSee('Morning Shift')
            ->assertSee('Schedules assigned by your Admin')
            ->assertDontSee('Add Schedule');
        $this->get('/schedules/create')->assertNotFound();
        $this->post('/schedules', [])->assertMethodNotAllowed();
    }

    public function test_admin_can_assign_schedule_to_worker_and_worker_sees_it(): void
    {
        $worker = User::factory()->create();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)->post(route('admin.workers.schedules.store', $worker), [
            'name' => 'Evening Shift', 'schedule_date' => today()->addDay()->toDateString(), 'type' => 'Workshift',
            'start_time' => '12:00', 'end_time' => '20:00', 'location' => 'LEXA Office',
        ])->assertRedirect();

        $this->assertDatabaseHas('work_schedules', ['user_id' => $worker->id, 'name' => 'Evening Shift']);
        $this->actingAs($worker)->get(route('schedules.index'))->assertOk()->assertSee('Evening Shift');
    }
}
