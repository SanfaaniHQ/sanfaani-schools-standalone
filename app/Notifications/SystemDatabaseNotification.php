<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemDatabaseNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly array $payload)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => (string) ($this->payload['title'] ?? 'System notification'),
            'body' => (string) ($this->payload['body'] ?? ''),
            'category' => (string) ($this->payload['category'] ?? 'system'),
            'event' => (string) ($this->payload['event'] ?? 'system.event'),
            'severity' => (string) ($this->payload['severity'] ?? 'info'),
            'action_url' => $this->payload['action_url'] ?? null,
            'school_id' => $this->payload['school_id'] ?? null,
            'role' => $this->payload['role'] ?? null,
            'metadata' => $this->payload['metadata'] ?? [],
        ];
    }
}
