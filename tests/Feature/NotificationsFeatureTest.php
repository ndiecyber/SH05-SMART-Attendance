<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_page_lists_only_the_authenticated_users_notifications_and_filters_them(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->notify($this->notification('Pengajuan izin terkirim', 'Approval', '/permissions'));
        $otherUser->notify($this->notification('Private notice', 'System', '/'));

        $response = $this->actingAs($user)->get(route('notifications.index', [
            'search' => 'izin',
            'status' => 'unread',
            'category' => 'approval',
        ]));

        $response->assertOk()
            ->assertSee('Pengajuan izin terkirim')
            ->assertDontSee('Private notice');
        $this->assertSame(1, $response->viewData('stats')['unread']);
        $this->assertSame(1, $response->viewData('stats')['active']);
        $this->assertSame(1, $response->viewData('activity')->sum('count'));
    }

    public function test_user_can_read_archive_restore_and_delete_only_owned_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->notify($this->notification('Own notice', 'Attendance', '/attendance/check'));
        $otherUser->notify($this->notification('Private notice', 'System', '/'));
        $owned = $user->notifications()->firstOrFail();
        $foreign = $otherUser->notifications()->firstOrFail();

        $this->actingAs($user)->patch(route('notifications.read', $owned->id))->assertRedirect();
        $this->assertNotNull($owned->fresh()->read_at);

        $this->patch(route('notifications.unread', $owned->id))->assertRedirect();
        $this->assertNull($owned->fresh()->read_at);

        $this->patch(route('notifications.archive', $owned->id))->assertRedirect();
        $this->assertTrue($owned->fresh()->data['archived']);

        $this->patch(route('notifications.restore', $owned->id))->assertRedirect();
        $this->assertFalse($owned->fresh()->data['archived']);

        $this->patch(route('notifications.archive', $foreign->id))->assertNotFound();
        $this->delete(route('notifications.destroy', $foreign->id))->assertNotFound();

        $this->delete(route('notifications.destroy', $owned->id))->assertRedirect();
        $this->assertDatabaseMissing('notifications', ['id' => $owned->id]);
        $this->assertDatabaseHas('notifications', ['id' => $foreign->id]);
    }

    public function test_preferences_are_saved_per_user_and_disabled_categories_stop_new_in_app_notifications(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put(route('notifications.preferences.update'), [
            'channels' => ['in_app' => '1', 'email' => '0', 'browser' => '0'],
            'categories' => ['attendance' => '0', 'approval' => '1', 'schedule' => '1', 'system' => '1', 'announcement' => '1'],
        ])->assertRedirect();

        $user->refresh();
        $this->assertFalse($user->notificationPreference->categories['attendance']);
        $this->assertTrue($user->notificationPreference->categories['approval']);

        $user->notify($this->notification('Filtered attendance event', 'Attendance', '/attendance/check'));
        $user->notify($this->notification('Permission sent', 'Approval', '/permissions'));

        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame('Permission sent', $user->notifications()->firstOrFail()->data['title']);
    }

    public function test_mark_all_read_updates_only_the_users_active_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->notify($this->notification('Current unread', 'Attendance', '/attendance/check'));
        $otherUser->notify($this->notification('Other unread', 'Attendance', '/attendance/check'));
        $own = $user->notifications()->firstOrFail();
        $foreign = $otherUser->notifications()->firstOrFail();

        $this->actingAs($user)->patch(route('notifications.read-all'))->assertRedirect();

        $this->assertNotNull($own->fresh()->read_at);
        $this->assertNull($foreign->fresh()->read_at);
    }

    public function test_permission_request_activity_creates_a_database_notification_for_its_owner(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('permissions.store'), [
            'permission_type' => 'WFH',
            'permission_date' => '2026-11-02',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'reason' => 'Work from home for this date.',
        ])->assertRedirect(route('permissions.index'));

        $notification = $user->notifications()->firstOrFail();
        $this->assertSame('Pengajuan permission berhasil dikirim', $notification->data['title']);
        $this->assertSame('Approval', $notification->data['category']);
        $this->assertSame(route('permissions.index', [], false), $notification->data['url']);
    }

    private function notification(string $title, string $category, string $url): ActivityNotification
    {
        return new ActivityNotification(
            title: $title,
            description: 'Test notification description',
            category: $category,
            priority: 'normal',
            requiresAction: false,
            url: $url,
            source: 'Feature test',
        );
    }
}
