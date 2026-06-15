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
use App\Services\LiveClasses\LiveClassProviderRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LiveClassProviderAbstractionTest extends TestCase
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

    public function test_provider_registry_resolves_manual_and_keeps_future_providers_disabled(): void
    {
        $registry = app(LiveClassProviderRegistry::class);
        $manual = $registry->resolve();
        $futureProviders = collect($registry->futureProviderSummaries());

        $this->assertSame(LiveClass::PROVIDER_MANUAL, $manual->key());
        $this->assertSame('Manual link', $manual->label());
        $this->assertTrue($manual->supportsManualLink());
        $this->assertFalse($manual->supportsAutoCreate());
        $this->assertFalse($manual->requiresCredentials());
        $this->assertSame([LiveClass::PROVIDER_MANUAL], collect($registry->selectableOptions())->pluck('key')->all());

        $this->assertEqualsCanonicalizing(
            [LiveClass::PROVIDER_GOOGLE_MEET, LiveClass::PROVIDER_ZOOM, LiveClass::PROVIDER_MICROSOFT_TEAMS],
            $futureProviders->pluck('key')->all()
        );
        $this->assertTrue($futureProviders->every(fn (array $provider): bool => $provider['enabled'] === false));
        $this->assertTrue($futureProviders->every(fn (array $provider): bool => $provider['capabilities']['auto_create_supported'] === false));
        $this->assertTrue($futureProviders->every(fn (array $provider): bool => $provider['requires_credentials'] === true));
    }

    public function test_disabled_or_unknown_provider_is_rejected_for_live_class_creation(): void
    {
        $context = $this->liveClassContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->from(route('school.live-classes.create'))
            ->post(route('school.live-classes.store'), $this->validPayload($context, [
                'provider' => LiveClass::PROVIDER_ZOOM,
            ]))
            ->assertRedirect(route('school.live-classes.create'))
            ->assertSessionHasErrors('provider');

        $this->from(route('school.live-classes.create'))
            ->post(route('school.live-classes.store'), $this->validPayload($context, [
                'provider' => 'unknown_provider',
            ]))
            ->assertRedirect(route('school.live-classes.create'))
            ->assertSessionHasErrors('provider');

        $this->assertDatabaseCount('live_classes', 0);
    }

    public function test_school_admin_can_create_manual_provider_live_class_and_see_provider_boundaries(): void
    {
        $context = $this->liveClassContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.live-classes.create'))
            ->assertOk()
            ->assertSee('Provider')
            ->assertSee('Manual link')
            ->assertSee('Provider automation is not active yet')
            ->assertSee('Google Meet')
            ->assertSee('API automation is disabled')
            ->assertDontSee('OAuth callback')
            ->assertDontSee('Client secret');

        $this->post(route('school.live-classes.store'), $this->validPayload($context, [
            'provider' => LiveClass::PROVIDER_MANUAL,
        ]))->assertRedirect();

        $liveClass = LiveClass::firstOrFail();

        $this->assertSame(LiveClass::PROVIDER_MANUAL, $liveClass->provider);
        $this->assertSame('Manual link', data_get($liveClass->metadata, 'provider_label'));
        $this->assertTrue(data_get($liveClass->metadata, 'provider_capabilities.manual_link_supported'));
        $this->assertFalse(data_get($liveClass->metadata, 'provider_capabilities.auto_create_supported'));

        $this->get(route('school.live-classes.show', $liveClass))
            ->assertOk()
            ->assertSee('Manual link')
            ->assertSee('Stage 17 Provider Boundary')
            ->assertSee('Manual provider is active')
            ->assertSee('Offline live class is not available');
    }

    public function test_manual_meeting_url_is_required_and_invalid_http_scheme_is_rejected(): void
    {
        $context = $this->liveClassContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->from(route('school.live-classes.create'))
            ->post(route('school.live-classes.store'), $this->validPayload($context, [
                'meeting_url' => null,
            ]))
            ->assertRedirect(route('school.live-classes.create'))
            ->assertSessionHasErrors('meeting_url');

        $this->from(route('school.live-classes.create'))
            ->post(route('school.live-classes.store'), $this->validPayload($context, [
                'meeting_url' => 'javascript:alert(1)',
            ]))
            ->assertRedirect(route('school.live-classes.create'))
            ->assertSessionHasErrors('meeting_url');

        $this->assertDatabaseCount('live_classes', 0);
    }

    public function test_recording_url_validation_remains_provider_aware(): void
    {
        $context = $this->liveClassContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->from(route('school.live-classes.create'))
            ->post(route('school.live-classes.store'), $this->validPayload($context, [
                'recording_url' => 'ftp://recordings.example.test/replay',
            ]))
            ->assertRedirect(route('school.live-classes.create'))
            ->assertSessionHasErrors('recording_url');

        $this->assertDatabaseCount('live_classes', 0);
    }

    public function test_provider_audit_metadata_includes_provider_key_without_password_or_secrets(): void
    {
        $context = $this->liveClassContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.live-classes.store'), $this->validPayload($context, [
            'provider' => LiveClass::PROVIDER_MANUAL,
            'meeting_password' => 'ProviderSecretShouldNotLeak',
        ]))->assertRedirect();

        $audit = AuditLog::query()->where('action', 'live_class_created')->firstOrFail();

        $this->assertSame(LiveClass::PROVIDER_MANUAL, data_get($audit->metadata, 'provider'));
        $this->assertArrayNotHasKey('meeting_password', $audit->metadata ?? []);
        $this->assertArrayNotHasKey('provider_secret', $audit->metadata ?? []);
        $this->assertArrayNotHasKey('oauth_token', $audit->metadata ?? []);
        $this->assertStringNotContainsString('ProviderSecretShouldNotLeak', json_encode($audit->toArray()));
    }

    public function test_teacher_assigned_scope_still_works_with_provider_registry(): void
    {
        $context = $this->liveClassContext('teacher');
        $this->assignTeacherToSubject($context);
        $this->actAsSchoolRole($context['user'], $context['school'], 'teacher');

        $this->post(route('school.live-classes.store'), $this->validPayload($context, [
            'provider' => LiveClass::PROVIDER_MANUAL,
            'teacher_user_id' => null,
        ]))->assertRedirect();

        $this->assertDatabaseHas('live_classes', [
            'school_id' => $context['school']->id,
            'provider' => LiveClass::PROVIDER_MANUAL,
            'teacher_user_id' => $context['user']->id,
        ]);
    }

    public function test_accountant_and_result_officer_do_not_gain_provider_management_access(): void
    {
        $context = $this->liveClassContext('school_admin');

        foreach (['accountant', 'result_officer'] as $role) {
            $user = $this->createUserForSchool($context['school'], $role);
            $this->actAsSchoolRole($user, $context['school'], $role);

            $this->get(route('school.live-classes.index'))->assertForbidden();
            $this->post(route('school.live-classes.store'), $this->validPayload($context, [
                'provider' => LiveClass::PROVIDER_MANUAL,
            ]))->assertForbidden();
        }

        $this->assertDatabaseCount('live_classes', 0);
    }

    public function test_future_provider_api_automation_is_not_exposed_as_completed(): void
    {
        $context = $this->liveClassContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.live-classes.index'))
            ->assertOk()
            ->assertSee('Google Meet, Zoom, and Microsoft Teams API automation remain disabled')
            ->assertSee('provider credentials')
            ->assertSee('generated meeting rooms')
            ->assertDontSee('Generate meeting room')
            ->assertDontSee('OAuth callback')
            ->assertDontSee('API automation is active');
    }

    public function test_standalone_dashboard_reports_provider_abstraction_and_offline_boundary(): void
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
            ->assertSee('provider setup details')
            ->assertSee('Provider setup is available')
            ->assertSee('Live class provider setup')
            ->assertSee('API automation and offline class delivery remain disabled');
    }

    public function test_no_provider_credentials_are_stored_or_required_for_manual_provider(): void
    {
        $context = $this->liveClassContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.live-classes.store'), $this->validPayload($context, [
            'provider' => LiveClass::PROVIDER_MANUAL,
        ]))->assertRedirect();

        $liveClass = LiveClass::firstOrFail();

        $this->assertSame(LiveClass::PROVIDER_MANUAL, $liveClass->provider);
        $this->assertFalse(data_get($liveClass->metadata, 'provider_capabilities.requires_credentials'));
        $this->assertArrayNotHasKey('provider_credentials', $liveClass->metadata ?? []);
        $this->assertArrayNotHasKey('oauth_token', $liveClass->metadata ?? []);
        $this->assertArrayNotHasKey('provider_payload', $liveClass->metadata ?? []);
    }

    public function test_student_and_parent_provider_access_boundaries_remain_deferred(): void
    {
        $access = app(LiveClassAccessService::class);

        $this->assertFalse($access->studentPortalIsSafe());
        $this->assertFalse($access->parentPortalIsSafe());
        $this->assertStringContainsString('deferred', $access->studentPortalBoundaryNote());
        $this->assertStringContainsString('deferred', $access->parentPortalBoundaryNote());
    }

    public function test_registry_assert_selectable_rejects_unknown_provider_without_fallback_for_writes(): void
    {
        $this->expectException(ValidationException::class);

        app(LiveClassProviderRegistry::class)->assertSelectable('not_real');
    }

    private function liveClassContext(string $role): array
    {
        $school = $this->createSchool();
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'JSS 1',
            'section' => fake()->unique()->lexify('B??'),
            'status' => 'active',
        ]);
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics '.fake()->unique()->numberBetween(1, 9999),
            'code' => fake()->unique()->lexify('MTP???'),
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
            'body' => 'Prep notes for provider abstraction testing.',
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
            'title' => 'JSS 1 Mathematics Provider Class',
            'description' => 'Provider abstraction revision session.',
            'provider' => LiveClass::PROVIDER_MANUAL,
            'meeting_url' => 'https://meet.example.test/provider-class',
            'meeting_password' => null,
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
            'timezone' => 'Africa/Lagos',
            'recording_url' => 'https://recordings.example.test/provider-class',
            'reminder_minutes' => 30,
        ], $overrides);
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
            'name' => 'Sanfaani Provider Academy '.$id,
            'slug' => 'sanfaani-provider-academy-'.$id,
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
