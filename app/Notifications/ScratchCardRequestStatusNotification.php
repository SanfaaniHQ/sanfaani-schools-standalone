<?php

namespace App\Notifications;

use App\Models\ScratchCardBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScratchCardRequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $queue = 'mail';

    public int $tries = 2;

    public int $timeout = 30;

    public function __construct(
        private ScratchCardBatch $batch,
        private string $status,
        private ?string $note = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $batch = $this->batch->loadMissing('school', 'academicSession', 'term');
        $settings = app('App\Services\PlatformSettingService')->get();

        return (new MailMessage)
            ->subject('Scratch card request '.$this->statusLabel())
            ->greeting('Hello,')
            ->line('Scratch card request: '.($batch->title ?: 'Batch #'.$batch->id))
            ->line('School: '.$batch->school->name)
            ->line('Session / Term: '.$batch->academicSession?->name.' / '.$batch->term?->name)
            ->line('Quantity: '.$batch->quantity)
            ->line('Status: '.$this->statusLabel())
            ->when($this->note, fn (MailMessage $message) => $message->line($this->note))
            ->action('Open Scratch Cards', route('school.scratch-cards.index'))
            ->line('For support, contact '.$settings->support_email.' or '.$settings->support_phone.'.');
    }

    private function statusLabel(): string
    {
        return str_replace('_', ' ', ucfirst($this->status));
    }
}
