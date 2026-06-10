<?php

namespace App\Notifications\Admissions;

use Illuminate\Notifications\Messages\MailMessage;

class AdmissionStatusChangedNotification extends BaseAdmissionNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        return $this->mail(
            'Admission application status updated',
            'Current status: '.str($this->application->status)->replace('_', ' ')->title()
        );
    }
}
