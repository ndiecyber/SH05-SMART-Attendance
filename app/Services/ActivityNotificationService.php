<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\ActivityNotification;
use Throwable;

class ActivityNotificationService
{
    public function send(User $user, ActivityNotification $notification): void
    {
        try {
            $user->notify($notification);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
