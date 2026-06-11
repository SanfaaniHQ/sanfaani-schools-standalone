<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\AuditLog;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendanceRecord;
use App\Models\StudentFeeInvoice;
use App\Models\StudentFeePayment;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ImportExportToolsTest extends TestCase
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

    public function test_import_export_routes_require_authentication(): void
    {
        $this->get(route('school.import-export.index'))
            ->assertRedirect(route('login'));

        $this->get(route('school.import-export.students.export'))
            ->assertRedirect(route('login'));

        $this->get(route('school.import-export.students.template'))
            ->assertRedirect(route('login'));

        $this->post(route('school.import-export.students.preview'), [])
            ->assertRedirect(route('login'));

        $this->get(route('school.import-export.attendance.export'))
            ->assertRedirect(route('login'));

        $this->get(route('school.import-export.finance.export'))
            ->assertRedirect(route('login'));
    }

    public function test_role_boundaries_for_import_export_tools(): void
    {
        [$school, $admin] = $this->schoolContext('school_admin');
        $this->createClass($school, 'JSS 1', 'A');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.import-export.index'))
            ->assertOk()
            ->assertSee('Import / Export')
            ->assertSee('Student Import Preview')
            ->assertSee('Attendance Summary')
            ->assertSee('Finance Summary');

        $accountant = $this->createUserForSchool($school, 'accountant');
        $this->actAsSchoolRole($accountant, $school, 'accountant');

        $this->get(route('school.import-export.index'))
            ->assertOk()
            ->assertSee('Finance Summary')
            ->assertDontSee('Student Import Preview')
            ->assertDontSee('Attendance Summary');

        $this->get(route('school.import-export.finance.export'))
            ->assertOk();

        $this->get(route('school.import-export.students.export'))
            ->assertForbidden();

        $this->get(route('school.import-export.students.template'))
            ->assertForbidden();

        $this->post(route('school.import-export.students.preview'), [
            'student_file' => UploadedFile::fake()->createWithContent('students.csv', $this->studentCsv()),
        ])->assertForbidden();

        $this->get(route('school.import-export.attendance.export'))
            ->assertForbidden();
    }

    public function test_disallowed_roles_cannot_access_import_export_workspace(): void
    {
        [$school] = $this->schoolContext('school_admin');

        foreach (['teacher', 'result_officer', 'parent', 'student'] as $role) {
            $user = $this->createUserForSchool($school, $role);
            $this->actAsSchoolRole($user, $school, $role);

            $this->get(route('school.import-export.index'))
                ->assertForbidden();

            $this->get(route('school.import-export.finance.export'))
                ->assertForbidden();
        }
    }

    public function test_student_export_is_school_scoped_and_excludes_sensitive_fields(): void
    {
        [$school, $admin] = $this->schoolContext('school_admin');
        $class = $this->createClass($school, 'JSS 1', 'A');
        $student = $this->createStudent($school, $class, 'ADM-001', 'Aisha', [
            'guardian_name' => 'Mr Bello',
            'guardian_phone' => '08012345678',
            'guardian_email' => 'guardian@example.test',
            'address' => 'Private family address',
        ]);
        [$otherSchool] = $this->schoolContext('school_admin');
        $otherClass = $this->createClass($otherSchool, 'JSS 1', 'B');
        $this->createStudent($otherSchool, $otherClass, 'OTHER-001', 'Other');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $response = $this->get(route('school.import-export.students.export', [
            'school_class_id' => $class->id,
            'search' => 'Aisha',
        ]));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('admission_number,first_name,middle_name,last_name,gender,date_of_birth,class,guardian_name,guardian_phone,guardian_email,status', $content);
        $this->assertStringContainsString($student->admission_number, $content);
        $this->assertStringContainsString('Aisha', $content);
        $this->assertStringNotContainsString('OTHER-001', $content);
        $this->assertStringNotContainsString('Private family address', $content);
        $this->assertStringNotContainsString('password', strtolower($content));
        $this->assertStringNotContainsString('metadata', strtolower($content));

        $audit = AuditLog::where('school_id', $school->id)
            ->where('action', 'import_export_students_exported')
            ->firstOrFail();

        $this->assertTrue($audit->metadata['filters']['has_search']);
        $this->assertArrayNotHasKey('search', $audit->metadata['filters']);
    }

    public function test_student_template_downloads_expected_headers_and_audits(): void
    {
        [$school, $admin] = $this->schoolContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $response = $this->get(route('school.import-export.students.template'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('admission_number,first_name,middle_name,last_name,gender,date_of_birth,class,guardian_name,guardian_phone,guardian_email,status', $content);
        $this->assertStringContainsString('SCH/2026/001', $content);
        $this->assertStringNotContainsString('password', strtolower($content));

        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'action' => 'import_export_student_template_downloaded',
        ]);
    }

    public function test_attendance_export_is_school_scoped_filtered_and_summary_only(): void
    {
        [$school, $admin] = $this->schoolContext('school_admin');
        [$session, $term] = $this->createSessionAndTerm($school);
        $class = $this->createClass($school, 'JSS 1', 'A');
        $otherClass = $this->createClass($school, 'JSS 2', 'B');
        $studentOne = $this->createStudent($school, $class, 'ATT-001', 'Ada');
        $studentTwo = $this->createStudent($school, $class, 'ATT-002', 'Bala');
        $studentThree = $this->createStudent($school, $otherClass, 'ATT-003', 'Chidi');
        $this->createAttendance($school, $class, $studentOne, $session, $term, 'present', '2026-07-01', 'private note');
        $this->createAttendance($school, $class, $studentTwo, $session, $term, 'late', '2026-07-01', 'late note');
        $this->createAttendance($school, $otherClass, $studentThree, $session, $term, 'absent', '2026-07-01', 'other note');

        [$otherSchool] = $this->schoolContext('school_admin');
        [$otherSession, $otherTerm] = $this->createSessionAndTerm($otherSchool);
        $externalClass = $this->createClass($otherSchool, 'External', 'A');
        $externalStudent = $this->createStudent($otherSchool, $externalClass, 'EXT-001', 'External');
        $this->createAttendance($otherSchool, $externalClass, $externalStudent, $otherSession, $otherTerm, 'present', '2026-07-01', 'external private');

        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $response = $this->get(route('school.import-export.attendance.export', [
            'date' => '2026-07-01',
            'school_class_id' => $class->id,
        ]));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('attendance_date,class,present,absent,late,excused,total', $content);
        $this->assertStringContainsString('2026-07-01,"JSS 1 A",1,0,1,0,2', $content);
        $this->assertStringNotContainsString('JSS 2 B', $content);
        $this->assertStringNotContainsString('EXT-001', $content);
        $this->assertStringNotContainsString('private note', $content);
        $this->assertStringNotContainsString('metadata', strtolower($content));

        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'action' => 'import_export_attendance_exported',
        ]);
    }

    public function test_finance_export_is_school_scoped_filtered_and_excludes_private_payment_values(): void
    {
        [$school, $admin] = $this->schoolContext('school_admin');
        [$session, $term] = $this->createSessionAndTerm($school);
        $class = $this->createClass($school, 'JSS 1', 'A');
        $otherClass = $this->createClass($school, 'JSS 2', 'B');
        $student = $this->createStudent($school, $class, 'FIN-001', 'Fatima');
        $otherStudent = $this->createStudent($school, $otherClass, 'FIN-002', 'Other');
        $invoice = $this->createInvoice($school, $class, $session, $term, $student, 'INV-001');
        $this->createPayment($school, $invoice, $student, 'cash', 'SECRET-REF', 'contains-private-payment-note');
        $this->createInvoice($school, $otherClass, $session, $term, $otherStudent, 'INV-OTHER');

        [$externalSchool] = $this->schoolContext('school_admin');
        [$externalSession, $externalTerm] = $this->createSessionAndTerm($externalSchool);
        $externalClass = $this->createClass($externalSchool, 'External', 'A');
        $externalStudent = $this->createStudent($externalSchool, $externalClass, 'EXT-FIN', 'External');
        $this->createInvoice($externalSchool, $externalClass, $externalSession, $externalTerm, $externalStudent, 'INV-EXT');

        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $response = $this->get(route('school.import-export.finance.export', [
            'school_class_id' => $class->id,
            'payment_method' => 'cash',
        ]));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('record_type,invoice_number,student_admission_number,student_name,class,session,term,status,total_amount,discount_amount,paid_amount,balance_amount,due_date,issued_at,payment_date,payment_method,payment_amount', $content);
        $this->assertStringContainsString('invoice,INV-001,FIN-001', $content);
        $this->assertStringContainsString('payment,INV-001,FIN-001', $content);
        $this->assertStringContainsString(',cash,5000.00', $content);
        $this->assertStringNotContainsString('INV-OTHER', $content);
        $this->assertStringNotContainsString('INV-EXT', $content);
        $this->assertStringNotContainsString('SECRET-REF', $content);
        $this->assertStringNotContainsString('contains-private-payment-note', $content);
        $this->assertStringNotContainsString('raw-finance-secret', $content);
        $this->assertStringNotContainsString('metadata', strtolower($content));

        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'action' => 'import_export_finance_exported',
        ]);
    }

    public function test_student_import_preview_validates_file_and_rows_without_writing_records(): void
    {
        [$school, $admin] = $this->schoolContext('school_admin');
        $class = $this->createClass($school, 'JSS 1', 'A');
        $this->createStudent($school, $class, 'ADM-EXISTS', 'Existing');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->from(route('school.import-export.index'))
            ->post(route('school.import-export.students.preview'), [
                'student_file' => UploadedFile::fake()->create('students.pdf', 1, 'application/pdf'),
            ])
            ->assertRedirect(route('school.import-export.index'))
            ->assertSessionHasErrors('student_file');

        $invalidCsv = $this->studentCsv([
            ['ADM-EXISTS', 'Existing', '', 'Student', 'female', '', 'JSS 1', '', '', '', 'active'],
            ['ADM-NEW', 'New', '', 'Student', 'female', '', 'Missing Class', '', '', '', 'active'],
            ['ADM-NEW', 'Duplicate', '', 'Student', 'female', '', 'JSS 1', '', '', '', 'active'],
            ['ADM-NEW', 'Duplicate', '', 'Student', 'female', '', 'JSS 1', '', '', '', 'active'],
        ]);

        $this->post(route('school.import-export.students.preview'), [
            'student_file' => UploadedFile::fake()->createWithContent('students.csv', $invalidCsv),
        ])
            ->assertRedirect(route('school.import-export.index'))
            ->assertSessionHas('warning');

        $this->assertDatabaseMissing('students', [
            'school_id' => $school->id,
            'admission_number' => 'ADM-NEW',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'action' => 'import_export_students_import_validation_failed',
        ]);
    }

    public function test_student_import_preview_and_commit_create_students_safely(): void
    {
        [$school, $admin] = $this->schoolContext('school_admin');
        $this->createSessionAndTerm($school);
        $class = $this->createClass($school, 'JSS 1', 'A');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->post(route('school.import-export.students.preview'), [
            'student_file' => UploadedFile::fake()->createWithContent('students.csv', $this->studentCsv([
                ['ADM-010', 'Amina', '', 'Yusuf', 'female', '2015-01-15', 'JSS 1', 'Mrs Yusuf', '08030000000', 'guardian@example.test', 'active'],
            ])),
        ])
            ->assertRedirect(route('school.import-export.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('students', [
            'school_id' => $school->id,
            'admission_number' => 'ADM-010',
        ]);

        $pending = session('student_import_preview');
        $this->assertNotEmpty($pending['token']);

        $this->post(route('school.import-export.students.import'), [
            'token' => $pending['token'],
        ])
            ->assertRedirect(route('school.import-export.index'))
            ->assertSessionHas('success');

        $student = Student::where('school_id', $school->id)
            ->where('admission_number', 'ADM-010')
            ->firstOrFail();

        $this->assertSame($class->id, $student->school_class_id);
        $this->assertDatabaseHas('student_class_enrollments', [
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'action' => 'import_export_students_import_previewed',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'school_id' => $school->id,
            'action' => 'import_export_students_import_committed',
        ]);
        $this->assertNull(session('student_import_preview'));
    }

    public function test_student_import_preview_rejects_too_many_rows(): void
    {
        [$school, $admin] = $this->schoolContext('school_admin');
        $this->createClass($school, 'JSS 1', 'A');
        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $rows = [];

        for ($i = 1; $i <= 201; $i++) {
            $rows[] = ['ADM-'.$i, 'Student'.$i, '', 'Bulk', 'female', '', 'JSS 1', '', '', '', 'active'];
        }

        $this->post(route('school.import-export.students.preview'), [
            'student_file' => UploadedFile::fake()->createWithContent('students.csv', $this->studentCsv($rows)),
        ])
            ->assertRedirect(route('school.import-export.index'))
            ->assertSessionHas('warning');

        $this->assertDatabaseCount('students', 0);

        $audit = AuditLog::where('school_id', $school->id)
            ->where('action', 'import_export_students_import_validation_failed')
            ->latest()
            ->firstOrFail();

        $this->assertSame(201, $audit->metadata['row_count']);
        $this->assertSame(200, $audit->metadata['valid_count']);
    }

    private function schoolContext(string $role): array
    {
        $school = School::create([
            'name' => fake()->unique()->company().' Academy',
            'slug' => fake()->unique()->slug(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '08030000000',
            'address' => 'Ilorin',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        return [$school, $this->createUserForSchool($school, $role)];
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

    private function createClass(School $school, string $name, string $section): SchoolClass
    {
        return SchoolClass::create([
            'school_id' => $school->id,
            'name' => $name,
            'section' => $section,
            'status' => 'active',
        ]);
    }

    private function createStudent(School $school, SchoolClass $class, string $admissionNumber, string $firstName, array $overrides = []): Student
    {
        return Student::create(array_merge([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => $admissionNumber,
            'first_name' => $firstName,
            'last_name' => 'Student',
            'gender' => 'female',
            'status' => 'active',
        ], $overrides));
    }

    private function createSessionAndTerm(School $school): array
    {
        $session = AcademicSession::create([
            'school_id' => $school->id,
            'name' => fake()->unique()->bothify('2026/####'),
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

        return [$session, $term];
    }

    private function createAttendance(
        School $school,
        SchoolClass $class,
        Student $student,
        AcademicSession $session,
        Term $term,
        string $status,
        string $date,
        string $note
    ): StudentAttendanceRecord {
        return StudentAttendanceRecord::create([
            'school_id' => $school->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'school_class_id' => $class->id,
            'student_id' => $student->id,
            'attendance_date' => $date,
            'status' => $status,
            'note' => $note,
            'source' => 'web',
            'metadata' => ['raw_offline_payload' => 'private-offline-secret'],
        ]);
    }

    private function createInvoice(
        School $school,
        SchoolClass $class,
        AcademicSession $session,
        Term $term,
        Student $student,
        string $invoiceNumber
    ): StudentFeeInvoice {
        return StudentFeeInvoice::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'invoice_number' => $invoiceNumber,
            'status' => StudentFeeInvoice::STATUS_PART_PAID,
            'total_amount' => 10000,
            'discount_amount' => 0,
            'paid_amount' => 5000,
            'balance_amount' => 5000,
            'due_date' => '2026-07-31',
            'issued_at' => '2026-07-01 09:00:00',
            'metadata' => ['raw-finance-secret' => 'hidden'],
        ]);
    }

    private function createPayment(
        School $school,
        StudentFeeInvoice $invoice,
        Student $student,
        string $method,
        string $reference,
        string $note
    ): StudentFeePayment {
        return StudentFeePayment::create([
            'school_id' => $school->id,
            'student_fee_invoice_id' => $invoice->id,
            'student_id' => $student->id,
            'amount' => 5000,
            'payment_date' => '2026-07-03',
            'method' => $method,
            'reference' => $reference,
            'note' => $note,
            'metadata' => ['raw-finance-secret' => 'hidden'],
        ]);
    }

    private function studentCsv(array $rows = []): string
    {
        $rows = $rows ?: [
            ['ADM-001', 'Aisha', '', 'Bello', 'female', '2015-09-12', 'JSS 1', 'Mr Bello', '08012345678', 'guardian@example.test', 'active'],
        ];

        $lines = [
            'admission_number,first_name,middle_name,last_name,gender,date_of_birth,class,guardian_name,guardian_phone,guardian_email,status',
        ];

        foreach ($rows as $row) {
            $handle = fopen('php://temp', 'r+');
            fputcsv($handle, $row);
            rewind($handle);
            $lines[] = rtrim(stream_get_contents($handle));
            fclose($handle);
        }

        return implode("\n", $lines)."\n";
    }
}
