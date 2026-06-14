<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\AuditLog;
use App\Models\ClassSubjectAssignment;
use App\Models\LiveClass;
use App\Models\LmsClassroom;
use App\Models\LmsMaterial;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\LiveClasses\LiveClassAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LiveClassFoundationTest extends TestCase
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

    public function test_live_class_routes_require_authentication(): void
    {
        $this->get(route('school.live-classes.index'))
            ->assertRedirect(route('login'));

        $this->post(route('school.live-classes.store'), [])
            ->assertRedirect(route('login'));
    }

    public function test_school_admin_can_access_live_class_dashboard_and_schedule_session(): void
    {
        $context = $this->liveClassContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.live-classes.index'))
            ->assertOk()
            ->assertSee('Live Classes')
            ->assertSee('Manual meeting links')
            ->assertSee('offline live class');

        $this->post(route('school.live-classes.store'), $this->validPayload($context, [
            'title' => 'Algebra Live Revision',
            'meeting_password' => 'ClassSecret123',
        ]))->assertRedirect();

        $this->assertDatabaseHas('live_classes', [
            'school_id' => $context['school']->id,
            'school_class_id' => $context['class']->id,
            'subject_id' => $context['subject']->id,
            'lms_classroom_id' => $context['classroom']->id,
            'lms_material_id' => $context['material']->id,
            'title' => 'Algebra Live Revision',
            'provider' => LiveClass::PROVIDER_MANUAL,
            'status' => LiveClass::STATUS_SCHEDULED,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $context['school']->id,
            'action' => 'live_class_created',
        ]);
    }

    public function test_teacher_can_access_and_schedule_live_class_only_for_assigned_scope(): void
    {
        $context = $this->liveClassContext('teacher');
        $this->assignTeacherToSubject($context);
        $this->actAsSchoolRole($context['user'], $context['school'], 'teacher');

        $this->get(route('school.live-classes.index'))
            ->assertOk()
            ->assertSee('Live Classes');

        $this->post(route('school.live-classes.store'), $this->validPayload($context, [
            'teacher_user_id' => null,
        ]))->assertRedirect();

        $this->assertDatabaseHas('live_classes', [
            'school_id' => $context['school']->id,
            'teacher_user_id' => $context['user']->hasRole('teacher') ? $context['user']->id : null,
            'created_by' => $context['user']->id,
            'status' => LiveClass::STATUS_SCHEDULED,
        ]);
    }

    public function test_teacher_cannot_schedule_unassigned_class_or_subject(): void
    {
        $context = $this->liveClassContext('teacher');
        $this->actAsSchoolRole($context['user'], $context['school'], 'teacher');

        $this->post(route('school.live-classes.store'), $this->validPayload($context))
            ->assertForbidden();

        $this->assertDatabaseCount('live_classes', 0);
    }

    public function test_accountant_and_result_officer_cannot_manage_live_classes(): void
    {
        $context = $this->liveClassContext('school_admin');

        foreach (['accountant', 'result_officer'] as $role) {
            $user = $this->createUserForSchool($context['school'], $role);
            $this->actAsSchoolRole($user, $context['school'], $role);

            $this->get(route('school.live-classes.index'))->assertForbidden();
            $this->post(route('school.live-classes.store'), $this->validPayload($context))->assertForbidden();
        }

        $this->assertDatabaseCount('live_classes', 0);
    }

    public function test_cross_school_live_classes_are_blocked(): void
    {
        $context = $this->liveClassContext('school_admin');
        $other = $this->liveClassContext('school_admin');
        $foreignLiveClass = $this->createLiveClass($other);
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.live-classes.show', $foreignLiveClass))
            ->assertForbidden();
    }

    public function test_invalid_meeting_url_and_end_time_before_start_are_rejected(): void
    {
        $context = $this->liveClassContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->from(route('school.live-classes.create'))
            ->post(route('school.live-classes.store'), $this->validPayload($context, [
                'meeting_url' => 'javascript:alert(1)',
                'ends_at' => now()->addMinutes(10)->format('Y-m-d H:i:s'),
                'starts_at' => now()->addHour()->format('Y-m-d H:i:s'),
            ]))
            ->assertRedirect(route('school.live-classes.create'))
            ->assertSessionHasErrors(['meeting_url', 'ends_at']);

        $this->assertDatabaseCount('live_classes', 0);
    }

    public function test_live_class_can_link_same_school_lms_context_and_rejects_cross_school_lms_links(): void
    {
        $context = $this->liveClassContext('school_admin');
        $other = $this->liveClassContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.live-classes.store'), $this->validPayload($context))
            ->assertRedirect();

        $this->assertDatabaseHas('live_classes', [
            'school_id' => $context['school']->id,
            'lms_classroom_id' => $context['classroom']->id,
            'lms_material_id' => $context['material']->id,
        ]);

        $this->from(route('school.live-classes.create'))
            ->post(route('school.live-classes.store'), $this->validPayload($context, [
                'title' => 'Cross School LMS Link',
                'lms_classroom_id' => $other['classroom']->id,
                'lms_material_id' => $other['material']->id,
            ]))
            ->assertRedirect(route('school.live-classes.create'))
            ->assertSessionHasErrors(['lms_classroom_id', 'lms_material_id']);

        $this->assertDatabaseCount('live_classes', 1);
    }

    public function test_status_workflow_is_audited_and_cancelled_session_cannot_start(): void
    {
        $context = $this->liveClassContext('school_admin');
        $liveClass = $this->createLiveClass($context);
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.live-classes.start', $liveClass))->assertRedirect();
        $this->assertSame(LiveClass::STATUS_LIVE, $liveClass->fresh()->status);

        $this->post(route('school.live-classes.complete', $liveClass))->assertRedirect();
        $this->assertSame(LiveClass::STATUS_COMPLETED, $liveClass->fresh()->status);

        $cancelled = $this->createLiveClass($context, ['title' => 'Cancelled Revision']);
        $this->post(route('school.live-classes.cancel', $cancelled))->assertRedirect();
        $this->assertSame(LiveClass::STATUS_CANCELLED, $cancelled->fresh()->status);

        $this->from(route('school.live-classes.show', $cancelled))
            ->post(route('school.live-classes.start', $cancelled))
            ->assertRedirect(route('school.live-classes.show', $cancelled))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('audit_logs', ['action' => 'live_class_started']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'live_class_completed']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'live_class_cancelled']);
    }

    public function test_meeting_password_is_authorized_view_only_and_not_exposed_in_audit_metadata(): void
    {
        $context = $this->liveClassContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.live-classes.store'), $this->validPayload($context, [
            'meeting_password' => 'SecretPass-Do-Not-Audit',
        ]))->assertRedirect();

        $liveClass = LiveClass::firstOrFail();
        $this->get(route('school.live-classes.show', $liveClass))
            ->assertOk()
            ->assertSee('SecretPass-Do-Not-Audit');

        $audit = AuditLog::query()->where('action', 'live_class_created')->firstOrFail();
        $this->assertArrayNotHasKey('meeting_password', $audit->metadata ?? []);
        $this->assertStringNotContainsString('SecretPass-Do-Not-Audit', json_encode($audit->toArray()));
    }

    public function test_dashboard_and_sidebar_links_appear_only_for_allowed_roles(): void
    {
        $context = $this->liveClassContext('school_admin');

        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');
        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Live Classes')
            ->assertSee('manual internet meeting links');

        $teacher = $this->createUserForSchool($context['school'], 'teacher');
        $teacherContext = array_merge($context, ['user' => $teacher]);
        $this->assignTeacherToSubject($teacherContext);
        $this->actAsSchoolRole($teacher, $context['school'], 'teacher');
        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Live Classes')
            ->assertSee('manual internet sessions');

        foreach (['accountant', 'result_officer'] as $role) {
            $user = $this->createUserForSchool($context['school'], $role);
            $this->actAsSchoolRole($user, $context['school'], $role);
            $this->get(route('school.dashboard'))
                ->assertOk()
                ->assertDontSee('Live Classes');
        }
    }

    public function test_standalone_dashboard_marks_live_class_foundation_available_and_deferred_boundaries_visible(): void
    {
        config([
            'standalone.product_edition' => 'standalone',
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        $context = $this->liveClassContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Live classes')
            ->assertSee('Manual meeting links')
            ->assertSee('Provider setup is available')
            ->assertSee('Provider API automation remains deferred')
            ->assertSee('Offline live class is not implemented');
    }

    public function test_student_and_parent_live_class_visibility_remains_deferred(): void
    {
        $access = app(LiveClassAccessService::class);

        $this->assertFalse($access->studentPortalIsSafe());
        $this->assertFalse($access->parentPortalIsSafe());
        $this->assertStringContainsString('deferred', $access->studentPortalBoundaryNote());
        $this->assertStringContainsString('deferred', $access->parentPortalBoundaryNote());
    }

    private function liveClassContext(string $role): array
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
        $classroom = LmsClassroom::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'title' => 'JSS 1 Mathematics LMS',
            'status' => LmsClassroom::STATUS_ACTIVE,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        $material = LmsMaterial::create([
            'school_id' => $school->id,
            'lms_classroom_id' => $classroom->id,
            'teacher_user_id' => $user->id,
            'title' => 'Live Class Prep Material',
            'body' => 'Prep notes for the live class.',
            'type' => LmsMaterial::TYPE_LESSON,
            'status' => LmsMaterial::STATUS_DRAFT,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return compact('school', 'class', 'subject', 'session', 'term', 'user', 'classroom', 'material');
    }

    private function validPayload(array $context, array $overrides = []): array
    {
        return array_merge([
            'school_class_id' => $context['class']->id,
            'subject_id' => $context['subject']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'lms_classroom_id' => $context['classroom']->id,
            'lms_material_id' => $context['material']->id,
            'teacher_user_id' => $context['user']->hasRole('teacher') ? $context['user']->id : null,
            'title' => 'JSS 1 Mathematics Live Class',
            'description' => 'Weekly revision session.',
            'meeting_url' => 'https://meet.example.test/jss-1-math',
            'meeting_password' => null,
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
            'timezone' => 'Africa/Lagos',
            'recording_url' => 'https://recordings.example.test/jss-1-math',
            'reminder_minutes' => 30,
        ], $overrides);
    }

    private function createLiveClass(array $context, array $overrides = []): LiveClass
    {
        return LiveClass::create(array_merge([
            'school_id' => $context['school']->id,
            'school_class_id' => $context['class']->id,
            'subject_id' => $context['subject']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'lms_classroom_id' => $context['classroom']->id,
            'lms_material_id' => $context['material']->id,
            'teacher_user_id' => $context['user']->id,
            'title' => 'JSS 1 Mathematics Live Class',
            'description' => 'Weekly revision session.',
            'provider' => LiveClass::PROVIDER_MANUAL,
            'meeting_url' => 'https://meet.example.test/jss-1-math',
            'meeting_password' => 'VisibleToAuthorizedOnly',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'timezone' => 'Africa/Lagos',
            'status' => LiveClass::STATUS_SCHEDULED,
            'recording_url' => null,
            'created_by' => $context['user']->id,
            'updated_by' => $context['user']->id,
            'metadata' => ['reminder_minutes' => 30, 'internet_required' => true],
        ], $overrides));
    }

    private function assignTeacherToSubject(array $context): void
    {
        TeacherSubjectAssignment::create([
            'school_id' => $context['school']->id,
            'teacher_user_id' => $context['user']->id,
            'subject_id' => $context['subject']->id,
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'role_type' => 'subject_teacher',
            'status' => 'active',
        ]);
    }

    private function createSchool(): School
    {
        $id = fake()->unique()->numberBetween(1, 999999);

        return School::create([
            'name' => 'Sanfaani Live Class Academy '.$id,
            'slug' => 'sanfaani-live-class-academy-'.$id,
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
}
