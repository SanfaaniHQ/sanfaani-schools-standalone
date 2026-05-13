<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\ScratchCard;
use App\Models\ScratchCardBatch;
use App\Models\ScratchCardUsage;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentPromotionBatch;
use App\Models\StudentPromotionItem;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\StudentAcademicLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentAcademicLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_promotion_creates_enrollment_lineage_without_rewriting_old_results(): void
    {
        $school = $this->createSchool();
        $admin = $this->createUserForSchool($school, 'school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $fromClass = $this->createClass($school, 'JSS 1', 'A');
        $toClass = $this->createClass($school, 'JSS 2', 'A');
        $fromSession = $this->createSession($school, '2025/2026');
        $toSession = $this->createSession($school, '2026/2027');
        $fromTerm = $this->createTerm($school, $fromSession, 'Third Term');
        $this->createTerm($school, $toSession, 'First Term');
        $subject = $this->createSubject($school);
        $student = $this->createStudent($school, $fromClass, 'ADM-001');

        $sourceEnrollment = StudentClassEnrollment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $fromClass->id,
            'academic_session_id' => $fromSession->id,
            'start_term_id' => $fromTerm->id,
            'status' => 'active',
            'created_by' => $admin->id,
            'enrolled_at' => now()->subMonths(6),
        ]);

        $result = StudentResult::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $fromClass->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $fromSession->id,
            'term_id' => $fromTerm->id,
            'result_type' => 'term_result',
            'ca_score' => 30,
            'exam_score' => 55,
            'total_score' => 85,
            'grade' => 'A',
            'remark' => 'Excellent',
            'status' => 'published',
            'published_at' => now()->subMonth(),
            'published_by' => $admin->id,
            'recorded_by' => $admin->id,
        ]);

        $processed = app(StudentAcademicLifecycleService::class)->processBatch(
            $school,
            $admin,
            [
                'from_academic_session_id' => $fromSession->id,
                'to_academic_session_id' => $toSession->id,
                'from_school_class_id' => $fromClass->id,
                'to_school_class_id' => $toClass->id,
                'promotion_type' => 'promote_selected',
            ],
            collect([$student->id => ['selected' => '1', 'action' => 'promote']]),
            collect([$student->id => $student]),
            collect([$fromClass->id => $fromClass, $toClass->id => $toClass]),
            collect([$fromSession->id => $fromSession, $toSession->id => $toSession])
        );

        $student->refresh();
        $result->refresh();
        $sourceEnrollment->refresh();
        $targetEnrollment = StudentClassEnrollment::where('student_id', $student->id)
            ->where('academic_session_id', $toSession->id)
            ->where('school_class_id', $toClass->id)
            ->firstOrFail();
        $promotionItem = StudentPromotionItem::firstOrFail();

        $this->assertSame($toClass->id, $student->school_class_id);
        $this->assertSame($fromClass->id, $result->school_class_id);
        $this->assertSame('completed', $sourceEnrollment->status);
        $this->assertSame($fromTerm->id, $sourceEnrollment->end_term_id);
        $this->assertSame($sourceEnrollment->id, $targetEnrollment->promoted_from_enrollment_id);
        $this->assertSame($sourceEnrollment->id, $promotionItem->from_student_class_enrollment_id);
        $this->assertSame($targetEnrollment->id, $promotionItem->to_student_class_enrollment_id);
        $this->assertSame(1, StudentClassEnrollment::where('student_id', $student->id)->current()->count());
        $this->assertSame(['promote' => 1, 'repeat' => 0, 'demote' => 0, 'graduate' => 0, 'transfer' => 0, 'withdraw' => 0, 'skip' => 0], $processed['counts']);
        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'auditable_id' => $student->id,
            'action' => 'student_promoted',
        ]);
    }

    public function test_locked_session_blocks_lifecycle_batch_and_rolls_back(): void
    {
        $school = $this->createSchool();
        $admin = $this->createUserForSchool($school, 'school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $fromClass = $this->createClass($school, 'JSS 1', 'A');
        $toClass = $this->createClass($school, 'JSS 2', 'A');
        $fromSession = $this->createSession($school, '2025/2026', 'locked');
        $toSession = $this->createSession($school, '2026/2027');
        $fromTerm = $this->createTerm($school, $fromSession, 'Third Term');
        $this->createTerm($school, $toSession, 'First Term');
        $student = $this->createStudent($school, $fromClass, 'ADM-002');

        StudentClassEnrollment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $fromClass->id,
            'academic_session_id' => $fromSession->id,
            'start_term_id' => $fromTerm->id,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(StudentAcademicLifecycleService::class)->processBatch(
                $school,
                $admin,
                [
                    'from_academic_session_id' => $fromSession->id,
                    'to_academic_session_id' => $toSession->id,
                    'from_school_class_id' => $fromClass->id,
                    'to_school_class_id' => $toClass->id,
                    'promotion_type' => 'promote_selected',
                ],
                collect([$student->id => ['selected' => '1', 'action' => 'promote']]),
                collect([$student->id => $student]),
                collect([$fromClass->id => $fromClass, $toClass->id => $toClass]),
                collect([$fromSession->id => $fromSession, $toSession->id => $toSession])
            );
        } finally {
            $student->refresh();

            $this->assertSame($fromClass->id, $student->school_class_id);
            $this->assertSame(0, StudentPromotionBatch::count());
            $this->assertSame(1, StudentClassEnrollment::where('student_id', $student->id)->current()->count());
        }
    }

    public function test_archive_and_restore_preserve_academic_status_results_and_scratch_card_history(): void
    {
        $school = $this->createSchool();
        $admin = $this->createUserForSchool($school, 'school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $class = $this->createClass($school, 'SSS 3', 'A');
        $session = $this->createSession($school, '2025/2026');
        $term = $this->createTerm($school, $session, 'Third Term');
        $subject = $this->createSubject($school);
        $student = $this->createStudent($school, $class, 'ADM-003', 'graduated');

        StudentClassEnrollment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'start_term_id' => $term->id,
            'end_term_id' => $term->id,
            'status' => 'graduated',
            'created_by' => $admin->id,
        ]);

        StudentResult::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'ca_score' => 28,
            'exam_score' => 50,
            'total_score' => 78,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $batch = ScratchCardBatch::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'title' => 'Graduation cards',
            'quantity' => 1,
            'payment_status' => 'paid',
            'status' => 'generated',
        ]);
        $card = ScratchCard::create([
            'scratch_card_batch_id' => $batch->id,
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'serial_number' => 'SC-ARCHIVE-001',
            'pin_code' => '123456',
            'pin_hash' => hash('sha256', '123456'),
            'used_count' => 1,
            'status' => 'used',
            'used_by_student_id' => $student->id,
        ]);
        ScratchCardUsage::create([
            'scratch_card_id' => $card->id,
            'school_id' => $school->id,
            'student_id' => $student->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'used_at' => now(),
        ]);

        app(StudentAcademicLifecycleService::class)->archive($school, $student, $admin);

        $this->assertSoftDeleted('students', ['id' => $student->id]);
        $this->assertSame('graduated', Student::withTrashed()->findOrFail($student->id)->status);
        $this->assertSame(1, StudentResult::where('student_id', $student->id)->count());
        $this->assertSame(1, ScratchCardUsage::where('student_id', $student->id)->count());

        $restored = app(StudentAcademicLifecycleService::class)->restore($school, $student->id, $admin);

        $this->assertFalse($restored->trashed());
        $this->assertSame('graduated', $restored->status);
        $this->assertSame(0, StudentClassEnrollment::where('student_id', $student->id)->current()->count());
        $this->assertDatabaseHas('audit_logs', ['school_id' => $school->id, 'action' => 'student_archived']);
        $this->assertDatabaseHas('audit_logs', ['school_id' => $school->id, 'action' => 'student_restored']);
    }

    public function test_manual_result_update_keeps_historical_class_after_student_moves(): void
    {
        $school = $this->createSchool();
        $admin = $this->createUserForSchool($school, 'school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $oldClass = $this->createClass($school, 'JSS 1', 'A');
        $currentClass = $this->createClass($school, 'JSS 2', 'A');
        $oldSession = $this->createSession($school, '2025/2026');
        $currentSession = $this->createSession($school, '2026/2027');
        $oldTerm = $this->createTerm($school, $oldSession, 'Third Term');
        $currentTerm = $this->createTerm($school, $currentSession, 'First Term');
        $subject = $this->createSubject($school);
        $student = $this->createStudent($school, $currentClass, 'ADM-004');

        $oldEnrollment = StudentClassEnrollment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $oldClass->id,
            'academic_session_id' => $oldSession->id,
            'start_term_id' => $oldTerm->id,
            'end_term_id' => $oldTerm->id,
            'status' => 'completed',
            'created_by' => $admin->id,
        ]);
        StudentClassEnrollment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $currentClass->id,
            'academic_session_id' => $currentSession->id,
            'start_term_id' => $currentTerm->id,
            'status' => 'active',
            'created_by' => $admin->id,
            'promoted_from_enrollment_id' => $oldEnrollment->id,
        ]);

        $result = StudentResult::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $oldClass->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $oldSession->id,
            'term_id' => $oldTerm->id,
            'result_type' => 'term_result',
            'ca_score' => 20,
            'exam_score' => 40,
            'total_score' => 60,
            'status' => 'draft',
            'recorded_by' => $admin->id,
        ]);

        $this->patch(route('school.results.manual.update', $result), [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $oldSession->id,
            'term_id' => $oldTerm->id,
            'ca_score' => 25,
            'exam_score' => 45,
            'teacher_remark' => 'Updated safely',
            'status' => 'draft',
        ])->assertRedirect(route('school.results.manual.index'));

        $result->refresh();

        $this->assertSame($oldClass->id, $result->school_class_id);
        $this->assertSame('70.00', $result->total_score);
    }

    private function createSchool(): School
    {
        return School::create([
            'name' => fake()->unique()->company(),
            'slug' => fake()->unique()->slug(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function createClass(School $school, string $name, string $section): SchoolClass
    {
        return SchoolClass::create([
            'school_id' => $school->id,
            'name' => $name,
            'section' => $section,
            'status' => 'active',
        ]);
    }

    private function createSession(School $school, string $name, string $status = 'active'): AcademicSession
    {
        return AcademicSession::create([
            'school_id' => $school->id,
            'name' => $name,
            'status' => $status,
        ]);
    }

    private function createTerm(School $school, AcademicSession $session, string $name): Term
    {
        return Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => $name,
            'status' => 'active',
        ]);
    }

    private function createSubject(School $school): Subject
    {
        return Subject::create([
            'school_id' => $school->id,
            'name' => fake()->unique()->word().' Studies',
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'status' => 'active',
        ]);
    }

    private function createStudent(School $school, SchoolClass $class, string $admissionNumber, string $status = 'active'): Student
    {
        return Student::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => $admissionNumber,
            'first_name' => 'Ada',
            'last_name' => 'Student',
            'status' => $status,
        ]);
    }

    private function createUserForSchool(School $school, string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create(['school_id' => $school->id]);
        $user->assignRole($role);

        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
            'status' => 'active',
        ]);

        return $user;
    }

    private function actAsSchoolRole(User $user, School $school, string $role): void
    {
        $this->actingAs($user);

        session([
            'active_school_id' => $school->id,
            'active_role_context' => $role,
        ]);
    }
}
