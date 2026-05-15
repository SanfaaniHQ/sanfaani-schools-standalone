<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class ScratchCardRequestStatusNotification extends BaseSchoolNotification
{
    public function __construct(
        public string $status,
        public ?string $message = null
    ) {
        parent::__construct();
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $branding = $this->getSchoolBranding($notifiable);
        $schoolName = $branding->name ?: 'your school';

        return (new MailMessage)
            ->subject('Scratch Card Request Update')
            ->greeting('Hello,')
            ->line("Your scratch card request status: {$this->statusLabel()}")
            ->line($this->message ?? 'Login to view details.')
            ->action('Open Scratch Cards', route('school.scratch-cards.index'))
            ->line('This update was sent for '.$schoolName.'.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'school_id' => data_get($notifiable, 'school_id'),
        ];
    }

    private function statusLabel(): string
    {
        return str_replace('_', ' ', ucfirst($this->status));
    }
}
