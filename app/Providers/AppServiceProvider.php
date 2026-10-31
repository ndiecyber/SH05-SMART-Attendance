<?php

namespace App\Providers;

use App\Models\UserPreference;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function (ViewInstance $view): void {
            $user = Auth::user();

            if ($user === null) {
                $view->with('globalNavbar', null);
                $view->with('uiPreferences', UserPreference::DEFAULT_SETTINGS);

                return;
            }

            $user->loadMissing(['preference', 'notificationPreference']);
            $preferences = array_merge(UserPreference::DEFAULT_SETTINGS, $user->preference?->settings ?? []);
            $timezone = $preferences['timezone'];
            $today = CarbonImmutable::now($timezone)->toDateString();
            $activeAttendance = $user->attendanceRecords()
                ->whereDate('attendance_date', $today)
                ->whereNotNull('check_in_at')
                ->whereNull('check_out_at')
                ->latest('check_in_at')
                ->first(['check_in_at']);

            $view->with('uiPreferences', $preferences);
            $view->with('globalNavbar', [
                'user' => $user,
                'unreadCount' => $user->unreadNotifications()->where('data->archived', false)->count(),
                'workStartedAt' => $activeAttendance?->check_in_at?->toIso8601String(),
                'timezone' => $timezone,
            ]);
        });
    }
}
