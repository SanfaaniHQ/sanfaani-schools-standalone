<?php

namespace Tests\Feature\School;

use App\Enums\ResultWorkflowStatus;
use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ResultPublishingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_single_unpublish_json_clears_publication_state_and_preserves_inline_return_url(): void
    {
        $school = $this->createSchool();
        $admin = $this->createUserForSchool($school, 'school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $class = $this->createClass($school);
        $session = $this->createSession($school);
        $term = $this->createTerm($school, $session);
        $subject = $this->createSubject($school);
        $student = $this->createStudent($school, $class);
        $publishedAt = now()->subHour();

        $result = StudentResult::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'ca_score' => 30,
            'exam_score' => 55,
            'total_score' => 85,
            'grade' => 'A',
            'remark' => 'Excellent',
            'status' => ResultWorkflowStatus::Published->value,
            'published_at' => $publishedAt,
            'published_by' => $admin->id,
            'recorded_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $returnUrl = route('school.students.show', $student).'#result-profile';

        $this->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->post(route('school.results.publishing.unpublish-single', $result), [
            'unpublish_reason' => 'Correction required before parent access.',
            '_return_url' => $returnUrl,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('redirect_url', $returnUrl)
            ->assertJsonPath('result.status', ResultWorkflowStatus::Unpublished->value)
            ->assertJsonPath('result.is_published', false)
            ->assertJsonPath('result.published_at_label', 'Not published');

        $result->refresh();

        $this->assertSame(ResultWorkflowStatus::Unpublished->value, $result->status);
        $this->assertNull($result->published_at);
        $this->assertNull($result->published_by);
        $this->assertNotNull($result->unpublished_at);
        $this->assertSame($admin->id, $result->unpublished_by);
        $this->assertSame('Correction required before parent access.', $result->unpublish_reason);

        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'auditable_type' => StudentResult::class,
            'auditable_id' => $result->id,
            'action' => 'result_unpublished',
        ]);
    }

    public function test_single_publish_json_restores_publication_state_for_unpublished_result(): void
    {
        $school = $this->createSchool();
        $admin = $this->createUserForSchool($school, 'school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $class = $this->createClass($school);
        $session = $this->createSession($school);
        $term = $this->createTerm($school, $session);
        $subject = $this->createSubject($school);
        $student = $this->createStudent($school, $class);

        $result = StudentResult::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'ca_score' => 25,
            'exam_score' => 50,
            'total_score' => 75,
            'grade' => 'B',
            'remark' => 'Very good',
            'status' => ResultWorkflowStatus::Unpublished->value,
            'unpublished_at' => now()->subMinutes(30),
            'unpublished_by' => $admin->id,
            'unpublish_reason' => 'Temporary correction.',
            'recorded_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->withHeaders([
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->post(route('school.results.publishing.publish-single', $result), [
            '_return_url' => route('school.students.show', $student).'#result-profile',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('result.status', ResultWorkflowStatus::Published->value)
            ->assertJsonPath('result.is_published', true);

        $result->refresh();

        $this->assertSame(ResultWorkflowStatus::Published->value, $result->status);
        $this->assertNotNull($result->published_at);
        $this->assertSame($admin->id, $result->published_by);
        $this->assertNull($result->unpublished_at);
        $this->assertNull($result->unpublished_by);
        $this->assertNull($result->unpublish_reason);

        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'auditable_type' => StudentResult::class,
            'auditable_id' => $result->id,
            'action' => 'result_published',
        ]);
    }

    public function test_student_360_result_profile_renders_state_aware_publish_actions(): void
    {
        $school = $this->createSchool();
        $admin = $this->createUserForSchool($school, 'school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $class = $this->createClass($school);
        $session = $this->createSession($school);
        $term = $this->createTerm($school, $session);
        $subject = $this->createSubject($school);
        $student = $this->createStudent($school, $class);

        $result = StudentResult::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'result_type' => 'term_result',
            'ca_score' => 29,
            'exam_score' => 54,
            'total_score' => 83,
            'grade' => 'A',
            'remark' => 'Excellent',
            'status' => ResultWorkflowStatus::Published->value,
            'published_at' => now(),
            'published_by' => $admin->id,
            'recorded_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $this->get(route('school.students.show', [
            'student' => $student,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
        ]))
            ->assertOk()
            ->assertSee('Result Profile')
            ->assertSee('Unpublish')
            ->assertSee('View Audit Log');

        $result->update([
            'status' => ResultWorkflowStatus::Unpublished->value,
            'published_at' => null,
            'published_by' => null,
            'unpublished_at' => now(),
            'unpublished_by' => $admin->id,
        ]);

        $this->get(route('school.students.show', [
            'student' => $student,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
        ]))
            ->assertOk()
            ->assertSee('Publish')
            ->assertSee('Not published');
    }

    private function createSchool(): School
    {
        return School::create([
            'name' => 'Publishing Workflow Academy',
            'slug' => fake()->unique()->slug(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function createClass(School $school): SchoolClass
    {
        return SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'JSS 1',
            'section' => 'A',
            'status' => 'active',
        ]);
    }

    private function createSession(School $school): AcademicSession
    {
        return AcademicSession::create([
            'school_id' => $school->id,
            'name' => '2025/2026',
            'status' => 'active',
        ]);
    }

    private function createTerm(School $school, AcademicSession $session): Term
    {
        return Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'status' => 'active',
        ]);
    }

    private function createSubject(School $school): Subject
    {
        return Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MTH',
            'status' => 'active',
        ]);
    }

    private function createStudent(School $school, SchoolClass $class): Student
    {
        return Student::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => 'ADM-'.uniqid(),
            'first_name' => 'Amina',
            'last_name' => 'Student',
            'status' => 'active',
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
