<?php

namespace App\Notifications;

use App\Models\School;
use Illuminate\Notifications\Messages\MailMessage;

class SchoolCreatedNotification extends BaseSchoolNotification
{
    private int $schoolId;

    private string $schoolName;

    public function __construct(School $school)
    {
        parent::__construct();

        $this->schoolId = $school->id;
        $this->mailSchoolId = $school->id;
        $this->schoolName = $school->name;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $settings = app('App\Services\PlatformSettingService')->get();

        return (new MailMessage)
            ->subject($this->schoolName.' workspace is ready')
            ->greeting('Hello,')
            ->line($this->schoolName.' has been created successfully.')
            ->line('Portal URL: '.$settings->product_url)
            ->line('Recommended setup steps: create school admin users, confirm classes and subjects, configure sessions and terms, then review result access rules.')
            ->action('Open Portal', route('login'))
            ->line('For support, contact '.$settings->support_email.' or '.$settings->support_phone.'.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'school_id' => $this->schoolId,
            'school_name' => $this->schoolName,
        ];
    }
}
