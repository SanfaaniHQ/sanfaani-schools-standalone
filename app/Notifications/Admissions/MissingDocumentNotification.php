<?php

namespace App\Notifications\Admissions;

use Illuminate\Notifications\Messages\MailMessage;

class MissingDocumentNotification extends BaseAdmissionNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        return $this->mail(
            'Admission documents required',
            'The school needs additional or corrected admission documents.'
        );
    }
}
