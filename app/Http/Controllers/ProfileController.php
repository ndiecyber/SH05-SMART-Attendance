<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $profileUser = $request->user()->load(['emergencyContact', 'preference', 'notificationPreference']);
        $profileFields = [
            $profileUser->name,
            $profileUser->nickname,
            $profileUser->email,
            $profileUser->phone,
            $profileUser->university,
            $profileUser->major,
            $profileUser->student_id,
            $profileUser->division,
            $profileUser->job_title,
            $profileUser->address,
            $profileUser->bio,
            $profileUser->avatar_path,
        ];
        $profileCompleteness = (int) round(
            collect($profileFields)->filter(fn (mixed $value): bool => filled($value))->count() / count($profileFields) * 100,
        );
        $preferences = array_merge(UserPreference::DEFAULT_SETTINGS, $profileUser->preference?->settings ?? []);
        $notificationChannels = array_merge(
            ['in_app' => true, 'email' => false, 'browser' => false],
            $profileUser->notificationPreference?->channels ?? [],
        );
        $notificationCategories = array_merge(
            ['attendance' => true, 'approval' => true, 'schedule' => true, 'system' => true, 'announcement' => true],
            $profileUser->notificationPreference?->categories ?? [],
        );
        $workSchedule = $profileUser->schedules()
            ->whereDate('schedule_date', CarbonImmutable::now($preferences['timezone'])->toDateString())
            ->where('type', 'Workshift')
            ->orderBy('start_time')
            ->first();

        return view('profile.edit', [
            'profileUser' => $profileUser,
            'profileCompleteness' => $profileCompleteness,
            'preferences' => $preferences,
            'notificationChannels' => $notificationChannels,
            'notificationCategories' => $notificationCategories,
            'workSchedule' => $workSchedule,
            'mentor' => null,
            'locationRequirementsEnabled' => is_numeric(config('attendance.office.latitude'))
                && is_numeric(config('attendance.office.longitude')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^\\+?[0-9][0-9\\s().-]{6,38}$/'],
            'university' => ['nullable', 'string', 'max:255'],
            'major' => ['nullable', 'string', 'max:255'],
            'student_id' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'bio' => ['nullable', 'string', 'max:240'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'emergency' => ['sometimes', 'array:name,relationship,primary_phone,alternative_phone'],
            'emergency.name' => ['nullable', 'string', 'max:255'],
            'emergency.relationship' => ['nullable', 'string', 'max:100'],
            'emergency.primary_phone' => ['nullable', 'string', 'max:40', 'regex:/^\\+?[0-9][0-9\\s().-]{6,38}$/'],
            'emergency.alternative_phone' => ['nullable', 'string', 'max:40', 'regex:/^\\+?[0-9][0-9\\s().-]{6,38}$/'],
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            try {
                $avatarPath = $request->file('avatar')->store('avatars', 'local');
            } catch (\Throwable $exception) {
                report($exception);

                return back()->withErrors(['avatar' => 'Foto profil gagal disimpan. Silakan coba kembali.'])->withInput();
            }

            if (! is_string($avatarPath)) {
                return back()->withErrors(['avatar' => 'Foto profil gagal disimpan.'])->withInput();
            }
        }

        $oldAvatarPath = $user->avatar_path;

        try {
            DB::transaction(function () use ($data, $user, $avatarPath): void {
                $profileData = collect($data)->only([
                    'name', 'nickname', 'email', 'phone', 'university', 'major', 'student_id', 'job_title', 'address', 'bio',
                ])->all();

                if ($avatarPath !== null) {
                    $profileData['avatar_path'] = $avatarPath;
                }

                $user->update($profileData);

                if (array_key_exists('emergency', $data)) {
                    $user->emergencyContact()->updateOrCreate([], $data['emergency']);
                }

            });
        } catch (\Throwable $exception) {
            if ($avatarPath !== null) {
                Storage::disk('local')->delete($avatarPath);
            }

            report($exception);

            return back()->withErrors(['settings' => 'Perubahan gagal disimpan. Silakan coba kembali.'])->withInput();
        }

        if ($avatarPath !== null && $oldAvatarPath !== null && $oldAvatarPath !== $avatarPath) {
            Storage::disk('local')->delete($oldAvatarPath);
        }

        $user->refresh();

        return redirect()->route($request->routeIs('settings.update') ? 'settings.index' : 'profile.edit')
            ->with('success', 'Perubahan berhasil disimpan.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'preferences' => ['required', 'array:theme,language,date_format,time_format,timezone,sidebar_collapsed'],
            'preferences.theme' => ['required', Rule::in(['light', 'dark'])],
            'preferences.language' => ['required', Rule::in(['id', 'en'])],
            'preferences.date_format' => ['required', Rule::in(['long', 'short'])],
            'preferences.time_format' => ['required', Rule::in(['24h', '12h'])],
            'preferences.timezone' => ['required', 'timezone'],
            'preferences.sidebar_collapsed' => ['required', 'boolean'],
        ]);
        $preferences = array_merge(UserPreference::DEFAULT_SETTINGS, $data['preferences']);
        $preferences['sidebar_collapsed'] = filter_var($preferences['sidebar_collapsed'], FILTER_VALIDATE_BOOLEAN);
        $request->user()->preference()->updateOrCreate([], ['settings' => $preferences]);

        return redirect()->route('settings.index', ['tab' => 'preferences'])->with('success', 'Preferensi berhasil disimpan.');
    }

    public function updateNotificationPreferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notifications' => ['required', 'array:channels,categories'],
            'notifications.channels' => ['required', 'array:in_app,email,browser'],
            'notifications.channels.in_app' => ['required', 'boolean'],
            'notifications.channels.email' => ['nullable', 'in:0,false'],
            'notifications.channels.browser' => ['nullable', 'in:0,false'],
            'notifications.categories' => ['required', 'array:attendance,approval,schedule,system,announcement'],
            'notifications.categories.attendance' => ['required', 'boolean'],
            'notifications.categories.approval' => ['required', 'boolean'],
            'notifications.categories.schedule' => ['required', 'boolean'],
            'notifications.categories.system' => ['required', 'boolean'],
            'notifications.categories.announcement' => ['required', 'boolean'],
        ]);
        $user = $request->user();
        $channels = array_merge(['in_app' => true, 'email' => false, 'browser' => false], $data['notifications']['channels']);
        $channels['in_app'] = filter_var($channels['in_app'], FILTER_VALIDATE_BOOLEAN);
        $channels['email'] = false;
        $channels['browser'] = false;
        $categories = array_map(
            static fn (mixed $value): bool => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            $data['notifications']['categories'],
        );
        $user->notificationPreference()->updateOrCreate([], ['channels' => $channels, 'categories' => $categories]);

        return redirect()->route('settings.index', ['tab' => 'notifications'])->with('success', 'Preferensi notifikasi berhasil disimpan.');
    }

    public function updateSecurity(Request $request): RedirectResponse
    {
        $passwordFields = $request->only(['current_password', 'new_password', 'new_password_confirmation']);
        if (collect($passwordFields)->every(static fn (mixed $value): bool => blank($value))) {
            return redirect()->route('settings.index', ['tab' => 'security'])->with('success', 'Tidak ada perubahan kata sandi.');
        }

        $data = $request->validate([
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'required_with:current_password', 'confirmed', Password::min(8)],
            'new_password_confirmation' => ['nullable', 'required_with:new_password', 'string'],
        ]);

        if (filled($data['new_password'] ?? null)) {
            $request->user()->forceFill(['password' => Hash::make($data['new_password'])])->save();
        }

        return redirect()->route('settings.index', ['tab' => 'security'])->with('success', 'Pengaturan keamanan berhasil disimpan.');
    }

    public function resetPreferences(Request $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $request->user()->preference()->updateOrCreate([], ['settings' => UserPreference::DEFAULT_SETTINGS]);
            $request->user()->notificationPreference()->updateOrCreate([], [
                'channels' => ['in_app' => true, 'email' => false, 'browser' => false],
                'categories' => ['attendance' => true, 'approval' => true, 'schedule' => true, 'system' => true, 'announcement' => true],
            ]);
        });

        return redirect()->route('settings.index')->with('success', 'Preferensi dikembalikan ke nilai default.');
    }

    public function updateSidebarPreference(Request $request): Response
    {
        $data = $request->validate(['collapsed' => ['required', 'boolean']]);
        $settings = array_merge(
            UserPreference::DEFAULT_SETTINGS,
            $request->user()->preference?->settings ?? [],
            ['sidebar_collapsed' => filter_var($data['collapsed'], FILTER_VALIDATE_BOOLEAN)],
        );

        $request->user()->preference()->updateOrCreate([], ['settings' => $settings]);

        return response()->noContent();
    }

    public function export(Request $request): Response
    {
        $user = $request->user()->loadMissing(['emergencyContact', 'preference', 'notificationPreference']);
        $data = [
            'profile' => $user->only([
                'name', 'nickname', 'email', 'phone', 'university', 'major', 'student_id', 'division', 'job_title', 'address', 'bio', 'avatar_path',
            ]),
            'emergency_contact' => $user->emergencyContact?->only(['name', 'relationship', 'primary_phone', 'alternative_phone']),
            'preferences' => array_merge(UserPreference::DEFAULT_SETTINGS, $user->preference?->settings ?? []),
            'notification_preferences' => [
                'channels' => $user->notificationPreference?->channels,
                'categories' => $user->notificationPreference?->categories,
            ],
        ];

        return response()->streamDownload(function () use ($data): void {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }, 'lexa-account-data-'.$user->id.'.json', ['Content-Type' => 'application/json']);
    }

    public function avatar(Request $request): Response
    {
        $headers = [
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];
        $user = $request->user();

        if ($user->avatar_path !== null && Storage::disk('local')->exists($user->avatar_path)) {
            return Storage::disk('local')->response($user->avatar_path, null, $headers);
        }

        $initials = collect(preg_split('/\\s+/u', trim($user->name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
        $safeInitials = e($initials ?: 'U');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128"><rect width="128" height="128" rx="32" fill="#dbeafe"/><text x="64" y="69" dominant-baseline="middle" text-anchor="middle" fill="#1d4ed8" font-family="Arial,sans-serif" font-size="44" font-weight="700">'.$safeInitials.'</text></svg>';

        return response($svg, 200, $headers + ['Content-Type' => 'image/svg+xml']);
    }
}
