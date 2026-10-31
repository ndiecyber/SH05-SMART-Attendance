<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPreference;
use App\Notifications\ActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_updates_profile_contact_preferences_and_notifications_for_only_the_current_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create(['name' => 'Unchanged User']);

        $this->actingAs($user)->put(route('settings.update'), [
            'name' => 'Updated Account',
            'nickname' => 'Update',
            'email' => 'updated-settings@example.test',
            'phone' => '+62 812 3456 7890',
            'university' => 'Universitas Negeri',
            'major' => 'Informatika',
            'student_id' => 'A-1024',
            'job_title' => 'Developer Intern',
            'address' => 'Semarang',
            'bio' => 'Mengembangkan aplikasi.',
            'emergency' => [
                'name' => 'Emergency Contact',
                'relationship' => 'Sibling',
                'primary_phone' => '+62 812 0000 1111',
                'alternative_phone' => '+62 812 0000 2222',
            ],
        ])->assertRedirect(route('settings.index'))
            ->assertSessionHas('success');

        $this->put(route('settings.preferences.update'), [
            'preferences' => [
                'theme' => 'dark',
                'language' => 'en',
                'date_format' => 'short',
                'time_format' => '12h',
                'timezone' => 'Asia/Makassar',
                'sidebar_collapsed' => '1',
            ],
        ])->assertRedirect(route('settings.index', ['tab' => 'preferences']));

        $this->put(route('settings.notifications.update'), [
            'notifications' => [
                'channels' => ['in_app' => '0', 'email' => '0', 'browser' => '0'],
                'categories' => ['attendance' => '0', 'approval' => '1', 'schedule' => '1', 'system' => '1', 'announcement' => '1'],
            ],
        ])->assertRedirect(route('settings.index', ['tab' => 'notifications']));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Account',
            'nickname' => 'Update',
            'email' => 'updated-settings@example.test',
            'university' => 'Universitas Negeri',
            'student_id' => 'A-1024',
        ]);
        $this->assertDatabaseHas('emergency_contacts', [
            'user_id' => $user->id,
            'name' => 'Emergency Contact',
            'primary_phone' => '+62 812 0000 1111',
        ]);
        $this->assertSame('dark', $user->fresh()->preference->settings['theme']);
        $this->assertSame('Asia/Makassar', $user->fresh()->preference->settings['timezone']);
        $this->assertFalse($user->fresh()->notificationPreference->channels['in_app']);
        $this->assertFalse($user->fresh()->notificationPreference->categories['attendance']);
        $this->assertSame('Unchanged User', $otherUser->fresh()->name);
        $this->assertDatabaseMissing('emergency_contacts', ['user_id' => $otherUser->id]);
    }

    public function test_settings_requires_the_current_password_before_changing_it(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('settings.security.update'), [
            'current_password' => 'incorrect-password',
            'new_password' => 'replacement-password',
            'new_password_confirmation' => 'replacement-password',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_settings_saves_a_new_password_after_current_password_verification(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('settings.security.update'), [
            'current_password' => 'password',
            'new_password' => 'replacement-password',
            'new_password_confirmation' => 'replacement-password',
        ])->assertRedirect(route('settings.index', ['tab' => 'security']));

        $this->assertTrue(Hash::check('replacement-password', $user->fresh()->password));
    }

    public function test_profile_changes_save_without_sending_password_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('settings.update'), [
            'name' => 'Profile Updated',
            'nickname' => 'Profile',
            'email' => $user->email,
            'job_title' => 'QA Intern',
        ])->assertRedirect(route('settings.index'))
            ->assertSessionHas('success');

        $this->assertSame('Profile Updated', $user->fresh()->name);
        $this->assertSame('QA Intern', $user->fresh()->job_title);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_profile_photo_saves_without_sending_password_fields(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('settings.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('profile.png'),
        ])->assertRedirect(route('settings.index'));

        $this->assertNotNull($user->fresh()->avatar_path);
        Storage::disk('local')->assertExists($user->fresh()->avatar_path);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_security_settings_require_complete_matching_password_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->put(route('settings.security.update'), [
            'current_password' => 'password',
        ])->assertSessionHasErrors('new_password');

        $this->put(route('settings.security.update'), [
            'new_password' => 'replacement-password',
            'new_password_confirmation' => 'replacement-password',
        ])->assertSessionHasErrors('current_password');

        $this->put(route('settings.security.update'), [
            'current_password' => 'password',
            'new_password' => 'replacement-password',
            'new_password_confirmation' => 'different-password',
        ])->assertSessionHasErrors('new_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_empty_security_form_does_not_run_password_validation_or_change_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('settings.security.update'), [
            'current_password' => '',
            'new_password' => '',
            'new_password_confirmation' => '',
        ])->assertRedirect(route('settings.index', ['tab' => 'security']))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_settings_pages_use_the_shared_navbar_and_keep_account_export_private(): void
    {
        $user = User::factory()->create(['name' => 'Settings Owner', 'nickname' => 'Owner']);

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Settings Owner')
            ->assertSee('Halo,')
            ->assertSee('data-settings-tab="security"', false)
            ->assertSee('id="lexaConfirmDialog"', false)
            ->assertSee('data-confirm="Kembalikan preferensi tampilan', false)
            ->assertSee('data-settings-tab="preferences"', false)
            ->assertSee('data-settings-tab="attendance"', false)
            ->assertSee('data-settings-tab="notifications"', false)
            ->assertSee('data-settings-tab="privacy"', false);

        $this->get(route('settings.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/json');
    }

    public function test_global_alert_system_is_available_on_request_forms_and_uses_flash_feedback(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('leave.create'))
            ->assertOk()
            ->assertSee('id="lexaToastRegion"', false)
            ->assertSee('id="lexaConfirmDialog"', false);

        $this->get(route('permissions.create'))
            ->assertOk()
            ->assertSee('id="lexaAlertFeedback"', false);

        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee('js/alert-system.js', false)
            ->assertSee('id="lexaConfirmDialog"', false);
    }

    public function test_avatar_fallback_uses_the_authenticated_users_initials(): void
    {
        $user = User::factory()->create(['name' => 'Siti Farah']);

        $this->actingAs($user)
            ->get(route('profile.avatar'))
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml')
            ->assertSee('SF');
    }

    public function test_account_export_requires_authentication(): void
    {
        $this->get(route('settings.export'))->assertRedirect(route('login'));
    }

    public function test_sidebar_preference_is_persisted_and_restored_from_the_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('settings.sidebar-preference.update'), ['collapsed' => true])
            ->assertNoContent();

        $this->assertTrue($user->fresh()->preference->settings['sidebar_collapsed']);
        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee('data-sidebar-collapsed="true"', false);
    }

    public function test_reset_preferences_preserves_profile_attendance_and_notification_history(): void
    {
        $user = User::factory()->create(['name' => 'Keep This Profile']);
        $user->attendanceRecords()->create([
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now(),
            'status' => 'present',
        ]);
        $user->notify(new ActivityNotification(
            title: 'Existing notification',
            description: 'Keep this notification.',
            category: 'System',
            priority: 'normal',
            requiresAction: false,
            url: '/notifications',
            source: 'Test',
        ));

        $user->preference()->create(['settings' => ['theme' => 'dark']]);
        $user->notificationPreference()->create([
            'channels' => ['in_app' => false, 'email' => false, 'browser' => false],
            'categories' => ['attendance' => true, 'approval' => true, 'schedule' => true, 'system' => false, 'announcement' => true],
        ]);

        $this->actingAs($user)->post(route('settings.reset-preferences'))
            ->assertRedirect(route('settings.index'));

        $this->assertSame('Keep This Profile', $user->fresh()->name);
        $this->assertDatabaseHas('attendance_records', ['user_id' => $user->id]);
        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame(UserPreference::DEFAULT_SETTINGS, $user->fresh()->preference->settings);
        $this->assertTrue($user->fresh()->notificationPreference->channels['in_app']);
    }
}
