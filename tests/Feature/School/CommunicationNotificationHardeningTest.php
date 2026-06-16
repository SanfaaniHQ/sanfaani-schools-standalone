<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\LiveClass;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolNotificationLog;
use App\Models\SchoolNotificationTemplate;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\Communications\SchoolNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CommunicationNotificationHardeningTest extends TestCase
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

    public function test_communication_routes_require_authentication(): void
    {
        $this->get(route('school.communications.index'))
            ->assertRedirect(route('login'));

        $this->get(route('school.communications.logs'))
            ->assertRedirect(route('login'));

        $this->get(route('school.communications.templates.create'))
            ->assertRedirect(route('login'));
    }

    public function test_school_admin_can_access_center_and_manage_templates(): void
    {
        $context = $this->schoolContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.communications.index'))
            ->assertOk()
            ->assertSee('Communication Command Center')
            ->assertSee('Quick Actions')
            ->assertSee('Delivery Status');

        $this->post(route('school.communications.templates.store'), [
            'template_key' => 'live_class.reminder',
            'title' => 'Live Class Reminder',
            'subject' => 'Upcoming live class',
            'body' => 'Live class {{ title }} starts soon.',
            'channel' => SchoolNotificationTemplate::CHANNEL_DATABASE,
            'audience_type' => SchoolNotificationTemplate::AUDIENCE_SCHOOL_ADMIN,
            'is_active' => '1',
        ])->assertRedirect();

        $template = SchoolNotificationTemplate::firstOrFail();
        $this->assertSame($context['school']->id, $template->school_id);
        $this->assertTrue($template->is_active);

        $this->patch(route('school.communications.templates.update', $template), [
            'template_key' => 'live_class.reminder',
            'title' => 'Updated Live Class Reminder',
            'subject' => 'Updated subject',
            'body' => 'Updated safe body.',
            'channel' => SchoolNotificationTemplate::CHANNEL_SMS,
            'audience_type' => SchoolNotificationTemplate::AUDIENCE_SCHOOL_ADMIN,
            'is_active' => '0',
        ])->assertRedirect();

        $this->assertDatabaseHas('school_notification_templates', [
            'id' => $template->id,
            'school_id' => $context['school']->id,
            'title' => 'Updated Live Class Reminder',
            'channel' => SchoolNotificationTemplate::CHANNEL_SMS,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'communication_template_created']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'communication_template_updated']);
    }

    public function test_non_admin_school_roles_cannot_manage_communication_center(): void
    {
        $context = $this->schoolContext('school_admin');

        foreach (['teacher', 'accountant', 'result_officer'] as $role) {
            $user = $this->createUserForSchool($context['school'], $role);
            $this->actAsSchoolRole($user, $context['school'], $role);

            $this->get(route('school.communications.index'))->assertForbidden();
            $this->get(route('school.communications.templates.create'))->assertForbidden();
            $this->post(route('school.communications.templates.store'), [
                'template_key' => 'blocked.template',
                'title' => 'Blocked',
                'body' => 'Blocked',
                'channel' => SchoolNotificationTemplate::CHANNEL_DATABASE,
                'audience_type' => SchoolNotificationTemplate::AUDIENCE_SCHOOL_ADMIN,
            ])->assertForbidden();
        }

        $this->assertDatabaseCount('school_notification_templates', 0);
    }

    public function test_notification_logs_are_school_scoped_and_foreign_records_are_blocked(): void
    {
        $context = $this->schoolContext('school_admin');
        $other = $this->schoolContext('school_admin');
        $ownLog = $this->createNotificationLog($context['school'], 'Visible safe summary');
        $foreignLog = $this->createNotificationLog($other['school'], 'Hidden foreign summary');
        $foreignTemplate = SchoolNotificationTemplate::create([
            'school_id' => $other['school']->id,
            'template_key' => 'foreign.template',
            'title' => 'Foreign Template',
            'body' => 'Foreign body',
            'channel' => SchoolNotificationTemplate::CHANNEL_DATABASE,
            'audience_type' => SchoolNotificationTemplate::AUDIENCE_SCHOOL_ADMIN,
        ]);

        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.communications.logs'))
            ->assertOk()
            ->assertSee($ownLog->message_summary)
            ->assertDontSee($foreignLog->message_summary);

        $this->get(route('school.communications.logs.show', $foreignLog))->assertForbidden();
        $this->get(route('school.communications.templates.edit', $foreignTemplate))->assertForbidden();
    }

    public function test_live_class_workflow_logs_safe_notifications_without_external_delivery(): void
    {
        Mail::fake();
        $context = $this->liveClassContext();
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->post(route('school.live-classes.store'), $this->liveClassPayload($context, [
            'meeting_password' => 'SecretShouldNotLeak',
        ]))->assertRedirect();

        $liveClass = LiveClass::firstOrFail();
        $this->assertDatabaseHas('school_notification_logs', [
            'school_id' => $context['school']->id,
            'event_type' => 'live_class.scheduled',
            'channel' => SchoolNotificationLog::CHANNEL_DATABASE,
            'status' => SchoolNotificationLog::STATUS_LOGGED,
        ]);

        $this->patch(route('school.live-classes.update', $liveClass), $this->liveClassPayload($context, [
            'title' => 'Updated Live Class',
            'meeting_password' => 'SecretShouldNotLeak',
        ]))->assertRedirect();

        $this->post(route('school.live-classes.cancel', $liveClass->fresh()))->assertRedirect();

        $this->assertDatabaseHas('school_notification_logs', ['event_type' => 'live_class.updated']);
        $this->assertDatabaseHas('school_notification_logs', ['event_type' => 'live_class.cancelled']);
        $this->assertSame(3, SchoolNotificationLog::query()->where('school_id', $context['school']->id)->where('event_type', 'like', 'live_class.%')->count());
        $this->assertStringNotContainsString('SecretShouldNotLeak', json_encode(SchoolNotificationLog::query()->get()->toArray()));

        Mail::assertNothingSent();
    }

    public function test_deferred_provider_channels_are_logged_without_sending(): void
    {
        Mail::fake();
        $context = $this->schoolContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $smsLog = app(SchoolNotificationService::class)->logOperationalNotification($context['school'], [
            'event_type' => 'finance.invoice.sms_ready',
            'channel' => SchoolNotificationLog::CHANNEL_SMS,
            'recipient_type' => 'school_admin',
            'subject' => 'SMS-ready invoice reminder',
            'message_summary' => 'Invoice reminder prepared for future SMS provider dispatch.',
        ], $context['user']);

        $whatsAppLog = app(SchoolNotificationService::class)->logOperationalNotification($context['school'], [
            'event_type' => 'attendance.summary.whatsapp_ready',
            'channel' => SchoolNotificationLog::CHANNEL_WHATSAPP,
            'recipient_type' => 'school_admin',
            'subject' => 'WhatsApp-ready attendance summary',
            'message_summary' => 'Attendance summary prepared for future WhatsApp provider dispatch.',
        ], $context['user']);

        $this->assertSame(SchoolNotificationLog::STATUS_DEFERRED, $smsLog?->status);
        $this->assertSame(SchoolNotificationLog::STATUS_DEFERRED, $whatsAppLog?->status);
        $this->assertSame('deferred', data_get($smsLog?->metadata, 'external_provider_delivery'));
        $this->assertSame('deferred', data_get($whatsAppLog?->metadata, 'external_provider_delivery'));
        Mail::assertNothingSent();
    }

    public function test_dashboard_navigation_and_standalone_summary_are_role_scoped(): void
    {
        config([
            'standalone.product_edition' => 'standalone',
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        $context = $this->schoolContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Communication Center')
            ->assertSee('Communication notification hardening')
            ->assertSee('External provider APIs remain deferred');

        $teacher = $this->createUserForSchool($context['school'], 'teacher');
        $this->actAsSchoolRole($teacher, $context['school'], 'teacher');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertDontSee('Communication Center');
    }

    private function schoolContext(string $role): array
    {
        $school = $this->createSchool();
        $user = $this->createUserForSchool($school, $role);

        return compact('school', 'user');
    }

    private function liveClassContext(): array
    {
        $context = $this->schoolContext('school_admin');
        $class = SchoolClass::create([
            'school_id' => $context['school']->id,
            'name' => 'JSS 1',
            'section' => fake()->unique()->lexify('A??'),
            'status' => 'active',
        ]);
        $subject = Subject::create([
            'school_id' => $context['school']->id,
            'name' => 'Mathematics '.fake()->unique()->numberBetween(1, 9999),
            'code' => fake()->unique()->lexify('MTH???'),
            'status' => 'active',
            'assignment_type' => 'core',
            'is_elective' => false,
        ]);
        $session = AcademicSession::create([
            'school_id' => $context['school']->id,
            'name' => fake()->unique()->numerify('2026/2027 ###'),
            'is_active' => true,
            'status' => 'active',
        ]);
        $term = Term::create([
            'school_id' => $context['school']->id,
            'academic_session_id' => $session->id,
            'name' => 'First Term '.fake()->unique()->numberBetween(1, 9999),
            'is_active' => true,
            'status' => 'active',
        ]);

        return $context + compact('class', 'subject', 'session', 'term');
    }

    private function liveClassPayload(array $context, array $overrides = []): array
    {
        return array_merge([
            'school_class_id' => $context['class']->id,
            'subject_id' => $context['subject']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'teacher_user_id' => null,
            'title' => 'JSS 1 Mathematics Live Class',
            'description' => 'Revision session.',
            'provider' => LiveClass::PROVIDER_MANUAL,
            'meeting_url' => 'https://meet.example.test/class-session',
            'meeting_password' => null,
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
            'timezone' => 'Africa/Lagos',
            'recording_url' => null,
            'reminder_minutes' => 30,
        ], $overrides);
    }

    private function createNotificationLog(School $school, string $summary): SchoolNotificationLog
    {
        return SchoolNotificationLog::create([
            'school_id' => $school->id,
            'event_type' => 'test.notification',
            'channel' => SchoolNotificationLog::CHANNEL_DATABASE,
            'recipient_type' => 'school_admin',
            'recipient_name' => 'School Admins',
            'subject' => 'Scoped notification',
            'message_summary' => $summary,
            'status' => SchoolNotificationLog::STATUS_LOGGED,
            'metadata' => ['external_provider_active' => false],
        ]);
    }

    private function createSchool(): School
    {
        $id = fake()->unique()->numberBetween(1, 999999);

        return School::create([
            'name' => 'Sanfaani Communication Academy '.$id,
            'slug' => 'sanfaani-communication-academy-'.$id,
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
