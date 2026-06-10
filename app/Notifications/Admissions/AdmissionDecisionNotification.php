<?php

namespace App\Notifications\Admissions;

use Illuminate\Notifications\Messages\MailMessage;

class AdmissionDecisionNotification extends BaseAdmissionNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        return $this->mail(
            'Admission decision available',
            'Decision: '.str($this->application->status)->replace('_', ' ')->title()
        );
    }
}
