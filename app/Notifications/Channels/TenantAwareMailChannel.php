<?php

namespace App\Notifications\Channels;

use App\Contracts\SchoolAwareMailNotification;
use App\Models\School;
use App\Services\MailSettingService;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;

class TenantAwareMailChannel extends MailChannel
{
    public function __construct(
        MailFactory $mailer,
        Markdown $markdown,
        private MailSettingService $mailSettings
    ) {
        parent::__construct($mailer, $markdown);
    }

    public function send($notifiable, Notification $notification)
    {
        $school = $this->schoolFor($notifiable, $notification);

        return $this->mailSettings->deliverForSchool(
            $school,
            fn () => parent::send($notifiable, $notification)
        )['result'];
    }

    private function schoolFor(object $notifiable, Notification $notification): ?School
    {
        $schoolId = $notification instanceof SchoolAwareMailNotification
            ? $notification->schoolIdForMail($notifiable)
            : data_get($notifiable, 'school_id');

        return filled($schoolId) ? School::find((int) $schoolId) : null;
    }
}
