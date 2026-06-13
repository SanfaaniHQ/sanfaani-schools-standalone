<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\Admissions\AdmissionApplication;
use App\Models\Admissions\AdmissionCycle;
use App\Models\AttendanceOfflineSyncReceipt;
use App\Models\AuditLog;
use App\Models\CbtAttempt;
use App\Models\CbtAttemptAnswer;
use App\Models\CbtExam;
use App\Models\CbtExamQuestion;
use App\Models\CbtQuestion;
use App\Models\CbtQuestionBank;
use App\Models\FinanceFeeItem;
use App\Models\LiveClass;
use App\Models\LmsCbtActivity;
use App\Models\LmsClassroom;
use App\Models\LmsMaterial;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolNotificationLog;
use App\Models\SchoolNotificationTemplate;
use App\Models\Student;
use App\Models\StudentAttendanceRecord;
use App\Models\StudentFeeInvoice;
use App\Models\StudentFeePayment;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ReportsPackTest extends TestCase
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

    public function test_reports_center_requires_authentication(): void
    {
        $this->get(route('school.reports.index'))
            ->assertRedirect(route('login'));
    }

    public function test_school_admin_can_access_reports_center_with_school_summary_cards(): void
    {
        $context = $this->reportsContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.reports.index'))
            ->assertOk()
            ->assertSee('Reports Center')
            ->assertSee('School-Wide Overview')
            ->assertSee('Students and Classes')
            ->assertSee('Admissions')
            ->assertSee('Attendance')
            ->assertSee('Finance')
            ->assertSee('LMS and CBT')
            ->assertSee('Live Classes')
            ->assertSee('Communications')
            ->assertSee('Offline and Operations')
            ->assertSee('Existing Export Links')
            ->assertSee('Privacy Boundaries')
            ->assertSee('Reports Pack Available');
    }

    public function test_non_admin_school_roles_cannot_access_full_reports_center(): void
    {
        $context = $this->reportsContext('school_admin');

        foreach (['teacher', 'accountant', 'result_officer', 'parent', 'student'] as $role) {
            $user = $this->createUserForSchool($context['school'], $role);
            $this->actAsSchoolRole($user, $context['school'], $role);

            $this->get(route('school.reports.index'))->assertForbidden();
        }
    }

    public function test_reports_center_is_school_scoped_and_reuses_existing_finance_and_attendance_data(): void
    {
        $context = $this->reportsContext('school_admin', [
            'finance_total' => 100000,
            'finance_paid' => 40000,
        ]);
        $other = $this->reportsContext('school_admin', [
            'finance_total' => 999999,
            'finance_paid' => 999999,
        ]);
        $this->createStudent($other['school'], $other['class'], 'CROSS-001', 'Cross', 'Student');

        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.reports.index', [
            'date_from' => '2026-06-11',
            'date_to' => '2026-06-11',
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
        ]))
            ->assertOk()
            ->assertSee('100,000.00')
            ->assertSee('40,000.00')
            ->assertSee('60,000.00')
            ->assertSee('Present')
            ->assertSee('Absent')
            ->assertDontSee('999,999.00')
            ->assertDontSee('Cross Student');
    }

    public function test_reports_center_does_not_expose_private_payloads_or_secrets(): void
    {
        $context = $this->reportsContext('school_admin', [
            'cbt_answer_secret' => 'SECRET-CBT-ANSWER-DO-NOT-SHOW',
            'meeting_password' => 'SECRET-MEETING-PASSWORD',
            'notification_secret' => 'SECRET-NOTIFICATION-PAYLOAD',
            'finance_reference' => 'SECRET-FINANCE-REFERENCE',
            'admission_token' => 'SECRET-ADMISSION-TOKEN',
        ]);
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.reports.index'))
            ->assertOk()
            ->assertDontSee('SECRET-CBT-ANSWER-DO-NOT-SHOW')
            ->assertDontSee('SECRET-MEETING-PASSWORD')
            ->assertDontSee('SECRET-NOTIFICATION-PAYLOAD')
            ->assertDontSee('SECRET-FINANCE-REFERENCE')
            ->assertDontSee('SECRET-ADMISSION-TOKEN')
            ->assertSee('CBT answers')
            ->assertSee('payment secrets')
            ->assertSee('meeting passwords');
    }

    public function test_reports_center_access_is_audit_logged_with_safe_filter_metadata(): void
    {
        $context = $this->reportsContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.reports.index', [
            'date_from' => '2026-06-11',
            'date_to' => '2026-06-11',
            'school_class_id' => $context['class']->id,
            'academic_session_id' => $context['session']->id,
            'term_id' => $context['term']->id,
            'status' => 'present',
        ]))->assertOk();

        $log = AuditLog::query()
            ->where('school_id', $context['school']->id)
            ->where('user_id', $context['user']->id)
            ->where('action', 'school_reports_viewed')
            ->firstOrFail();

        $this->assertTrue($log->metadata['filters_used']);
        $this->assertSame('2026-06-11', $log->metadata['date_from']);
        $this->assertSame('2026-06-11', $log->metadata['date_to']);
        $this->assertSame($context['class']->id, $log->metadata['school_class_id']);
        $this->assertSame($context['session']->id, $log->metadata['academic_session_id']);
        $this->assertSame($context['term']->id, $log->metadata['term_id']);
        $this->assertSame('present', $log->metadata['status']);
        $this->assertStringNotContainsString('SECRET', json_encode($log->metadata));
    }

    public function test_dashboard_and_sidebar_link_reports_center_only_for_school_admin_boundary(): void
    {
        config([
            'standalone.product_edition' => 'standalone',
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        $context = $this->reportsContext('school_admin');
        $this->actAsSchoolRole($context['user'], $context['school'], 'school_admin');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Reports Center')
            ->assertSee('Reports Pack');

        foreach (['teacher', 'accountant', 'result_officer'] as $role) {
            $user = $this->createUserForSchool($context['school'], $role);
            $this->actAsSchoolRole($user, $context['school'], $role);

            $this->get(route('school.dashboard'))
                ->assertOk()
                ->assertDontSee('Reports Center');
        }
    }

    private function reportsContext(string $role, array $overrides = []): array
    {
        $school = $this->createSchool();
        $user = $this->createUserForSchool($school, $role);
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
            'assignment_type' => 'core',
            'is_elective' => false,
            'status' => 'active',
        ]);
        $session = AcademicSession::create([
            'school_id' => $school->id,
            'name' => fake()->unique()->bothify('2026/2027 ###'),
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
        $student = $this->createStudent($school, $class, fake()->unique()->bothify('REP-###'), 'Ada', 'Student');

        $this->createAdmissionsData($school, $class, $session, $overrides);
        $this->createAttendanceData($school, $class, $session, $term, $student, $user);
        $this->createFinanceData($school, $class, $session, $term, $student, $user, $overrides);
        $learning = $this->createLearningData($school, $class, $subject, $session, $term, $student, $user, $overrides);
        $this->createLiveClassData($school, $class, $subject, $session, $term, $user, $learning, $overrides);
        $this->createCommunicationData($school, $user, $overrides);
        $this->createOfflineSyncData($school, $user);

        return compact('school', 'user', 'class', 'subject', 'session', 'term', 'student') + $learning;
    }

    private function createAdmissionsData(School $school, SchoolClass $class, AcademicSession $session, array $overrides): void
    {
        $cycle = AdmissionCycle::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'name' => '2026 Admissions',
            'is_open' => true,
        ]);

        AdmissionApplication::create([
            'school_id' => $school->id,
            'admission_cycle_id' => $cycle->id,
            'application_number' => fake()->unique()->bothify('ADM-###'),
            'tracking_token' => $overrides['admission_token'] ?? Str::random(40),
            'first_name' => 'Applicant',
            'last_name' => 'One',
            'requested_class_id' => $class->id,
            'status' => AdmissionApplication::STATUS_SUBMITTED,
            'payment_status' => AdmissionApplication::PAYMENT_PENDING,
            'submitted_at' => '2026-06-11 09:00:00',
            'meta' => ['private_note' => 'SECRET-ADMISSION-META'],
        ]);
    }

    private function createAttendanceData(
        School $school,
        SchoolClass $class,
        AcademicSession $session,
        Term $term,
        Student $student,
        User $user
    ): void {
        StudentAttendanceRecord::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'student_id' => $student->id,
            'recorded_by' => $user->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'attendance_date' => '2026-06-11',
            'status' => StudentAttendanceRecord::STATUS_PRESENT,
            'source' => 'web',
        ]);
    }

    private function createFinanceData(
        School $school,
        SchoolClass $class,
        AcademicSession $session,
        Term $term,
        Student $student,
        User $user,
        array $overrides
    ): void {
        FinanceFeeItem::create([
            'school_id' => $school->id,
            'name' => 'Tuition',
            'code' => fake()->unique()->lexify('FEE???'),
            'default_amount' => $overrides['finance_total'] ?? 100000,
            'is_active' => true,
        ]);

        $total = (float) ($overrides['finance_total'] ?? 100000);
        $paid = (float) ($overrides['finance_paid'] ?? 40000);

        $invoice = StudentFeeInvoice::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'invoice_number' => fake()->unique()->bothify('INV-####'),
            'status' => $paid >= $total ? StudentFeeInvoice::STATUS_PAID : StudentFeeInvoice::STATUS_PART_PAID,
            'total_amount' => $total,
            'discount_amount' => 0,
            'paid_amount' => $paid,
            'balance_amount' => max($total - $paid, 0),
            'due_date' => '2026-06-10',
            'issued_at' => '2026-06-11 08:00:00',
            'created_by' => $user->id,
            'metadata' => ['private_gateway_payload' => 'SECRET-GATEWAY-PAYLOAD'],
        ]);

        StudentFeePayment::create([
            'school_id' => $school->id,
            'student_fee_invoice_id' => $invoice->id,
            'student_id' => $student->id,
            'amount' => $paid,
            'payment_date' => '2026-06-11',
            'method' => 'cash',
            'reference' => $overrides['finance_reference'] ?? null,
            'received_by' => $user->id,
            'note' => 'SECRET-FINANCE-NOTE',
        ]);
    }

    private function createLearningData(
        School $school,
        SchoolClass $class,
        Subject $subject,
        AcademicSession $session,
        Term $term,
        Student $student,
        User $user,
        array $overrides
    ): array {
        $classroom = LmsClassroom::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'title' => 'JSS 1 Mathematics',
            'status' => LmsClassroom::STATUS_ACTIVE,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        $material = LmsMaterial::create([
            'school_id' => $school->id,
            'lms_classroom_id' => $classroom->id,
            'teacher_user_id' => $user->id,
            'title' => 'Published Lesson',
            'body' => 'Safe lesson summary.',
            'type' => LmsMaterial::TYPE_LESSON,
            'status' => LmsMaterial::STATUS_PUBLISHED,
            'published_at' => '2026-06-11 10:00:00',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        $bank = CbtQuestionBank::create([
            'school_id' => $school->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'title' => 'Math Bank '.fake()->unique()->numberBetween(1, 9999),
            'code' => fake()->unique()->lexify('CBT???'),
            'difficulty' => 'medium',
            'status' => 'active',
            'is_reusable' => true,
        ]);
        $question = CbtQuestion::create([
            'school_id' => $school->id,
            'cbt_question_bank_id' => $bank->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'question_type' => 'theory',
            'prompt' => 'Safe prompt',
            'default_locale' => 'en',
            'direction' => 'ltr',
            'difficulty' => 'easy',
            'default_marks' => 10,
            'status' => 'active',
        ]);
        $exam = CbtExam::create([
            'school_id' => $school->id,
            'subject_id' => $subject->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'title' => 'Math CBT '.fake()->unique()->numberBetween(1, 9999),
            'slug' => 'math-cbt-'.fake()->unique()->numberBetween(1, 999999),
            'exam_type' => 'theory',
            'access_type' => 'internal_student',
            'result_type' => 'cbt_result',
            'status' => 'open',
            'starts_at' => '2026-06-11 08:00:00',
            'ends_at' => '2026-06-11 09:00:00',
            'duration_minutes' => 30,
            'question_count' => 1,
            'total_marks' => 10,
        ]);
        $examQuestion = CbtExamQuestion::create([
            'school_id' => $school->id,
            'cbt_exam_id' => $exam->id,
            'cbt_question_id' => $question->id,
            'marks' => 10,
            'sort_order' => 1,
            'is_required' => true,
        ]);
        $attempt = CbtAttempt::create([
            'attempt_uuid' => (string) Str::uuid(),
            'school_id' => $school->id,
            'cbt_exam_id' => $exam->id,
            'student_id' => $student->id,
            'user_id' => $user->id,
            'attempt_no' => 1,
            'status' => 'submitted',
            'access_channel' => 'internal',
            'started_at' => '2026-06-11 08:05:00',
            'submitted_at' => '2026-06-11 08:40:00',
            'client_snapshot' => ['secret' => 'SECRET-CLIENT-SNAPSHOT'],
            'security_snapshot' => ['secret' => 'SECRET-SECURITY-SNAPSHOT'],
        ]);
        CbtAttemptAnswer::create([
            'school_id' => $school->id,
            'cbt_attempt_id' => $attempt->id,
            'cbt_exam_question_id' => $examQuestion->id,
            'cbt_question_id' => $question->id,
            'question_type' => 'theory',
            'answer_payload' => ['secret' => $overrides['cbt_answer_secret'] ?? 'SECRET-CBT-ANSWER'],
            'answer_text' => $overrides['cbt_answer_secret'] ?? 'SECRET-CBT-ANSWER',
            'status' => 'submitted',
        ]);
        LmsCbtActivity::create([
            'school_id' => $school->id,
            'lms_classroom_id' => $classroom->id,
            'lms_material_id' => $material->id,
            'cbt_exam_id' => $exam->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'target_type' => LmsCbtActivity::TARGET_MATERIAL,
            'target_id' => $material->id,
            'title' => 'Linked CBT',
            'status' => LmsCbtActivity::STATUS_ACTIVE,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return compact('classroom', 'material', 'exam');
    }

    private function createLiveClassData(
        School $school,
        SchoolClass $class,
        Subject $subject,
        AcademicSession $session,
        Term $term,
        User $user,
        array $learning,
        array $overrides
    ): void {
        LiveClass::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'lms_classroom_id' => $learning['classroom']->id,
            'lms_material_id' => $learning['material']->id,
            'teacher_user_id' => $user->id,
            'title' => 'Safe Live Class',
            'provider' => LiveClass::PROVIDER_MANUAL,
            'meeting_url' => 'https://meet.example.test/safe-class',
            'meeting_password' => $overrides['meeting_password'] ?? 'SECRET-MEETING',
            'starts_at' => '2026-06-11 12:00:00',
            'ends_at' => '2026-06-11 13:00:00',
            'timezone' => 'Africa/Lagos',
            'status' => LiveClass::STATUS_SCHEDULED,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'metadata' => ['provider_payload' => 'SECRET-PROVIDER-PAYLOAD'],
        ]);
    }

    private function createCommunicationData(School $school, User $user, array $overrides): void
    {
        $template = SchoolNotificationTemplate::create([
            'school_id' => $school->id,
            'template_key' => fake()->unique()->bothify('reports.template.###'),
            'title' => 'Report Template',
            'body' => 'Safe body',
            'channel' => SchoolNotificationTemplate::CHANNEL_DATABASE,
            'audience_type' => SchoolNotificationTemplate::AUDIENCE_SCHOOL_ADMIN,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        SchoolNotificationLog::create([
            'school_id' => $school->id,
            'template_id' => $template->id,
            'event_type' => 'reports.test',
            'channel' => SchoolNotificationLog::CHANNEL_DATABASE,
            'recipient_type' => 'school_admin',
            'recipient_name' => 'School Admins',
            'subject' => 'Safe subject',
            'message_summary' => 'Safe report message summary',
            'status' => SchoolNotificationLog::STATUS_LOGGED,
            'created_by' => $user->id,
            'metadata' => ['private_payload' => $overrides['notification_secret'] ?? 'SECRET-NOTIFICATION'],
        ]);
    }

    private function createOfflineSyncData(School $school, User $user): void
    {
        AttendanceOfflineSyncReceipt::create([
            'school_id' => $school->id,
            'client_uuid' => (string) Str::uuid(),
            'processed_by' => $user->id,
            'payload_hash' => hash('sha256', 'reports-test'),
            'result_status' => 'synced',
            'processed_at' => '2026-06-11 14:00:00',
        ]);
    }

    private function createSchool(): School
    {
        $id = fake()->unique()->numberBetween(1, 999999);

        return School::create([
            'name' => 'Sanfaani Reports Academy '.$id,
            'slug' => 'sanfaani-reports-academy-'.$id,
            'email' => 'reports'.$id.'@example.test',
            'phone' => '08030000000',
            'address' => 'Ilorin',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);
    }

    private function createStudent(
        School $school,
        SchoolClass $class,
        string $admissionNumber,
        string $firstName,
        string $lastName
    ): Student {
        return Student::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => $admissionNumber,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'status' => 'active',
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
