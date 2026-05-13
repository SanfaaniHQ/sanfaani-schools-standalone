<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherClassAssignment;
use App\Models\TeacherSubjectAssignment;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\TeacherAssignmentAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TeacherAssignmentArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_assignment_access_supports_multiple_class_and_subject_modes(): void
    {
        $school = $this->createSchool();
        $classA = $this->createClass($school, 'JSS 1', 'A');
        $classB = $this->createClass($school, 'JSS 2', 'A');
        $math = $this->createSubject($school, 'Mathematics', 'MTH');
        $english = $this->createSubject($school, 'English', 'ENG');
        $teacher = $this->createUserForSchool($school, 'teacher');

        TeacherClassAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'school_class_id' => $classA->id,
            'role_type' => 'class_teacher',
            'status' => 'active',
        ]);

        TeacherSubjectAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'subject_id' => $math->id,
            'school_class_id' => $classB->id,
            'role_type' => 'subject_teacher',
            'status' => 'active',
        ]);

        $service = app(TeacherAssignmentAccessService::class);

        $this->assertEqualsCanonicalizing([$classA->id, $classB->id], $service->visibleClassIds($school, $teacher)->all());
        $this->assertTrue($service->subjectsForTeacher($school, $teacher, $classA->id)->pluck('id')->contains($english->id));
        $this->assertEquals([$math->id], $service->subjectsForTeacher($school, $teacher, $classB->id)->pluck('id')->all());
        $this->assertTrue($service->canTeach($school, $teacher, $classA->id, $english->id));
        $this->assertTrue($service->canTeach($school, $teacher, $classB->id, $math->id));
    }

    public function test_general_subject_assignment_expands_visible_classes_without_single_class_assumption(): void
    {
        $school = $this->createSchool();
        $classA = $this->createClass($school, 'SSS 1', 'A');
        $classB = $this->createClass($school, 'SSS 2', 'A');
        $subject = $this->createSubject($school, 'Biology', 'BIO');
        $teacher = $this->createUserForSchool($school, 'teacher');

        TeacherSubjectAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => null,
            'role_type' => 'co_teacher',
            'status' => 'active',
        ]);

        $classIds = app(TeacherAssignmentAccessService::class)->visibleClassIds($school, $teacher);

        $this->assertEqualsCanonicalizing([$classA->id, $classB->id], $classIds->all());
    }

    public function test_overlapping_subject_assignment_duplicates_are_rejected(): void
    {
        [$school, $admin, $teacher, $class, $subject, $session, $term] = $this->assignmentContext();
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->post(route('school.teacher-assignments.store'), [
            'assignment_scope' => 'subject',
            'teacher_user_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => null,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'role_type' => 'subject_teacher',
            'status' => 'active',
        ])->assertRedirect(route('school.teacher-assignments.index'));

        $this->from(route('school.teacher-assignments.create'))->post(route('school.teacher-assignments.store'), [
            'assignment_scope' => 'subject',
            'teacher_user_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'role_type' => 'subject_teacher',
            'status' => 'active',
        ])->assertRedirect(route('school.teacher-assignments.create'))
            ->assertSessionHasErrors('teacher_user_id');

        $this->assertSame(1, TeacherSubjectAssignment::where('school_id', $school->id)->count());
    }

    public function test_restore_is_blocked_when_it_would_overlap_an_active_assignment(): void
    {
        [$school, $admin, $teacher, $class] = $this->assignmentContext();
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $archived = TeacherClassAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'school_class_id' => $class->id,
            'role_type' => 'class_teacher',
            'status' => 'archived',
            'ends_at' => today(),
        ]);
        $archived->delete();

        TeacherClassAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'school_class_id' => $class->id,
            'role_type' => 'co_teacher',
            'status' => 'active',
        ]);

        $this->post(route('school.teacher-assignments.restore', $archived), [
            'type' => 'class',
        ])->assertSessionHasErrors('teacher_user_id');

        $this->assertSoftDeleted('teacher_class_assignments', ['id' => $archived->id]);
    }

    private function assignmentContext(): array
    {
        $school = $this->createSchool();
        $admin = $this->createUserForSchool($school, 'school_admin');
        $teacher = $this->createUserForSchool($school, 'teacher');
        $class = $this->createClass($school, 'JSS 1', 'A');
        $subject = $this->createSubject($school, 'Mathematics', 'MTH');
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

        return [$school, $admin, $teacher, $class, $subject, $session, $term];
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

    private function createSubject(School $school, string $name, string $code): Subject
    {
        return Subject::create([
            'school_id' => $school->id,
            'name' => $name,
            'code' => $code,
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
