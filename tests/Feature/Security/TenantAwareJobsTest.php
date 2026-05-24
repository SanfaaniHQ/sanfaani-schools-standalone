<?php

namespace Tests\Feature\Security;

use App\Jobs\ProcessBulkCommunicationBatch;
use App\Mail\Transactional\StudentTransactionalMail;
use App\Models\AcademicSession;
use App\Models\BulkCommunicationBatch;
use App\Models\BulkCommunicationRecipient;
use App\Models\CommunicationLog;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\BulkCommunicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TenantAwareJobsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['school_admin', 'super_admin'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_bulk_communication_job_processes_only_the_batch_school_context(): void
    {
        Mail::fake();

        $contextA = $this->academicContext('Job Alpha', 'job-alpha', 'JOB-A-001', 'guardian-a@example.test');
        $contextB = $this->academicContext('Job Beta', 'job-beta', 'JOB-B-001', 'guardian-b@example.test');
        $adminA = $this->schoolUser($contextA['school'], 'school_admin', 'job-admin@example.test');

        $batch = app(BulkCommunicationService::class)->createBatch($contextA['school'], $adminA, 'school_admin', [
            'audience' => 'selected_students',
            'student_ids' => [$contextA['student']->id, $contextB['student']->id],
            'channels' => ['email'],
            'type' => 'custom_message',
            'subject' => 'Tenant scoped job',
            'message' => 'Only School A should receive this.',
            'chunk_size' => 10,
            'max_sync_recipients' => 10,
        ]);

        $this->assertSame(1, $batch->recipients()->count());
        $this->assertDatabaseHas('bulk_communication_recipients', [
            'school_id' => $contextA['school']->id,
            'recipient_id' => $contextA['student']->id,
            'recipient_address' => 'guardian-a@example.test',
        ]);
        $this->assertDatabaseMissing('bulk_communication_recipients', [
            'school_id' => $contextB['school']->id,
            'recipient_id' => $contextB['student']->id,
        ]);

        (new ProcessBulkCommunicationBatch($batch->id, $adminA->id, $contextA['school']->id))
            ->handle(app(BulkCommunicationService::class));

        $this->assertSame(1, CommunicationLog::where('school_id', $contextA['school']->id)->count());
        $this->assertSame(0, CommunicationLog::where('school_id', $contextB['school']->id)->count());
        $this->assertDatabaseHas('communication_logs', [
            'school_id' => $contextA['school']->id,
            'recipient' => 'guardian-a@example.test',
            'subject' => 'Tenant scoped job',
        ]);
        $this->assertDatabaseMissing('communication_logs', [
            'recipient' => 'guardian-b@example.test',
        ]);
        Mail::assertSent(StudentTransactionalMail::class, function (StudentTransactionalMail $mail) use ($contextA) {
            return $mail->school?->id === $contextA['school']->id
                && data_get($mail->mailMetadata, 'bulk_communication_batch_id') !== null;
        });
    }

    public function test_bulk_communication_job_with_mismatched_school_context_fails_closed(): void
    {
        Mail::fake();

        $contextA = $this->academicContext('Mismatch Alpha', 'mismatch-alpha', 'MISMATCH-A', 'mismatch-a@example.test');
        $contextB = $this->academicContext('Mismatch Beta', 'mismatch-beta', 'MISMATCH-B', 'mismatch-b@example.test');
        $adminA = $this->schoolUser($contextA['school'], 'school_admin', 'mismatch-admin@example.test');

        $batch = app(BulkCommunicationService::class)->createBatch($contextA['school'], $adminA, 'school_admin', [
            'audience' => 'selected_students',
            'student_ids' => [$contextA['student']->id],
            'channels' => ['email'],
            'type' => 'custom_message',
            'subject' => 'Mismatched tenant job',
            'message' => 'This should not be processed.',
        ]);

        (new ProcessBulkCommunicationBatch($batch->id, $adminA->id, $contextB['school']->id))
            ->handle(app(BulkCommunicationService::class));

        $batch->refresh();

        $this->assertSame(BulkCommunicationBatch::STATUS_PENDING, $batch->status);
        $this->assertSame(1, $batch->recipients()->where('status', BulkCommunicationRecipient::STATUS_PENDING)->count());
        $this->assertSame(0, CommunicationLog::count());
        Mail::assertNothingSent();
    }

    private function academicContext(string $schoolName, string $slug, string $admissionNumber, string $guardianEmail): array
    {
        $school = School::create([
            'name' => $schoolName,
            'slug' => $slug,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'Basic 1',
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
        $student = Student::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => $admissionNumber,
            'first_name' => 'Queued',
            'last_name' => 'Student',
            'guardian_email' => $guardianEmail,
            'status' => 'active',
        ]);

        StudentClassEnrollment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'start_term_id' => $term->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        return compact('school', 'class', 'session', 'term', 'student');
    }

    private function schoolUser(School $school, string $role, string $email): User
    {
        $user = User::factory()->create([
            'school_id' => $school->id,
            'email' => $email,
        ]);
        $user->assignRole($role);

        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
            'status' => 'active',
        ]);

        return $user;
    }
}
