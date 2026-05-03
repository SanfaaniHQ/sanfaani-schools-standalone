<?php

namespace App\Notifications;

use App\Models\AcademicSession;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResultPublishedNotification extends Notification
{
    public function __construct(
        private Student $student,
        private AcademicSession $academicSession,
        private Term $term,
        private string $resultType
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->student->loadMissing('school');

        return (new MailMessage)
            ->subject('Result published for '.$student->fullName())
            ->greeting('Hello,')
            ->line('A '.$this->label($this->resultType).' result has been published for '.$student->fullName().'.')
            ->line('School: '.$student->school->name)
            ->line('Admission Number: '.$student->admission_number)
            ->line('Session / Term: '.$this->academicSession->name.' / '.$this->term->name)
            ->line('Use the result checker when you are ready. A valid scratch card or PIN may be required.')
            ->action('Open Result Checker', route('public.results.index', ['school_id' => $student->school_id]));
    }

    private function label(string $resultType): string
    {
        return str_replace('_', ' ', $resultType);
    }
}
