<?php

namespace Tests\Feature\School;

use App\Enums\ResultWorkflowStatus;
use App\Mail\Transactional\StudentTransactionalMail;
use App\Models\AcademicSession;
use App\Models\BulkCommunicationBatch;
use App\Models\CommunicationLog;
use App\Models\MailSetting;
use App\Models\ReportCardSnapshot;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolNotificationLog;
use App\Models\SchoolNotificationTemplate;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentResult;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CommunicationCommandCenterStageBTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        config([
            'mail.default' => 'log',
            'mail.from.address' => 'noreply@example.test',
            'mail.from.name' => 'Sanfaani Test',
        ]);

        $this->configurePlatformMailer();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'school_admin', 'teacher', 'result_officer', 'accountant', 'parent', 'student'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_bulk_message_redesigned_form_renders(): void
    {
        $context = $this->communicationContext();
        $this->createStudent($context, guardianEmail: 'guardian@example.test');
        SchoolNotificationTemplate::create([
            'school_id' => $context['school']->id,
            'template_key' => 'parent.update',
            'title' => 'Parent Update',
            'subject' => 'Parent update subject',
            'body' => 'Reusable parent update body.',
            'channel' => SchoolNotificationTemplate::CHANNEL_EMAIL,
            'audience_type' => SchoolNotificationTemplate::AUDIENCE_STUDENT,
            'is_active' => true,
            'created_by' => $context['admin']->id,
            'updated_by' => $context['admin']->id,
        ]);
        $this->actAsSchoolRole($context['admin'], $context['school'], 'school_admin');

        $this->get(route('school.communications.bulk'))
            ->assertOk()
            ->assertSee('Compose Message')
            ->assertSee('Audience group')
            ->assertSee('Channels')
            ->assertSee('Template')
            ->assertSee('Recipient Summary')
            ->assertSee('Preview')
            ->assertSee('Create And Process Batch');
    }

    public function test_bulk_message_no_recipients_is_handled_cleanly(): void
    {
        $context = $this->communicationContext();
        $this->actAsSchoolRole($context['admin'], $context['school'], 'school_admin');

        $this->post(route('school.communications.bulk.send'), [
            'audience' => 'selected_students',
            'student_ids' => [],
            'channels' => ['email'],
            'type' => 'custom_message',
            'subject' => 'No recipient check',
            'message' => 'This should not be sent to anyone.',
            'chunk_size' => 10,
        ])
            ->assertRedirect()
            ->assertSessionHas('warning', __('ui.bulk_communication_no_recipients'));

        $this->assertDatabaseHas('bulk_communication_batches', [
            'school_id' => $context['school']->id,
            'subject' => 'No recipient check',
            'total_recipients' => 0,
        ]);
        $this->assertSame(0, CommunicationLog::count());
    }

    public function test_notification_filters_work(): void
    {
        $context = $this->communicationContext();
        $visible = $this->notificationLog($context['school'], [
            'event_type' => 'report_card.email.sent',
            'channel' => SchoolNotificationLog::CHANNEL_EMAIL,
            'status' => SchoolNotificationLog::STATUS_SENT,
            'recipient_name' => 'Aisha Parent',
            'recipient_email' => 'guardian@example.test',
            'subject' => 'Visible report card',
            'message_summary' => 'Visible report card summary',
        ]);
        $hidden = $this->notificationLog($context['school'], [
            'event_type' => 'finance.invoice.generated',
            'channel' => SchoolNotificationLog::CHANNEL_DATABASE,
            'status' => SchoolNotificationLog::STATUS_LOGGED,
            'recipient_name' => 'Finance Office',
            'subject' => 'Hidden invoice',
            'message_summary' => 'Hidden invoice summary',
        ]);
        $hidden->forceFill(['created_at' => now()->subDays(4), 'updated_at' => now()->subDays(4)])->save();
        $this->actAsSchoolRole($context['admin'], $context['school'], 'school_admin');

        $this->get(route('school.communications.logs', [
            'event_type' => $visible->event_type,
            'status' => $visible->status,
            'channel' => $visible->channel,
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'search' => 'guardian@example.test',
        ]))
            ->assertOk()
            ->assertSee('Visible report card summary')
            ->assertDontSee('Hidden invoice summary')
            ->assertSee('Clear filters');
    }

    public function test_notification_empty_state_renders(): void
    {
        $context = $this->communicationContext();
        $this->actAsSchoolRole($context['admin'], $context['school'], 'school_admin');

        $this->get(route('school.communications.logs'))
            ->assertOk()
            ->assertSee('No matching notification logs');
    }

    public function test_student_360_email_report_card_action_appears_for_permitted_user(): void
    {
        $context = $this->communicationContext();
        $student = $this->createStudent($context, guardianEmail: 'guardian@example.test');
        $this->actAsSchoolRole($context['admin'], $context['school'], 'school_admin');

        $this->get(route('school.students.show', $student))
            ->assertOk()
            ->assertSee(__('ui.email_report_card_to_parent'))
            ->assertSee('guardian@example.test')
            ->assertSee('Custom Email');
    }

    public function test_report_card_email_sends_when_parent_email_exists_and_logs_notification(): void
    {
        Mail::fake();
        $context = $this->publishedReportContext();
        $this->actAsSchoolRole($context['admin'], $context['school'], 'school_admin');

        $this->post(route('school.communications.students.report-card-email', $context['student']), [
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'result_type' => 'term_result',
        ])
            ->assertRedirect()
            ->assertSessionHas('success', __('ui.report_card_email_sent'));

        Mail::assertSent(StudentTransactionalMail::class);

        $this->assertDatabaseHas('communication_logs', [
            'school_id' => $context['school']->id,
            'recipient' => 'guardian@example.test',
            'type' => 'report_card_parent_email',
            'status' => CommunicationLog::STATUS_SENT,
        ]);
        $this->assertDatabaseHas('school_notification_logs', [
            'school_id' => $context['school']->id,
            'event_type' => 'report_card.email.sent',
            'channel' => SchoolNotificationLog::CHANNEL_EMAIL,
            'status' => SchoolNotificationLog::STATUS_SENT,
            'recipient_email' => 'guardian@example.test',
        ]);
        $this->assertSame(1, ReportCardSnapshot::count());
        $this->assertNotNull(data_get(CommunicationLog::firstOrFail()->metadata, 'report_card_snapshot_uuid'));
    }

    public function test_report_card_email_missing_parent_email_fails_gracefully(): void
    {
        Mail::fake();
        $context = $this->publishedReportContext(guardianEmail: null);
        $this->actAsSchoolRole($context['admin'], $context['school'], 'school_admin');

        $this->post(route('school.communications.students.report-card-email', $context['student']), [
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'result_type' => 'term_result',
        ])
            ->assertRedirect()
            ->assertSessionHas('error', __('ui.report_card_email_missing_guardian'));

        Mail::assertNothingSent();
        $this->assertSame(0, CommunicationLog::count());
        $this->assertSame(0, ReportCardSnapshot::count());
        $this->assertDatabaseHas('school_notification_logs', [
            'school_id' => $context['school']->id,
            'event_type' => 'report_card.email.failed',
            'channel' => SchoolNotificationLog::CHANNEL_EMAIL,
            'status' => SchoolNotificationLog::STATUS_FAILED,
        ]);
    }

    public function test_stage_b_translation_keys_exist_for_existing_locales(): void
    {
        $keys = [
            'bulk_communication_no_recipients',
            'email_report_card_to_parent',
            'email_report_card_to_parent_help',
            'notification_logs',
            'parent_guardian',
            'parent_email_missing',
            'print_or_save_report_card',
            'report_card_email_body',
            'report_card_email_failed',
            'report_card_email_headline',
            'report_card_email_log_subject',
            'report_card_email_missing_guardian',
            'report_card_email_notification_summary',
            'report_card_email_sent',
            'report_card_email_subject',
            'report_card_secure_title',
            'report_card_snapshot_footer',
            'secure_link_expires',
            'secure_report_card_link',
            'secure_report_card_link_body',
            'sending_message',
            'sending_report_card_email',
        ];

        foreach (['ar', 'en', 'fr', 'ha', 'yo'] as $locale) {
            $lines = require lang_path($locale.'/ui.php');

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $lines, "{$locale} is missing ui.{$key}");
            }
        }
    }

    private function communicationContext(): array
    {
        $school = School::create([
            'name' => 'Stage B Communication School',
            'slug' => 'stage-b-communication-school-'.fake()->unique()->numberBetween(1, 999999),
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
        $class = SchoolClass::create([
            'school_id' => $school->id,
            'name' => 'Basic 4',
            'section' => 'A',
            'status' => 'active',
        ]);
        $session = AcademicSession::create([
            'school_id' => $school->id,
            'name' => '2025/2026',
            'is_active' => true,
            'status' => 'active',
        ]);
        $term = Term::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => 'First Term',
            'is_active' => true,
            'status' => 'active',
        ]);
        $admin = User::factory()->create([
            'school_id' => $school->id,
            'email' => fake()->unique()->safeEmail(),
        ]);
        $this->assignSchoolRole($school, $admin, 'school_admin');

        return compact('school', 'class', 'session', 'term', 'admin');
    }

    private function publishedReportContext(?string $guardianEmail = 'guardian@example.test'): array
    {
        $context = $this->communicationContext();
        $student = $this->createStudent($context, guardianEmail: $guardianEmail);
        $subject = Subject::create([
            'school_id' => $context['school']->id,
            'name' => 'Mathematics',
            'code' => fake()->unique()->lexify('MTH???'),
            'status' => 'active',
        ]);
        StudentResult::create([
            'school_id' => $context['school']->id,
            'student_id' => $student->id,
            'school_class_id' => $context['class']->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'result_type' => 'term_result',
            'ca_score' => 30,
            'exam_score' => 55,
            'total_score' => 85,
            'grade' => 'A',
            'remark' => 'Excellent',
            'status' => ResultWorkflowStatus::Published->value,
            'published_at' => now(),
            'published_by' => $context['admin']->id,
            'recorded_by' => $context['admin']->id,
        ]);

        return $context + compact('student', 'subject');
    }

    private function createStudent(array $context, ?string $guardianEmail): Student
    {
        $student = Student::create([
            'school_id' => $context['school']->id,
            'school_class_id' => $context['class']->id,
            'admission_number' => fake()->unique()->bothify('STB-###'),
            'first_name' => 'Aisha',
            'last_name' => 'Bello',
            'guardian_name' => 'Aisha Parent',
            'guardian_email' => $guardianEmail,
            'guardian_phone' => '08000000000',
            'status' => 'active',
        ]);

        StudentClassEnrollment::create([
            'school_id' => $context['school']->id,
            'student_id' => $student->id,
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'start_term_id' => $context['term']->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        return $student;
    }

    private function notificationLog(School $school, array $overrides = []): SchoolNotificationLog
    {
        return SchoolNotificationLog::create(array_merge([
            'school_id' => $school->id,
            'event_type' => 'test.notification',
            'channel' => SchoolNotificationLog::CHANNEL_DATABASE,
            'recipient_type' => 'school_admin',
            'recipient_name' => 'School Admins',
            'subject' => 'Notification subject',
            'message_summary' => 'Notification summary',
            'status' => SchoolNotificationLog::STATUS_LOGGED,
            'metadata' => ['external_provider_active' => false],
        ], $overrides));
    }

    private function assignSchoolRole(School $school, User $user, string $role): void
    {
        $user->assignRole($role);

        UserSchoolRole::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'role_name' => $role,
            'status' => 'active',
        ]);
    }

    private function actAsSchoolRole(User $user, School $school, string $role): void
    {
        $this->actingAs($user);

        session([
            'active_school_id' => $school->id,
            'active_role_context' => $role,
        ]);
    }

    private function configurePlatformMailer(): void
    {
        MailSetting::updateOrCreate(
            ['school_id' => null],
            [
                'mailer' => 'log',
                'from_address' => 'noreply@example.test',
                'from_name' => 'Sanfaani Test',
                'is_enabled' => true,
            ]
        );
    }
}
