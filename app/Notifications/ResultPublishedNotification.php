<?php

namespace App\Notifications;

use App\Models\AcademicSession;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Notifications\Messages\MailMessage;

class ResultPublishedNotification extends BaseSchoolNotification
{
    private string $studentName;

    private string $admissionNumber;

    private ?int $schoolId;

    private ?string $schoolName;

    private ?string $schoolSlug;

    private string $academicSessionName;

    private string $termName;

    public function __construct(
        Student $student,
        AcademicSession $academicSession,
        Term $term,
        private string $resultType
    ) {
        parent::__construct();

        $student->loadMissing('school');

        $this->studentName = $student->fullName();
        $this->admissionNumber = (string) $student->admission_number;
        $this->schoolId = $student->school_id;
        $this->schoolName = $student->school?->name;
        $this->schoolSlug = $student->school?->slug;
        $this->academicSessionName = $academicSession->name;
        $this->termName = $term->name;
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
            ->subject('Result published for '.$this->studentName)
            ->greeting('Hello,')
            ->line('A '.$this->label($this->resultType).' result has been published for '.$this->studentName.'.')
            ->line('School: '.$this->schoolName)
            ->line('Admission Number: '.$this->admissionNumber)
            ->line('Session / Term: '.$this->academicSessionName.' / '.$this->termName)
            ->line('Use the result checker when you are ready. A valid scratch card or PIN may be required.')
            ->action('Open Result Checker', $checkerUrl);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'student_name' => $this->studentName,
            'admission_number' => $this->admissionNumber,
            'school_id' => $this->schoolId,
            'school_name' => $this->schoolName,
            'academic_session' => $this->academicSessionName,
            'term' => $this->termName,
            'result_type' => $this->resultType,
        ];
    }

    private function label(string $resultType): string
    {
        return str_replace('_', ' ', $resultType);
    }
}
