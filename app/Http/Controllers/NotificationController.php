<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NotificationController extends Controller
{
    private const CATEGORIES = [
        'attendance' => 'Attendance',
        'approval' => 'Approval',
        'schedule' => 'Schedule',
        'system' => 'System',
        'announcement' => 'Announcement',
    ];

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()
            ->where('data->archived', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', 'in:all,unread,read,action'],
            'category' => ['nullable', 'in:all,unread,action,attendance,approval,schedule,system,announcement'],
        ]);

        $user = $request->user();
        $timezone = config('app.timezone');
        $now = CarbonImmutable::now($timezone);
        $activeNotifications = $user->notifications()->where('data->archived', false);

        $query = clone $activeNotifications;
        $status = $filters['status'] ?? 'all';
        $categoryFilter = $filters['category'] ?? 'all';

        if ($status === 'unread' || $categoryFilter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        if ($status === 'action' || $categoryFilter === 'action') {
            $query->where('data->requires_action', true);
        }

        if (isset(self::CATEGORIES[$categoryFilter])) {
            $query->where('data->category', self::CATEGORIES[$categoryFilter]);
        }

        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(function ($builder) use ($search): void {
                $builder->where('data->title', 'like', $search)
                    ->orWhere('data->description', 'like', $search)
                    ->orWhere('data->category', 'like', $search)
                    ->orWhere('data->source', 'like', $search);
            });
        }

        $notifications = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $groupedNotifications = $notifications->getCollection()
            ->groupBy(fn (DatabaseNotification $notification): string => $notification->created_at
                ->timezone($timezone)
                ->toDateString())
            ->map(function (Collection $items, string $date) use ($timezone): array {
                $groupDate = CarbonImmutable::parse($date, $timezone);
                $label = $groupDate->isToday()
                    ? 'Hari Ini'
                    : ($groupDate->isYesterday() ? 'Kemarin' : $groupDate->locale('id')->translatedFormat('l, d F Y'));

                return ['date' => $date, 'label' => $label, 'count' => $items->count(), 'items' => $items];
            });

        $weekStart = $now->startOfDay()->subDays(6);
        $weekItems = $user->notifications()
            ->where('created_at', '>=', $weekStart)
            ->where('created_at', '<=', $now)
            ->get(['created_at', 'data']);

        $activity = collect(range(6, 0))->map(function (int $daysAgo) use ($now, $weekItems, $timezone): array {
            $date = $now->startOfDay()->subDays($daysAgo);
            $count = $weekItems->filter(fn (DatabaseNotification $notification): bool => $notification->created_at
                ->timezone($timezone)
                ->isSameDay($date))->count();

            return ['date' => $date->toDateString(), 'label' => $date->locale('id')->translatedFormat('D'), 'count' => $count];
        });

        $categoryCounts = $weekItems->countBy(fn (DatabaseNotification $notification): string => (string) ($notification->data['category'] ?? 'Other'));
        $categorySummary = collect(self::CATEGORIES)->map(function (string $label) use ($categoryCounts, $weekItems): array {
            $count = $categoryCounts->get($label, 0);

            return ['label' => $label, 'count' => $count, 'percent' => $weekItems->isEmpty() ? 0 : (int) round(($count / $weekItems->count()) * 100)];
        });
        $otherCount = $categoryCounts->except(array_values(self::CATEGORIES))->sum();
        $categorySummary->push([
            'label' => 'Lainnya',
            'count' => $otherCount,
            'percent' => $weekItems->isEmpty() ? 0 : (int) round(($otherCount / $weekItems->count()) * 100),
        ]);

        $preferences = $user->notificationPreference;
        $activeCount = (clone $activeNotifications)->count();
        $archivedNotifications = $user->notifications()
            ->where('data->archived', true)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(5, ['*'], 'archived_page')
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'groupedNotifications' => $groupedNotifications,
            'archivedNotifications' => $archivedNotifications,
            'actionNotifications' => (clone $activeNotifications)
                ->where('data->requires_action', true)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
            'importantNotifications' => (clone $activeNotifications)
                ->where('data->priority', 'high')
                ->orderByDesc('created_at')
                ->limit(3)
                ->get(),
            'activity' => $activity,
            'categorySummary' => $categorySummary,
            'preferences' => $preferences,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $status,
                'category' => $categoryFilter,
            ],
            'stats' => [
                'unread' => $user->unreadNotifications()->where('data->archived', false)->count(),
                'action' => (clone $activeNotifications)->where('data->requires_action', true)->count(),
                'today' => $user->notifications()->whereBetween('created_at', [$now->subHours(24), $now])->count(),
                'last30Days' => $user->notifications()->whereBetween('created_at', [$now->subDays(30), $now])->count(),
                'active' => $activeCount,
            ],
        ]);
    }

    public function open(Request $request, string $notificationId): RedirectResponse
    {
        $notification = $this->ownedNotification($request, $notificationId);
        $notification->markAsRead();

        $target = $notification->data['url'] ?? null;
        $path = is_string($target) ? parse_url($target, PHP_URL_PATH) : null;
        $query = is_string($target) ? parse_url($target, PHP_URL_QUERY) : null;

        if (! is_string($path) || ! Str::startsWith($path, '/') || parse_url($target, PHP_URL_SCHEME) !== null || parse_url($target, PHP_URL_HOST) !== null) {
            return redirect()->route('notifications.index');
        }

        return redirect()->to($path.($query ? '?'.$query : ''));
    }

    public function markRead(Request $request, string $notificationId): RedirectResponse
    {
        $this->ownedNotification($request, $notificationId)->markAsRead();

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markUnread(Request $request, string $notificationId): RedirectResponse
    {
        $this->ownedNotification($request, $notificationId)->forceFill(['read_at' => null])->save();

        return back()->with('success', 'Notifikasi ditandai belum dibaca.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()
            ->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi aktif ditandai sudah dibaca.');
    }

    public function archive(Request $request, string $notificationId): RedirectResponse
    {
        $notification = $this->ownedNotification($request, $notificationId);
        $notification->forceFill(['data' => array_merge($notification->data, ['archived' => true])])->save();

        return back()->with('success', 'Notifikasi dipindahkan ke arsip.');
    }

    public function restore(Request $request, string $notificationId): RedirectResponse
    {
        $notification = $this->ownedNotification($request, $notificationId);
        $notification->forceFill(['data' => array_merge($notification->data, ['archived' => false])])->save();

        return back()->with('success', 'Notifikasi dipulihkan ke daftar utama.');
    }

    public function destroy(Request $request, string $notificationId): RedirectResponse
    {
        $this->ownedNotification($request, $notificationId)->delete();

        return back()->with('success', 'Notifikasi dihapus.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'channels' => ['required', 'array'],
            'channels.in_app' => ['required', 'boolean'],
            'channels.email' => ['required', 'in:0,false'],
            'channels.browser' => ['required', 'in:0,false'],
            'categories' => ['required', 'array'],
            'categories.attendance' => ['required', 'boolean'],
            'categories.approval' => ['required', 'boolean'],
            'categories.schedule' => ['required', 'boolean'],
            'categories.system' => ['required', 'boolean'],
            'categories.announcement' => ['required', 'boolean'],
        ]);

        $request->user()->notificationPreference()->updateOrCreate([], [
            'channels' => [
                'in_app' => filter_var($data['channels']['in_app'], FILTER_VALIDATE_BOOLEAN),
                'email' => false,
                'browser' => false,
            ],
            'categories' => array_map(static fn (mixed $value): bool => filter_var($value, FILTER_VALIDATE_BOOLEAN), $data['categories']),
        ]);

        return redirect()->to(route('notifications.index').'#notification-preferences')
            ->with('success', 'Preferensi notifikasi berhasil disimpan.');
    }

    private function ownedNotification(Request $request, string $notificationId): DatabaseNotification
    {
        /** @var MorphMany<DatabaseNotification, mixed> $notifications */
        $notifications = $request->user()->notifications();

        return $notifications->whereKey($notificationId)->firstOrFail();
    }
}
