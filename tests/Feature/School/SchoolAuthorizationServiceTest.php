<?php

namespace Tests\Feature\School;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolFeatureOverride;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherClassAssignment;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\SchoolAuthorizationService;
use App\Services\SchoolRoleFeatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SchoolAuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_role_feature_resolution_uses_catalog_settings_and_aliases(): void
    {
        $school = $this->createSchool();
        $teacher = $this->createUserForSchool($school, 'teacher');
        $this->actAsSchoolRole($teacher, $school, 'teacher');

        $authorization = app(SchoolAuthorizationService::class);

        $this->assertFalse($authorization->can($teacher, $school, 'communication.bulk'));
        $this->assertFalse($authorization->can($teacher, $school, 'results.publish'));

        app(SchoolRoleFeatureService::class)->setFeature($school->id, 'teacher', 'support.access', false);

        $this->assertFalse(app(SchoolAuthorizationService::class)->can($teacher, $school, 'support.manage'));
    }

    public function test_multi_role_users_resolve_permissions_from_active_role_context(): void
    {
        $school = $this->createSchool();
        $user = $this->createUserForSchool($school, 'teacher');
        Role::findOrCreate('result_officer');
        $user->assignRole('result_officer');
        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => 'result_officer',
            'status' => 'active',
        ]);

        $this->actAsSchoolRole($user, $school, 'teacher');
        $this->assertFalse(app(SchoolAuthorizationService::class)->can($user, $school, 'results.publish'));

        session(['active_role_context' => 'result_officer']);
        $this->assertTrue(app(SchoolAuthorizationService::class)->can($user, $school, 'results.publish'));
    }

    public function test_h2_h10_route_feature_aliases_resolve_for_default_school_roles(): void
    {
        $school = $this->createSchool();
        $admin = $this->createUserForSchool($school, 'school_admin');
        $teacher = $this->createUserForSchool($school, 'teacher');
        $parent = $this->createUserForSchool($school, 'parent');

        $authorization = app(SchoolAuthorizationService::class);

        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $this->assertTrue($authorization->can($admin, $school, 'school.scratch-cards.generate'));
        $this->assertTrue($authorization->can($admin, $school, 'school.result-system.index'));
        $this->assertTrue($authorization->can($admin, $school, 'school.feature-control.index'));
        $this->assertTrue($authorization->can($admin, $school, 'school.teacher-reviews.index'));

        $this->actAsSchoolRole($teacher, $school, 'teacher');
        $this->assertTrue($authorization->can($teacher, $school, 'school.live-classes.create'));
        $this->assertTrue($authorization->can($teacher, $school, 'portal.conversations.index'));

        $this->actAsSchoolRole($parent, $school, 'parent');
        $this->assertTrue($authorization->can($parent, $school, 'portal.live-classes.join'));
        $this->assertTrue($authorization->can($parent, $school, 'portal.teacher-reviews.store'));
        $this->assertTrue($authorization->can($parent, $school, 'portal.conversations.messages.store'));
    }

    public function test_teacher_student_visibility_is_assignment_and_school_scoped(): void
    {
        $school = $this->createSchool();
        $otherSchool = $this->createSchool('Other School', 'other-school');
        $classA = $this->createClass($school, 'JSS 1', 'A');
        $classB = $this->createClass($school, 'JSS 1', 'B');
        $otherClass = $this->createClass($otherSchool, 'JSS 1', 'A');
        $teacher = $this->createUserForSchool($school, 'teacher');
        $this->actAsSchoolRole($teacher, $school, 'teacher');

        TeacherClassAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'school_class_id' => $classA->id,
            'status' => 'active',
        ]);

        $assignedStudent = $this->createStudent($school, $classA, 'ADM-001');
        $unassignedStudent = $this->createStudent($school, $classB, 'ADM-002');
        $otherSchoolStudent = $this->createStudent($otherSchool, $otherClass, 'ADM-003');
        $authorization = app(SchoolAuthorizationService::class);

        $this->assertTrue($authorization->canViewStudent($teacher, $school, $assignedStudent));
        $this->assertFalse($authorization->canViewStudent($teacher, $school, $unassignedStudent));
        $this->assertFalse($authorization->canViewStudent($teacher, $school, $otherSchoolStudent));
    }

    public function test_general_subject_assignment_allows_teacher_to_view_school_classes(): void
    {
        $school = $this->createSchool();
        $classA = $this->createClass($school, 'SSS 1', 'A');
        $classB = $this->createClass($school, 'SSS 2', 'A');
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MTH',
            'status' => 'active',
        ]);
        $teacher = $this->createUserForSchool($school, 'teacher');
        $this->actAsSchoolRole($teacher, $school, 'teacher');

        TeacherSubjectAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => null,
            'status' => 'active',
        ]);

        $classIds = app(SchoolAuthorizationService::class)->teacherVisibleClassIds($teacher, $school);

        $this->assertTrue($classIds->contains($classA->id));
        $this->assertTrue($classIds->contains($classB->id));
    }

    public function test_explicit_school_feature_disable_blocks_school_admin_but_not_super_admin(): void
    {
        $school = $this->createSchool();
        $admin = $this->createUserForSchool($school, 'school_admin');
        $superAdmin = User::factory()->create();
        Role::findOrCreate('super_admin');
        $superAdmin->assignRole('super_admin');

        SchoolFeatureOverride::create([
            'school_id' => $school->id,
            'feature_key' => 'result_publishing',
            'is_enabled' => false,
        ]);
        SchoolFeatureOverride::create([
            'school_id' => $school->id,
            'feature_key' => 'support.access',
            'is_enabled' => false,
        ]);

        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $this->assertFalse(app(SchoolAuthorizationService::class)->can($admin, $school, 'results.publish'));
        $this->assertFalse(app(SchoolAuthorizationService::class)->can($admin, $school, 'support.manage'));

        $this->actingAs($superAdmin);
        $this->assertTrue(app(SchoolAuthorizationService::class)->can($superAdmin, $school, 'results.publish'));
        $this->assertTrue(app(SchoolAuthorizationService::class)->can($superAdmin, $school, 'support.manage'));
    }

    private function createSchool(string $name = 'Sanfaani School', string $slug = 'sanfaani-school'): School
    {
        return School::create([
            'name' => $name,
            'slug' => $slug,
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

    private function createStudent(School $school, SchoolClass $class, string $admissionNumber): Student
    {
        return Student::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => $admissionNumber,
            'first_name' => 'Ada',
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
