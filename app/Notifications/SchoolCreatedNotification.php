<?php

namespace App\Notifications;

use App\Models\School;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SchoolCreatedNotification extends Notification
{
    public function __construct(private School $school) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $settings = app('App\Services\PlatformSettingService')->get();

        return (new MailMessage)
            ->subject($this->school->name.' has been created on Sanfaani Schools')
            ->greeting('Hello,')
            ->line($this->school->name.' has been created on Sanfaani Schools.')
            ->line('Portal URL: '.$settings->product_url)
            ->line('Recommended setup steps: create school admin users, confirm classes and subjects, configure sessions and terms, then review result access rules.')
            ->action('Open Portal', route('login'))
            ->line('For support, contact '.$settings->support_email.' or '.$settings->support_phone.'.');
    }
}
