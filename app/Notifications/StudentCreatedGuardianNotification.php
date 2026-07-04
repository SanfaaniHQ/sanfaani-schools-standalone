<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Notifications\Messages\MailMessage;

class StudentCreatedGuardianNotification extends BaseSchoolNotification
{
    private string $studentName;

    private string $admissionNumber;

    private ?int $schoolId;

    private ?string $schoolName;

    private ?string $schoolSlug;

    public function __construct(Student $student)
    {
        parent::__construct();

        $student->loadMissing('school');

        $this->studentName = $student->fullName();
        $this->admissionNumber = (string) $student->admission_number;
        $this->schoolId = $student->school_id;
        $this->mailSchoolId = $student->school_id;
        $this->schoolName = $student->school?->name;
        $this->schoolSlug = $student->school?->slug;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $checkerUrl = $this->schoolSlug
            ? route('public.school.results.index', ['school' => $this->schoolSlug])
            : route('public.results.index');

        return (new MailMessage)
            ->subject($this->studentName.' has been registered')
            ->greeting('Hello,')
            ->line($this->studentName.' has been registered for '.$this->schoolName.'.')
            ->line('Admission Number: '.$this->admissionNumber)
            ->line('Published results can be checked through the result checker. Result access may require a scratch card or PIN when results are published.')
            ->action('Open Result Checker', $checkerUrl);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'student_name' => $this->studentName,
            'admission_number' => $this->admissionNumber,
            'school_id' => $this->schoolId,
            'school_name' => $this->schoolName,
        ];
    }
}
