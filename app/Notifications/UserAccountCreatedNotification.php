<?php

namespace App\Notifications;

use App\Models\School;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

class UserAccountCreatedNotification extends BaseSchoolNotification
{
    private string $userName;

    private string $loginId;

    private ?int $schoolId;

    private ?string $schoolName;

    public function __construct(
        User $user,
        private string $role,
        ?School $school = null
    ) {
        parent::__construct();

        $this->userName = $user->name;
        $this->loginId = $user->staff_code ?: (string) $user->email;
        $this->schoolId = $school?->id;
        $this->schoolName = $school?->name;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $supportEmail = app('App\Services\PlatformSettingService')->get()->support_email;
        $supportPhone = app('App\Services\PlatformSettingService')->get()->support_phone;
        $workspaceName = $this->schoolName ?: 'the platform';

        return (new MailMessage)
            ->subject('Your '.$workspaceName.' account is ready')
            ->greeting('Hello '.$this->userName.',')
            ->line('An account has been created for you.')
            ->line('Role: '.str_replace('_', ' ', ucfirst($this->role)))
            ->when($this->schoolName, fn (MailMessage $message) => $message->line('School: '.$this->schoolName))
            ->line('Login ID: '.$this->loginId)
            ->line('Use the password provided securely by your administrator.')
            ->action('Login', route('login'))
            ->line('For support, contact '.$supportEmail.' or '.$supportPhone.'.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'role' => $this->role,
            'school_id' => $this->schoolId ?: data_get($notifiable, 'school_id'),
            'school_name' => $this->schoolName,
        ];
    }
}
