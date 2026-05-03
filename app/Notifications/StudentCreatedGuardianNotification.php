<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentCreatedGuardianNotification extends Notification
{
    public function __construct(private Student $student) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->student->loadMissing('school');

        return (new MailMessage)
            ->subject($student->fullName().' has been registered')
            ->greeting('Hello,')
            ->line($student->fullName().' has been registered on Sanfaani Schools for '.$student->school->name.'.')
            ->line('Admission Number: '.$student->admission_number)
            ->line('Published results can be checked through the result checker. Result access may require a scratch card or PIN when results are published.')
            ->action('Open Result Checker', route('public.results.index', ['school_id' => $student->school_id]));
    }
}
