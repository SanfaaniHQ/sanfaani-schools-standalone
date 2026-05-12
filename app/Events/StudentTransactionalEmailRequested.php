<?php

namespace App\Events;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\ScratchCardBatch;
use App\Models\Student;
use App\Models\StudentPromotionItem;
use App\Models\Term;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentTransactionalEmailRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public School $school,
        public Student $student,
        public string $eventKey,
        public string $recipient,
        public string $subject,
        public string $headline,
        public string $body,
        public array $metadata = [],
        public string $type = 'student_transactional',
        public bool $respectPreferences = true
    ) {}

    public static function studentCreated(Student $student): self
    {
        $school = $student->school;

        return new self(
            $school,
            $student,
            'student_created_guardian',
            (string) $student->guardian_email,
            $student->fullName().' has been registered',
            'Student onboarding update',
            'A student record has been created for '.$student->fullName().".\nAdmission number: ".$student->admission_number,
            [
                'student_id' => $student->id,
                'admission_number' => $student->admission_number,
            ],
            'student_created'
        );
    }

    public static function studentArchived(Student $student): self
    {
        $school = $student->school;

        return new self(
            $school,
            $student,
            'student_archived',
            (string) $student->guardian_email,
            $student->fullName().' has been archived',
            'Student status update',
            'The student record for '.$student->fullName().' has been archived. Academic records and results remain preserved.',
            [
                'student_id' => $student->id,
                'admission_number' => $student->admission_number,
                'status' => $student->status,
            ],
            'student_archived'
        );
    }

    public static function studentPromoted(StudentPromotionItem $item): self
    {
        $student = $item->student;
        $school = $student->school;
        $fromClass = $item->fromClass;
        $toClass = $item->toClass;
        $fromSession = $item->fromSession;
        $toSession = $item->toSession;
        $action = str_replace('_', ' ', $item->action);

        return new self(
            $school,
            $student,
            'student_promoted',
            (string) $student->guardian_email,
            $student->fullName().' promotion update',
            'Class progression update',
            self::promotionBody($student, $item->action, $fromClass, $toClass, $fromSession, $toSession),
            [
                'student_id' => $student->id,
                'promotion_item_id' => $item->id,
                'promotion_batch_id' => $item->student_promotion_batch_id,
                'action' => $item->action,
                'action_label' => $action,
                'from_class_id' => $item->from_school_class_id,
                'to_class_id' => $item->to_school_class_id,
                'from_academic_session_id' => $item->from_academic_session_id,
                'to_academic_session_id' => $item->to_academic_session_id,
            ],
            'student_promoted'
        );
    }

    public static function resultPublished(Student $student, AcademicSession $academicSession, Term $term, array $context = []): self
    {
        $school = $student->school;

        return new self(
            $school,
            $student,
            'result_published',
            (string) $student->guardian_email,
            'Result published for '.$student->fullName(),
            'Result available',
            'A result has been published for '.$student->fullName().".\nSession: ".$academicSession->name."\nTerm: ".$term->name."\nResult type: ".str_replace('_', ' ', (string) ($context['result_type'] ?? 'term_result')),
            array_merge($context, [
                'student_id' => $student->id,
                'academic_session_id' => $academicSession->id,
                'term_id' => $term->id,
            ]),
            'result_published'
        );
    }

    public static function scratchCardGenerated(Student $student, ScratchCardBatch $batch): self
    {
        $school = $student->school;

        return new self(
            $school,
            $student,
            'scratch_card_generated',
            (string) $student->guardian_email,
            'Scratch card access is available',
            'Result access update',
            'Scratch card access has been generated for an upcoming result access window. Contact the school office for the assigned card details.',
            [
                'student_id' => $student->id,
                'scratch_card_batch_id' => $batch->id,
                'school_class_id' => $batch->school_class_id,
                'academic_session_id' => $batch->academic_session_id,
                'term_id' => $batch->term_id,
                'result_type' => $batch->result_type,
            ],
            'scratch_card_generated'
        );
    }

    public static function resultAvailable(Student $student, AcademicSession $academicSession, Term $term, array $context = []): self
    {
        $school = $student->school;

        return new self(
            $school,
            $student,
            'result_available',
            (string) $student->guardian_email,
            'Result available for '.$student->fullName(),
            'Result access confirmation',
            'The requested result is available for '.$student->fullName().".\nSession: ".$academicSession->name."\nTerm: ".$term->name,
            array_merge($context, [
                'student_id' => $student->id,
                'academic_session_id' => $academicSession->id,
                'term_id' => $term->id,
            ]),
            'result_available'
        );
    }

    private static function promotionBody(
        Student $student,
        string $action,
        ?SchoolClass $fromClass,
        ?SchoolClass $toClass,
        ?AcademicSession $fromSession,
        ?AcademicSession $toSession
    ): string {
        $from = trim(($fromClass->name ?? 'previous class').' '.($fromClass->section ?? ''));
        $to = trim(($toClass->name ?? 'updated class').' '.($toClass->section ?? ''));

        return match ($action) {
            'promote' => $student->fullName().' has been promoted from '.$from.' to '.$to.".\nSession: ".($fromSession->name ?? 'N/A').' to '.($toSession->name ?? 'N/A'),
            'repeat' => $student->fullName().' will repeat in '.$to.".\nSession: ".($toSession->name ?? 'N/A'),
            'graduate' => $student->fullName().' has been marked as graduated.',
            'transfer' => $student->fullName().' has been marked as transferred.',
            'withdraw' => $student->fullName().' has been marked as withdrawn.',
            default => $student->fullName().' has a student progression update.',
        };
    }
}
