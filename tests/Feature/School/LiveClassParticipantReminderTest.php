<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\ClassSubjectAssignment;
use App\Models\LiveClass;
use App\Models\LiveClassParticipant;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Notifications\LiveClassInvitationNotification;
use App\Notifications\LiveClassReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LiveClassParticipantReminderTest extends TestCase
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

    public function test_school_admin_can_create_live_meeting_with_resolved_participants_and_notifications(): void
    {
        $context = $this->liveClassContext('school_admin');
        $portal = $this->createStudentAndParent($context);
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.live-classes.store'), $this->validPayload($context, [
            'audience_type' => LiveClassParticipant::AUDIENCE_CLASS,
        ]))->assertRedirect();

        $liveClass = LiveClass::firstOrFail();

        $this->assertDatabaseHas('live_class_participants', [
            'school_id' => $context['school']->id,
            'live_class_id' => $liveClass->id,
            'user_id' => $portal['studentUser']->id,
            'status' => LiveClassParticipant::STATUS_INVITED,
        ]);

        $this->assertDatabaseHas('live_class_participants', [
            'school_id' => $context['school']->id,
            'live_class_id' => $liveClass->id,
            'user_id' => $portal['parentUser']->id,
            'status' => LiveClassParticipant::STATUS_INVITED,
        ]);

        $this->assertTrue($portal['parentUser']->notifications()
            ->where('type', LiveClassInvitationNotification::class)
            ->exists());
    }

    public function test_teacher_can_create_live_meeting_for_assigned_scope_with_selected_users(): void
    {
        $context = $this->liveClassContext('teacher');
        $portal = $this->createStudentAndParent($context);
        $this->assignTeacherToSubject($context);
        $this->actAsSchoolRole($context['user'], $context['school'], 'teacher');

        $this->post(route('school.live-classes.store'), $this->validPayload($context, [
            'teacher_user_id' => null,
            'audience_type' => LiveClassParticipant::AUDIENCE_SELECTED_USERS,
            'selected_user_ids' => [$portal['parentUser']->id],
        ]))->assertRedirect();

        $liveClass = LiveClass::firstOrFail();

        $this->assertSame($context['user']->id, $liveClass->teacher_user_id);
        $this->assertDatabaseHas('live_class_participants', [
            'live_class_id' => $liveClass->id,
            'user_id' => $portal['parentUser']->id,
            'audience_type' => LiveClassParticipant::AUDIENCE_SELECTED_USERS,
        ]);
    }

    public function test_unauthorized_school_role_cannot_create_live_meeting(): void
    {
        $context = $this->liveClassContext('school_admin');
        $resultOfficer = $this->createUserForSchool($context['school'], 'result_officer');
        $this->actAsSchoolRole($resultOfficer, $context['school'], 'result_officer');

        $this->post(route('school.live-classes.store'), $this->validPayload($context))
            ->assertForbidden();

        $this->assertDatabaseCount('live_classes', 0);
    }

    public function test_participants_can_see_and_join_meeting_without_cross_school_visibility(): void
    {
        $context = $this->liveClassContext('school_admin');
        $portal = $this->createStudentAndParent($context);
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');
        $this->post(route('school.live-classes.store'), $this->validPayload($context))->assertRedirect();
        $liveClass = LiveClass::firstOrFail();

        $this->actAsSchoolRole($portal['parentUser'], $context['school'], 'parent');

        $this->get(route('portal.live-classes.index'))
            ->assertOk()
            ->assertSee($liveClass->title);

        $this->get(route('portal.live-classes.show', $liveClass))
            ->assertOk()
            ->assertSee('Join Live Class');

        $this->post(route('portal.live-classes.join', $liveClass))
            ->assertRedirect($liveClass->meeting_url);

        $this->assertDatabaseHas('live_class_participants', [
            'live_class_id' => $liveClass->id,
            'user_id' => $portal['parentUser']->id,
            'status' => LiveClassParticipant::STATUS_JOINED,
        ]);

        $other = $this->liveClassContext('school_admin');
        $foreignLiveClass = LiveClass::create($this->liveClassRow($other));

        $this->get(route('portal.live-classes.show', $foreignLiveClass))
            ->assertForbidden();
    }

    public function test_reminder_command_sends_due_reminders_once_without_redis(): void
    {
        config(['queue.default' => 'sync', 'cache.default' => 'file']);

        $context = $this->liveClassContext('school_admin');
        $portal = $this->createStudentAndParent($context);
        $liveClass = LiveClass::create($this->liveClassRow($context, [
            'starts_at' => now()->addMinutes(5),
            'metadata' => ['reminder_minutes' => 10],
        ]));
        LiveClassParticipant::create([
            'school_id' => $context['school']->id,
            'live_class_id' => $liveClass->id,
            'user_id' => $portal['parentUser']->id,
            'audience_type' => LiveClassParticipant::AUDIENCE_CLASS,
            'role_context' => 'parent',
            'status' => LiveClassParticipant::STATUS_INVITED,
            'invited_at' => now()->subHour(),
            'reminder_due_at' => now()->subMinute(),
        ]);

        $this->artisan('live-classes:send-reminders')
            ->expectsOutput('Live-class reminders processed: 1')
            ->assertSuccessful();

        $this->assertTrue($portal['parentUser']->notifications()
            ->where('type', LiveClassReminderNotification::class)
            ->exists());
        $this->assertNotNull(LiveClassParticipant::first()->reminder_sent_at);

        $this->artisan('live-classes:send-reminders')
            ->expectsOutput('Live-class reminders processed: 0')
            ->assertSuccessful();

        $this->assertSame(1, $portal['parentUser']->notifications()
            ->where('type', LiveClassReminderNotification::class)
            ->count());
    }

    public function test_parent_dashboard_renders_upcoming_live_class_invitation(): void
    {
        $context = $this->liveClassContext('school_admin');
        $portal = $this->createStudentAndParent($context);
        $liveClass = LiveClass::create($this->liveClassRow($context));
        LiveClassParticipant::create([
            'school_id' => $context['school']->id,
            'live_class_id' => $liveClass->id,
            'user_id' => $portal['parentUser']->id,
            'audience_type' => LiveClassParticipant::AUDIENCE_CLASS,
            'role_context' => 'parent',
            'status' => LiveClassParticipant::STATUS_INVITED,
            'invited_at' => now(),
            'reminder_due_at' => now()->addMinutes(30),
        ]);

        $this->actAsSchoolRole($portal['parentUser'], $context['school'], 'parent');

        $this->get(route('parent.dashboard'))
            ->assertOk()
            ->assertSee('Live Classes')
            ->assertSee($liveClass->title);
    }

    private function liveClassContext(string $role): array
    {
        $school = $this->createSchool();
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'JSS 1',
            'section' => fake()->unique()->lexify('H??'),
            'status' => 'active',
        ]);
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics '.fake()->unique()->numberBetween(1, 9999),
            'code' => fake()->unique()->lexify('HMT???'),
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

    private function validPayload(array $context, array $overrides = []): array
    {
        return array_merge([
            'school_class_id' => $context['class']->id,
            'subject_id' => $context['subject']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'teacher_user_id' => $context['user']->hasRole('teacher') ? $context['user']->id : null,
            'title' => 'H4 Mathematics Live Class',
            'description' => 'Participant and reminder test session.',
            'provider' => LiveClass::PROVIDER_MANUAL,
            'meeting_url' => 'https://meet.example.test/h4-live-class',
            'meeting_password' => null,
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
            'timezone' => 'Africa/Lagos',
            'recording_url' => null,
            'reminder_minutes' => 30,
            'audience_type' => LiveClassParticipant::AUDIENCE_CLASS,
        ], $overrides);
    }

    private function liveClassRow(array $context, array $overrides = []): array
    {
        return array_merge([
            'school_id' => $context['school']->id,
            'school_class_id' => $context['class']->id,
            'subject_id' => $context['subject']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'teacher_user_id' => $context['user']->id,
            'title' => 'H4 Mathematics Live Class',
            'description' => 'Participant test session.',
            'provider' => LiveClass::PROVIDER_MANUAL,
            'meeting_url' => 'https://meet.example.test/h4-live-class',
            'meeting_password' => null,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'timezone' => 'Africa/Lagos',
            'status' => LiveClass::STATUS_SCHEDULED,
            'recording_url' => null,
            'created_by' => $context['user']->id,
            'updated_by' => $context['user']->id,
            'metadata' => ['reminder_minutes' => 30],
        ], $overrides);
    }

    private function createStudentAndParent(array $context): array
    {
        $studentUser = $this->createUserForSchool($context['school'], 'student');
        $parentUser = $this->createUserForSchool($context['school'], 'parent');
        $student = Student::create([
            'school_id' => $context['school']->id,
            'student_user_id' => $studentUser->id,
            'school_class_id' => $context['class']->id,
            'admission_number' => fake()->unique()->numerify('H4###'),
            'first_name' => 'Ada',
            'last_name' => 'Learner',
            'guardian_name' => $parentUser->name,
            'guardian_email' => $parentUser->email,
            'status' => 'active',
        ]);
        $student->parentUsers()->attach($parentUser->id, [
            'school_id' => $context['school']->id,
            'relationship' => 'parent',
            'is_primary' => true,
            'can_view_results' => true,
            'can_view_attendance' => true,
            'can_view_finance' => true,
            'receives_notifications' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return compact('studentUser', 'parentUser', 'student');
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
            'name' => 'Sanfaani H4 Academy '.$id,
            'slug' => 'sanfaani-h4-academy-'.$id,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function createUserForSchool(School $school, string $role): User
    {
        $user = User::factory()->create([
            'school_id' => $school->id,
            'email' => fake()->unique()->safeEmail(),
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

    private function actAsSchoolRole(User $user, School $school, string $role): void
    {
        $this->actingAs($user);
        session([
            'active_school_id' => $school->id,
            'active_role_context' => $role,
        ]);
    }
}
