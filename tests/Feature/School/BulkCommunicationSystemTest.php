<?php

namespace Tests\Feature\School;

use App\Enums\ResultWorkflowStatus;
use App\Mail\Transactional\StudentTransactionalMail;
use App\Models\AcademicSession;
use App\Models\BulkCommunicationBatch;
use App\Models\BulkCommunicationRecipient;
use App\Models\CommunicationLog;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\BulkCommunicationService;
use App\Services\CommunicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class BulkCommunicationSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mail.default' => 'log',
            'mail.from.address' => 'noreply@example.test',
            'mail.from.name' => 'Sanfaani Test',
        ]);
    }

    public function test_bulk_student_communication_is_deduplicated_chunked_and_resumable(): void
    {
        Mail::fake();
        $context = $this->createAcademicContext();
        $admin = $this->createUser($context['school']);
        $this->actingAs($admin);
        $studentA = $this->createStudent($context, 'ADM-001', 'guardian@example.test');
        $studentB = $this->createStudent($context, 'ADM-002', 'guardian@example.test');
        $studentC = $this->createStudent($context, 'ADM-003', 'other@example.test');

        $batch = app(BulkCommunicationService::class)->createAndProcess($context['school'], $admin, 'school_admin', [
            'audience' => 'selected_students',
            'student_ids' => [$studentA->id, $studentB->id, $studentC->id],
            'channels' => ['email'],
            'type' => 'custom_message',
            'subject' => 'Parent update',
            'message' => 'Term update body',
            'chunk_size' => 1,
            'max_sync_recipients' => 1,
        ]);

        $this->assertSame(BulkCommunicationBatch::STATUS_PAUSED, $batch->status);
        $this->assertSame(2, $batch->total_recipients);
        $this->assertSame(1, $batch->sent_count);
        $this->assertSame(1, $batch->duplicate_count);
        $this->assertSame(1, $batch->pendingRecipientCount());

        $batch = app(BulkCommunicationService::class)->processPendingBatch($batch, $admin);

        $this->assertSame(BulkCommunicationBatch::STATUS_COMPLETED, $batch->status);
        $this->assertSame(2, $batch->sent_count);
        $this->assertSame(0, $batch->pendingRecipientCount());
        $this->assertSame(2, CommunicationLog::where('school_id', $context['school']->id)->count());
        Mail::assertSent(StudentTransactionalMail::class, 2);

        $duplicateSubmit = app(BulkCommunicationService::class)->createAndProcess($context['school'], $admin, 'school_admin', [
            'audience' => 'selected_students',
            'student_ids' => [$studentA->id, $studentB->id, $studentC->id],
            'channels' => ['email'],
            'type' => 'custom_message',
            'subject' => 'Parent update',
            'message' => 'Term update body',
            'chunk_size' => 1,
            'max_sync_recipients' => 1,
        ]);

        $this->assertTrue($batch->is($duplicateSubmit));
        $this->assertSame(1, BulkCommunicationBatch::count());
        $this->assertSame(2, CommunicationLog::where('school_id', $context['school']->id)->count());
    }

    public function test_published_result_filter_targets_only_students_with_current_published_results(): void
    {
        Mail::fake();
        $context = $this->createAcademicContext();
        $admin = $this->createUser($context['school']);
        $this->actingAs($admin);
        $publishedStudent = $this->createStudent($context, 'ADM-004', 'published@example.test');
        $unpublishedStudent = $this->createStudent($context, 'ADM-005', 'draft@example.test');
        $subject = Subject::create([
            'school_id' => $context['school']->id,
            'name' => 'Mathematics',
            'code' => 'MTH',
            'status' => 'active',
        ]);
        $this->createResult($context, $publishedStudent, $subject, ResultWorkflowStatus::Published->value);
        $this->createResult($context, $unpublishedStudent, $subject, ResultWorkflowStatus::Approved->value);

        $batch = app(BulkCommunicationService::class)->createAndProcess($context['school'], $admin, 'school_admin', [
            'audience' => 'class',
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'published_result_status' => 'published',
            'channels' => ['email'],
            'type' => 'result_notification',
            'subject' => 'Results are ready',
            'message' => 'Please check the portal.',
        ]);

        $this->assertSame(BulkCommunicationBatch::STATUS_COMPLETED, $batch->status);
        $this->assertSame(1, $batch->sent_count);
        $this->assertSame('published@example.test', CommunicationLog::firstOrFail()->recipient);
    }

    public function test_staff_audience_respects_user_role_status_filter(): void
    {
        Mail::fake();
        $context = $this->createAcademicContext();
        $admin = $this->createUser($context['school']);
        $this->actingAs($admin);
        $activeTeacher = $this->createUser($context['school'], 'active.teacher@example.test');
        $inactiveTeacher = $this->createUser($context['school'], 'inactive.teacher@example.test');
        $this->assignSchoolRole($context['school'], $activeTeacher, 'teacher', 'active');
        $this->assignSchoolRole($context['school'], $inactiveTeacher, 'teacher', 'inactive');

        $batch = app(BulkCommunicationService::class)->createAndProcess($context['school'], $admin, 'school_admin', [
            'audience' => 'teachers',
            'user_status' => 'active',
            'channels' => ['email'],
            'type' => 'custom_message',
            'subject' => 'Staff briefing',
            'message' => 'Meeting update.',
        ]);

        $this->assertSame(1, $batch->sent_count);
        $this->assertSame('active.teacher@example.test', CommunicationLog::firstOrFail()->recipient);
    }

    public function test_failed_bulk_recipient_can_be_retried_without_duplicating_successful_recipients(): void
    {
        $context = $this->createAcademicContext();
        $admin = $this->createUser($context['school']);
        $this->actingAs($admin);
        $this->createStudent($context, 'ADM-006', 'retry@example.test');
        $calls = 0;
        $communications = Mockery::mock(CommunicationService::class);
        $communications->shouldReceive('sendSchoolEmail')
            ->twice()
            ->andReturnUsing(function (...$arguments) use (&$calls, $context) {
                $calls++;

                return CommunicationLog::create([
                    'school_id' => $context['school']->id,
                    'recipient' => $arguments[1],
                    'subject' => $arguments[2],
                    'type' => $arguments[5],
                    'status' => $calls === 1 ? CommunicationLog::STATUS_FAILED : CommunicationLog::STATUS_SENT,
                    'failure_reason' => $calls === 1 ? 'SMTP timeout' : null,
                    'sent_at' => $calls === 1 ? null : now(),
                    'metadata' => $arguments[6] ?? [],
                ]);
            });
        app()->instance(CommunicationService::class, $communications);

        $service = app(BulkCommunicationService::class);
        $batch = $service->createAndProcess($context['school'], $admin, 'school_admin', [
            'audience' => 'class',
            'school_class_id' => $context['class']->id,
            'channels' => ['email'],
            'type' => 'custom_message',
            'subject' => 'Retry test',
            'message' => 'Retry body.',
        ]);

        $this->assertSame(BulkCommunicationBatch::STATUS_COMPLETED_WITH_FAILURES, $batch->status);
        $this->assertSame(1, $batch->failed_count);
        $this->assertSame(BulkCommunicationRecipient::STATUS_FAILED, $batch->recipients()->firstOrFail()->status);

        $batch = $service->retryFailed($batch, $admin);

        $this->assertSame(BulkCommunicationBatch::STATUS_COMPLETED, $batch->status);
        $this->assertSame(1, $batch->sent_count);
        $this->assertSame(0, $batch->failed_count);
        $this->assertSame(2, CommunicationLog::count());
        $this->assertSame(1, BulkCommunicationRecipient::count());
    }

    private function createAcademicContext(): array
    {
        $school = School::create([
            'name' => 'Bulk Communication School',
            'slug' => 'bulk-communication-school',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'Basic 4',
            'section' => 'A',
            'status' => 'active',
        ]);
        $session = AcademicSession::create([
            'school_id' => $school->id,
            'name' => '2025/2026',
            'status' => 'active',
        ]);
        $term = Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'status' => 'active',
        ]);

        return compact('school', 'class', 'session', 'term');
    }

    private function createStudent(array $context, string $admissionNumber, ?string $guardianEmail): Student
    {
        $student = Student::create([
            'school_id' => $context['school']->id,
            'school_class_id' => $context['class']->id,
            'admission_number' => $admissionNumber,
            'first_name' => 'Amina',
            'last_name' => $admissionNumber,
            'guardian_email' => $guardianEmail,
            'guardian_phone' => '08000000000',
            'status' => 'active',
        ]);

        StudentClassEnrollment::create([
            'school_id' => $context['school']->id,
            'student_id' => $student->id,
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'start_term_id' => $context['term']->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        return $student;
    }

    private function createResult(array $context, Student $student, Subject $subject, string $status): StudentResult
    {
        return StudentResult::create([
            'school_id' => $context['school']->id,
            'student_id' => $student->id,
            'school_class_id' => $context['class']->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'result_type' => 'term_result',
            'ca_score' => 30,
            'exam_score' => 55,
            'total_score' => 85,
            'status' => $status,
            'published_at' => $status === ResultWorkflowStatus::Published->value ? now() : null,
        ]);
    }

    private function createUser(School $school, string $email = 'admin@example.test'): User
    {
        return User::factory()->create([
            'school_id' => $school->id,
            'email' => $email,
        ]);
    }

    private function assignSchoolRole(School $school, User $user, string $role, string $status): void
    {
        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
            'status' => $status,
        ]);
    }
}
