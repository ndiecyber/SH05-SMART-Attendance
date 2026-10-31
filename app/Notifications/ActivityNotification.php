<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ActivityNotification extends Notification
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $title,
        public string $description,
        public string $category,
        public string $priority,
        public bool $requiresAction,
        public string $url,
        public string $source,
        public array $metadata = [],
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $preference = $notifiable->notificationPreference;
        $channels = $preference?->channels ?? [];
        $categories = $preference?->categories ?? [];
        $categoryKey = Str::snake($this->category);

        if (($channels['in_app'] ?? true) === false || ($categories[$categoryKey] ?? true) === false) {
            return [];
        }

        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'priority' => $this->priority,
            'requires_action' => $this->requiresAction,
            'url' => $this->url,
            'source' => $this->source,
            'archived' => false,
            'metadata' => $this->metadata,
        ];
    }
}
