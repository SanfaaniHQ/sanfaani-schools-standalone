<?php

namespace App\Notifications\Admissions;

use App\Models\Admissions\AdmissionApplication;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationSubmittedNotification extends BaseAdmissionNotification
{
    public function __construct(AdmissionApplication $application, public string $trackingToken)
    {
        parent::__construct($application);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->mail(
            'Admission application received',
            'The school has received the admission application.'
        )->line('Tracking token: '.$this->trackingToken);
    }
}
