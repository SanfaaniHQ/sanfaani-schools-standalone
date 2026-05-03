<?php

namespace App\Notifications;

use App\Models\School;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserAccountCreatedNotification extends Notification
{
    public function __construct(
        private User $user,
        private string $role,
        private ?School $school = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $supportEmail = app('App\Services\PlatformSettingService')->get()->support_email;
        $supportPhone = app('App\Services\PlatformSettingService')->get()->support_phone;
        $loginId = $this->user->staff_code ?: $this->user->email;

        return (new MailMessage)
            ->subject('Your Sanfaani Schools account is ready')
            ->greeting('Hello '.$this->user->name.',')
            ->line('An account has been created for you on Sanfaani Schools.')
            ->line('Role: '.str_replace('_', ' ', ucfirst($this->role)))
            ->when($this->school, fn (MailMessage $message) => $message->line('School: '.$this->school->name))
            ->line('Login ID: '.$loginId)
            ->line('Use the password provided securely by your administrator.')
            ->action('Login to Sanfaani Schools', route('login'))
            ->line('For support, contact '.$supportEmail.' or '.$supportPhone.'.');
    }
}
