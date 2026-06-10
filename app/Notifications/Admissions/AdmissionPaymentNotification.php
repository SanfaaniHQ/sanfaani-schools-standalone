<?php

namespace App\Notifications\Admissions;

use Illuminate\Notifications\Messages\MailMessage;

class AdmissionPaymentNotification extends BaseAdmissionNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        return $this->mail(
            'Admission payment status updated',
            'Payment status: '.str($this->application->payment_status)->replace('_', ' ')->title()
        );
    }
}
