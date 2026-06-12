<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\AuditLog;
use App\Models\ClassSubjectAssignment;
use App\Models\LmsClassroom;
use App\Models\LmsMaterial;
use App\Models\LmsResource;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LmsFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'result_officer', 'accountant', 'parent', 'student'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_lms_routes_require_authentication(): void
    {
        $this->get(route('school.lms.index'))
            ->assertRedirect(route('login'));

        $this->post(route('school.lms.classrooms.store'), [])
            ->assertRedirect(route('login'));
    }

    public function test_school_admin_can_access_lms_dashboard_and_create_classroom(): void
    {
        $context = $this->lmsContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.lms.index'))
            ->assertOk()
            ->assertSee('Learning Materials')
            ->assertSee('Stage 15 Boundary')
            ->assertSee('private local storage');

        $this->post(route('school.lms.classrooms.store'), [
            'school_class_id' => $context['class']->id,
            'subject_id' => $context['subject']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'title' => 'JSS 1 Mathematics',
            'description' => 'Number theory and weekly notes',
        ])->assertRedirect();

        $this->assertDatabaseHas('lms_classrooms', [
            'school_id' => $context['school']->id,
            'school_class_id' => $context['class']->id,
            'subject_id' => $context['subject']->id,
            'title' => 'JSS 1 Mathematics',
            'status' => LmsClassroom::STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $context['school']->id,
            'action' => 'lms_classroom_created',
        ]);
    }

    public function test_teacher_can_access_lms_and_manage_material_only_for_assigned_scope(): void
    {
        $context = $this->lmsContext('teacher');
        $this->assignTeacherToSubject($context['school'], $context['user'], $context['class'], $context['subject'], $context['session'], $context['term']);
        $classroom = $this->createClassroom($context);

        $this->actAsSchoolRole($context['user'], $context['school'], 'teacher');

        $this->get(route('school.lms.index'))
            ->assertOk()
            ->assertSee('Learning Materials')
            ->assertSee($classroom->title);

        $this->post(route('school.lms.materials.store', $classroom), [
            'title' => 'Week 1 Lesson',
            'type' => LmsMaterial::TYPE_LESSON,
            'body' => 'Introduction to place value.',
        ])->assertRedirect();

        $this->assertDatabaseHas('lms_materials', [
            'school_id' => $context['school']->id,
            'lms_classroom_id' => $classroom->id,
            'teacher_user_id' => $context['user']->id,
            'title' => 'Week 1 Lesson',
            'status' => LmsMaterial::STATUS_DRAFT,
        ]);
    }

    public function test_teacher_cannot_manage_unassigned_lms_scope(): void
    {
        $context = $this->lmsContext('teacher');
        $classroom = $this->createClassroom($context);
        $this->actAsSchoolRole($context['user'], $context['school'], 'teacher');

        $this->post(route('school.lms.materials.store', $classroom), [
            'title' => 'Unassigned Lesson',
            'type' => LmsMaterial::TYPE_LESSON,
        ])->assertForbidden();

        $this->assertDatabaseCount('lms_materials', 0);
    }

    public function test_accountant_and_result_officer_cannot_access_or_manage_lms(): void
    {
        $context = $this->lmsContext('school_admin');

        foreach (['accountant', 'result_officer'] as $role) {
            $user = $this->createUserForSchool($context['school'], $role);
            $this->actAsSchoolRole($user, $context['school'], $role);

            $this->get(route('school.lms.index'))->assertForbidden();
            $this->post(route('school.lms.classrooms.store'), [
                'school_class_id' => $context['class']->id,
                'subject_id' => $context['subject']->id,
                'title' => 'Blocked LMS Classroom',
            ])->assertForbidden();
        }

        $this->assertDatabaseCount('lms_classrooms', 0);
    }

    public function test_cross_school_lms_records_are_blocked(): void
    {
        $context = $this->lmsContext('school_admin');
        $other = $this->lmsContext('school_admin');
        $foreignClassroom = $this->createClassroom($other);
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.lms.classrooms.show', $foreignClassroom))
            ->assertForbidden();
    }

    public function test_duplicate_classroom_scope_is_rejected_without_creating_duplicate(): void
    {
        $context = $this->lmsContext('school_admin');
        $this->createClassroom($context);
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->from(route('school.lms.index'))
            ->post(route('school.lms.classrooms.store'), [
                'school_class_id' => $context['class']->id,
                'subject_id' => $context['subject']->id,
                'academic_session_id' => $context['session']->id,
                'term_id' => $context['term']->id,
                'title' => 'Duplicate Classroom',
            ])
            ->assertRedirect(route('school.lms.index'))
            ->assertSessionHasErrors('school_class_id');

        $this->assertDatabaseCount('lms_classrooms', 1);
    }

    public function test_material_publish_and_unpublish_workflow_is_audited(): void
    {
        $context = $this->lmsContext('school_admin');
        $classroom = $this->createClassroom($context);
        $material = $this->createMaterial($context, $classroom);
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.lms.materials.publish', $material))->assertRedirect();
        $this->assertSame(LmsMaterial::STATUS_PUBLISHED, $material->fresh()->status);
        $this->assertNotNull($material->fresh()->published_at);

        $this->post(route('school.lms.materials.unpublish', $material))->assertRedirect();
        $this->assertSame(LmsMaterial::STATUS_DRAFT, $material->fresh()->status);

        $this->assertDatabaseHas('audit_logs', ['action' => 'lms_material_published']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'lms_material_unpublished']);
    }

    public function test_unpublished_material_is_not_visible_to_unassigned_teacher(): void
    {
        $context = $this->lmsContext('school_admin');
        $classroom = $this->createClassroom($context);
        $material = $this->createMaterial($context, $classroom);
        $teacher = $this->createUserForSchool($context['school'], 'teacher');
        $this->actAsSchoolRole($teacher, $context['school'], 'teacher');

        $this->get(route('school.lms.materials.show', $material))
            ->assertForbidden();
    }

    public function test_resource_upload_validates_type_and_stores_private_metadata_without_raw_path_in_ui(): void
    {
        Storage::fake('local');
        $context = $this->lmsContext('school_admin');
        $classroom = $this->createClassroom($context);
        $material = $this->createMaterial($context, $classroom);
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->from(route('school.lms.materials.show', $material))
            ->post(route('school.lms.resources.store', $material), [
                'resource' => UploadedFile::fake()->create('unsafe.exe', 10, 'application/x-msdownload'),
            ])
            ->assertRedirect(route('school.lms.materials.show', $material))
            ->assertSessionHasErrors('resource');

        $this->post(route('school.lms.resources.store', $material), [
            'resource' => UploadedFile::fake()->create('lesson.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $resource = LmsResource::firstOrFail();
        Storage::disk('local')->assertExists($resource->path);
        $this->assertStringStartsWith('lms/schools/'.$context['school']->id.'/materials/'.$material->id.'/', $resource->path);

        $this->get(route('school.lms.materials.show', $material))
            ->assertOk()
            ->assertSee($resource->original_name)
            ->assertDontSee($resource->path);
    }

    public function test_resource_download_requires_authorization_and_audits_download(): void
    {
        Storage::fake('local');
        $context = $this->lmsContext('school_admin');
        $classroom = $this->createClassroom($context);
        $material = $this->createMaterial($context, $classroom);
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');
        $this->post(route('school.lms.resources.store', $material), [
            'resource' => UploadedFile::fake()->create('lesson.pdf', 100, 'application/pdf'),
        ])->assertRedirect();
        $resource = LmsResource::firstOrFail();

        $teacher = $this->createUserForSchool($context['school'], 'teacher');
        $this->actAsSchoolRole($teacher, $context['school'], 'teacher');
        $this->get(route('school.lms.resources.download', $resource))->assertForbidden();

        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');
        $this->get(route('school.lms.resources.download', $resource))->assertOk();

        $this->assertDatabaseHas('audit_logs', ['action' => 'lms_resource_downloaded']);
    }

    public function test_lms_links_appear_only_for_allowed_school_roles(): void
    {
        $context = $this->lmsContext('school_admin');

        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');
        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Learning Materials');

        $teacher = $this->createUserForSchool($context['school'], 'teacher');
        $this->actAsSchoolRole($teacher, $context['school'], 'teacher');
        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Learning Materials');

        foreach (['accountant', 'result_officer'] as $role) {
            $user = $this->createUserForSchool($context['school'], $role);
            $this->actAsSchoolRole($user, $context['school'], $role);
            $this->get(route('school.dashboard'))
                ->assertOk()
                ->assertDontSee('Learning Materials');
        }
    }

    public function test_lms_boundaries_are_visible_and_student_portal_is_deferred(): void
    {
        $context = $this->lmsContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.lms.index'))
            ->assertOk()
            ->assertSee('Student LMS viewing is deferred')
            ->assertSee('Existing CBT items')
            ->assertSee('Live classes')
            ->assertSee('offline LMS')
            ->assertSee('submissions/grading')
            ->assertDontSee(route('school.lms.index').'/student');
    }

    private function lmsContext(string $role): array
    {
        $school = $this->createSchool();
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'JSS 1',
            'section' => fake()->unique()->lexify('A??'),
            'status' => 'active',
        ]);
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics '.fake()->unique()->numberBetween(1, 9999),
            'code' => fake()->unique()->lexify('MTH???'),
            'status' => 'active',
            'assignment_type' => 'core',
            'is_elective' => false,
        ]);
        $session = AcademicSession::create([
            'school_id' => $school->id,
            'name' => fake()->unique()->numerify('2026/2027 ###'),
            'is_active' => true,
            'status' => 'active',
        ]);
        $term = Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'First Term '.fake()->unique()->numberBetween(1, 9999),
            'is_active' => true,
            'status' => 'active',
        ]);
        ClassSubjectAssignment::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'assignment_type' => 'core',
            'is_elective' => false,
            'is_required' => true,
            'status' => 'active',
        ]);
        $user = $this->createUserForSchool($school, $role);

        return compact('school', 'class', 'subject', 'session', 'term', 'user');
    }

    private function createSchool(): School
    {
        return School::create([
            'name' => 'Sanfaani LMS Academy '.fake()->unique()->numberBetween(1, 9999),
            'slug' => fake()->unique()->slug(),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function createUserForSchool(School $school, string $role): User
    {
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

    private function assignTeacherToSubject(
        School $school,
        User $teacher,
        SchoolClass $class,
        Subject $subject,
        AcademicSession $session,
        Term $term
    ): void {
        TeacherSubjectAssignment::create([
            'school_id' => $school->id,
            'teacher_user_id' => $teacher->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'role_type' => 'subject_teacher',
            'status' => 'active',
        ]);
    }

    private function createClassroom(array $context): LmsClassroom
    {
        return LmsClassroom::create([
            'school_id' => $context['school']->id,
            'school_class_id' => $context['class']->id,
            'subject_id' => $context['subject']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'title' => 'JSS 1 Mathematics LMS',
            'status' => LmsClassroom::STATUS_ACTIVE,
            'created_by' => $context['user']->id,
            'updated_by' => $context['user']->id,
        ]);
    }

    private function createMaterial(array $context, LmsClassroom $classroom): LmsMaterial
    {
        return LmsMaterial::create([
            'school_id' => $context['school']->id,
            'lms_classroom_id' => $classroom->id,
            'teacher_user_id' => $context['user']->id,
            'title' => 'Draft Lesson',
            'body' => 'Draft material body.',
            'type' => LmsMaterial::TYPE_LESSON,
            'status' => LmsMaterial::STATUS_DRAFT,
            'created_by' => $context['user']->id,
            'updated_by' => $context['user']->id,
        ]);
    }
}
