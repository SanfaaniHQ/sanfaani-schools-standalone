<?php

namespace Tests\Feature\Security;

use App\Enums\ResultWorkflowStatus;
use App\Models\AcademicSession;
use App\Models\LeadRequest;
use App\Models\ResultPublication;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\ScratchCardBatch;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\SupportMessage;
use App\Models\SupportThread;
use App\Models\TeacherClassAssignment;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'result_officer', 'parent', 'student', 'accountant'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_school_admin_from_school_a_cannot_view_or_edit_school_b_students(): void
    {
        $schoolA = $this->school('Alpha School', 'alpha-school');
        $schoolB = $this->school('Beta School', 'beta-school');
        $contextA = $this->academicContext($schoolA, 'Alpha Class', 'ALPHA-001');
        $contextB = $this->academicContext($schoolB, 'Beta Class', 'BETA-001');
        $adminA = $this->schoolUser($schoolA, 'school_admin', 'admin-a@example.test');

        $this->actAsSchoolRole($adminA, $schoolA, 'school_admin');

        $this->get(route('school.students.show', $contextA['student']))->assertOk();
        $this->get(route('school.students.show', $contextB['student']))->assertForbidden();
        $this->get(route('school.students.edit', $contextB['student']))->assertForbidden();
        $this->get(route('school.students.index'))
            ->assertOk()
            ->assertSee('ALPHA-001')
            ->assertDontSee('BETA-001');
    }

    public function test_teacher_from_school_a_cannot_view_school_b_student_records(): void
    {
        $schoolA = $this->school('Teacher Alpha', 'teacher-alpha');
        $schoolB = $this->school('Teacher Beta', 'teacher-beta');
        $contextA = $this->academicContext($schoolA, 'Assigned Class', 'TA-001');
        $contextB = $this->academicContext($schoolB, 'Other School Class', 'TB-001');
        $teacherA = $this->schoolUser($schoolA, 'teacher', 'teacher-a@example.test');

        TeacherClassAssignment::create([
            'school_id' => $schoolA->id,
            'teacher_user_id' => $teacherA->id,
            'school_class_id' => $contextA['class']->id,
            'status' => 'active',
        ]);

        $this->actAsSchoolRole($teacherA, $schoolA, 'teacher');

        $this->get(route('school.students.show', $contextA['student']))->assertOk();
        $this->get(route('school.students.show', $contextB['student']))->assertForbidden();
    }

    public function test_result_officer_cannot_access_another_schools_result_workflow(): void
    {
        $schoolA = $this->school('Result Alpha', 'result-alpha');
        $schoolB = $this->school('Result Beta', 'result-beta');
        $contextB = $this->academicContext($schoolB, 'Beta Results', 'RB-001');
        $resultB = $this->studentResult($contextB, ResultWorkflowStatus::Draft->value);
        $officerA = $this->schoolUser($schoolA, 'result_officer', 'officer-a@example.test');

        $this->actAsSchoolRole($officerA, $schoolA, 'result_officer');

        $this->get(route('school.results.manual.edit', $resultB))->assertForbidden();
        $this->post(route('school.results.publishing.publish-single', $resultB))->assertForbidden();
    }

    public function test_scratch_cards_and_result_publications_are_school_scoped(): void
    {
        $schoolA = $this->school('Scratch Alpha', 'scratch-alpha');
        $schoolB = $this->school('Scratch Beta', 'scratch-beta');
        $contextA = $this->academicContext($schoolA, 'Visible Class', 'SA-001');
        $contextB = $this->academicContext($schoolB, 'Hidden Class', 'SB-001');
        $adminA = $this->schoolUser($schoolA, 'school_admin', 'scratch-admin@example.test');

        $this->scratchBatch($contextB, $adminA);
        $this->resultPublication($contextA, $adminA, 'Visible Class');
        $this->resultPublication($contextB, $adminA, 'Hidden Class');

        $this->actAsSchoolRole($adminA, $schoolA, 'school_admin');

        $otherBatch = ScratchCardBatch::where('school_id', $schoolB->id)->firstOrFail();

        $this->get(route('school.scratch-cards.show', $otherBatch))->assertForbidden();
        $this->get(route('school.results.publishing.index'))
            ->assertOk()
            ->assertSee('Visible Class')
            ->assertDontSee('Hidden Class');
    }

    public function test_support_and_marketing_boundaries_are_enforced(): void
    {
        $schoolA = $this->school('Support Alpha', 'support-alpha');
        $schoolB = $this->school('Support Beta', 'support-beta');
        $adminA = $this->schoolUser($schoolA, 'school_admin', 'support-admin@example.test');
        $adminB = $this->schoolUser($schoolB, 'school_admin', 'support-b@example.test');
        $superAdmin = $this->superAdmin();
        $threadB = $this->supportThread($schoolB, $adminB, 'Beta private support thread');
        $lead = LeadRequest::create([
            'type' => 'demo',
            'name' => 'Platform Lead',
            'school_name' => 'Lead Academy',
            'email' => 'lead@example.test',
            'status' => LeadRequest::STATUS_NEW,
        ]);

        $this->actAsSchoolRole($adminA, $schoolA, 'school_admin');
        $this->get(route('school.support.show', $threadB))->assertForbidden();
        $this->get(route('admin.lead-requests.index'))->assertForbidden();

        $this->actingAs($superAdmin);
        $this->get(route('admin.lead-requests.show', $lead))->assertOk()->assertSee('Lead Academy');
        $this->get(route('admin.support-threads.show', $threadB))->assertOk()->assertSee('Beta private support thread');
    }

    public function test_super_admin_platform_views_are_global_and_non_super_admins_are_blocked(): void
    {
        $schoolA = $this->school('Platform Alpha', 'platform-alpha');
        $schoolB = $this->school('Platform Beta', 'platform-beta');
        $schoolAdmin = $this->schoolUser($schoolA, 'school_admin', 'platform-admin@example.test');
        $superAdmin = $this->superAdmin();

        $this->actAsSchoolRole($schoolAdmin, $schoolA, 'school_admin');
        $this->get(route('admin.schools.index'))->assertForbidden();

        $this->actingAs($superAdmin);
        $this->get(route('admin.schools.index'))
            ->assertOk()
            ->assertSee($schoolA->name)
            ->assertSee($schoolB->name);
    }

    private function school(string $name, string $slug): School
    {
        return School::create([
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function academicContext(School $school, string $className, string $admissionNumber): array
    {
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'name' => $className,
            'section' => 'A',
            'status' => 'active',
        ]);
        $session = AcademicSession::create([
            'school_id' => $school->id,
            'name' => '2025/2026',
            'status' => 'active',
            'is_active' => true,
        ]);
        $term = Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'status' => 'active',
            'is_active' => true,
        ]);
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics '.$admissionNumber,
            'code' => 'MTH'.$school->id,
            'status' => 'active',
        ]);
        $student = Student::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => $admissionNumber,
            'first_name' => 'Tenant',
            'last_name' => $admissionNumber,
            'guardian_email' => strtolower($admissionNumber).'@example.test',
            'status' => 'active',
        ]);

        return compact('school', 'class', 'session', 'term', 'subject', 'student');
    }

    private function studentResult(array $context, string $status): StudentResult
    {
        return StudentResult::create([
            'school_id' => $context['school']->id,
            'student_id' => $context['student']->id,
            'school_class_id' => $context['class']->id,
            'subject_id' => $context['subject']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'result_type' => 'term_result',
            'ca_score' => 30,
            'exam_score' => 50,
            'total_score' => 80,
            'grade' => 'A',
            'status' => $status,
        ]);
    }

    private function resultPublication(array $context, User $actor, string $label): ResultPublication
    {
        return ResultPublication::create([
            'school_id' => $context['school']->id,
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'result_type' => 'term_result',
            'scope_type' => 'class',
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $actor->id,
            'created_by' => $actor->id,
            'unpublish_reason' => $label,
        ]);
    }

    private function scratchBatch(array $context, User $actor): ScratchCardBatch
    {
        return ScratchCardBatch::create([
            'batch_code' => 'BATCH-'.$context['school']->id,
            'school_id' => $context['school']->id,
            'requested_by' => $actor->id,
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'result_type' => 'term_result',
            'title' => 'Batch '.$context['school']->id,
            'quantity' => 10,
            'amount' => 0,
            'currency' => 'NGN',
            'payment_status' => 'pending',
            'status' => 'pending_payment',
        ]);
    }

    private function supportThread(School $school, User $creator, string $subject): SupportThread
    {
        $thread = SupportThread::create([
            'school_id' => $school->id,
            'created_by' => $creator->id,
            'creator_role' => 'school_admin',
            'routed_to_role' => SupportThread::ROUTE_SUPER_ADMIN,
            'subject' => $subject,
            'category' => 'technical_issue',
            'priority' => 'normal',
            'status' => SupportThread::STATUS_ESCALATED,
            'visibility' => SupportThread::VISIBILITY_ESCALATED,
            'escalation_level' => 1,
            'last_message_at' => now(),
        ]);

        SupportMessage::create([
            'support_thread_id' => $thread->id,
            'school_id' => $school->id,
            'sender_id' => $creator->id,
            'sender_role' => 'school_admin',
            'message' => $subject,
            'is_internal_note' => false,
        ]);

        return $thread;
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

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

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
