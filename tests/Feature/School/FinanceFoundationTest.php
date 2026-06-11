<?php

namespace Tests\Feature\School;

use App\Models\AcademicSession;
use App\Models\AuditLog;
use App\Models\FinanceFeeAssignment;
use App\Models\FinanceFeeItem;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFeeInvoice;
use App\Models\Term;
use App\Models\User;
use App\Models\UserSchoolRole;
use App\Services\Finance\SchoolFinanceReportService;
use App\Services\Finance\SchoolFinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FinanceFoundationTest extends TestCase
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

    public function test_finance_routes_require_authentication(): void
    {
        $this->get(route('school.finance.index'))
            ->assertRedirect(route('login'));

        $this->get(route('school.finance.reports'))
            ->assertRedirect(route('login'));

        $this->get(route('school.finance.audit'))
            ->assertRedirect(route('login'));

        $this->post(route('school.finance.fee-items.store'), [])
            ->assertRedirect(route('login'));
    }

    public function test_school_admin_and_accountant_can_access_finance_dashboard(): void
    {
        [$school, $admin] = $this->schoolContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.finance.index'))
            ->assertOk()
            ->assertSee('Fees & Finance')
            ->assertSee('manual payments')
            ->assertSee('Finance reports, finance CSV export, and audit review are available')
            ->assertSee('offline fee capture are deferred');

        $this->get(route('school.finance.reports'))
            ->assertOk()
            ->assertSee('Finance Reports')
            ->assertSee('Use Import / Export for selected finance CSV export');

        $accountant = $this->createUserForSchool($school, 'accountant');
        $this->actAsSchoolRole($accountant, $school, 'accountant');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Accountant Dashboard')
            ->assertSee('Fees and accounting foundation')
            ->assertSee('finance reports, CSV finance export, and audit review are available')
            ->assertSee('Export CSV');

        $this->get(route('school.finance.audit'))
            ->assertOk()
            ->assertSee('Finance Audit Review');
    }

    public function test_unauthorized_school_roles_cannot_manage_finance(): void
    {
        [$school] = $this->schoolContext('school_admin');

        foreach (['teacher', 'result_officer', 'parent', 'student'] as $role) {
            $user = $this->createUserForSchool($school, $role);
            $this->actAsSchoolRole($user, $school, $role);

            $this->get(route('school.finance.index'))->assertForbidden();
            $this->get(route('school.finance.reports'))->assertForbidden();
            $this->get(route('school.finance.audit'))->assertForbidden();

            $this->post(route('school.finance.fee-items.store'), [
                'name' => 'Tuition',
                'default_amount' => 50000,
            ])->assertForbidden();
        }
    }

    public function test_fee_item_assignment_and_student_invoice_can_be_created(): void
    {
        [$school, $admin, $class, $session, $term, $student] = $this->financeContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->post(route('school.finance.fee-items.store'), [
            'name' => 'Tuition',
            'code' => 'tuition',
            'default_amount' => 50000,
            'description' => 'Term tuition fee',
            'is_active' => '1',
        ])->assertRedirect(route('school.finance.fee-items.index'));

        $feeItem = FinanceFeeItem::firstOrFail();
        $this->assertSame('TUITION', $feeItem->code);

        $this->post(route('school.finance.assignments.store'), [
            'fee_item_id' => $feeItem->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'school_class_id' => $class->id,
            'amount' => 50000,
            'due_date' => '2026-07-15',
            'is_active' => '1',
        ])->assertRedirect(route('school.finance.assignments.index'));

        $this->assertDatabaseHas('finance_fee_assignments', [
            'school_id' => $school->id,
            'fee_item_id' => $feeItem->id,
            'school_class_id' => $class->id,
        ]);

        $this->post(route('school.finance.invoices.generate'), [
            'student_id' => $student->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'due_date' => '2026-07-20',
        ])->assertRedirect();

        $invoice = StudentFeeInvoice::with('items')->firstOrFail();
        $this->assertSame($school->id, $invoice->school_id);
        $this->assertSame($student->id, $invoice->student_id);
        $this->assertSame(StudentFeeInvoice::STATUS_ISSUED, $invoice->status);
        $this->assertEquals(50000.00, (float) $invoice->total_amount);
        $this->assertEquals(50000.00, (float) $invoice->balance_amount);
        $this->assertCount(1, $invoice->items);
    }

    public function test_class_fee_invoices_can_be_generated_without_duplicates(): void
    {
        [$school, $admin, $class, $session, $term] = $this->financeContext('school_admin');
        $this->createStudent($school, $class, 'FIN-002', 'Second');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $feeItem = $this->createFeeItem($school, $admin, 'Exam Fee', 10000);
        $this->createAssignment($school, $admin, $feeItem, $class, $session, $term, 10000);

        $payload = [
            'school_class_id' => $class->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
        ];

        $this->post(route('school.finance.invoices.generate'), $payload)
            ->assertRedirect(route('school.finance.invoices.index', ['school_class_id' => $class->id]));

        $this->assertDatabaseCount('student_fee_invoices', 2);
        $this->assertDatabaseCount('student_fee_invoice_items', 2);

        $this->post(route('school.finance.invoices.generate'), $payload)
            ->assertRedirect(route('school.finance.invoices.index', ['school_class_id' => $class->id]));

        $this->assertDatabaseCount('student_fee_invoices', 2);
        $this->assertDatabaseCount('student_fee_invoice_items', 2);
    }

    public function test_payment_recording_updates_invoice_status_and_rejects_overpayment(): void
    {
        [$school, $admin, , , , , $invoice] = $this->invoiceContext();
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->post(route('school.finance.invoices.payments.store', $invoice), [
            'amount' => 40000,
            'payment_date' => '2026-07-01',
            'method' => 'cash',
            'reference' => 'RCPT-001',
            'note' => 'First instalment',
        ])->assertRedirect(route('school.finance.invoices.show', $invoice));

        $invoice->refresh();
        $this->assertSame(StudentFeeInvoice::STATUS_PART_PAID, $invoice->status);
        $this->assertEquals(40000.00, (float) $invoice->paid_amount);
        $this->assertEquals(60000.00, (float) $invoice->balance_amount);

        $this->from(route('school.finance.invoices.show', $invoice))
            ->post(route('school.finance.invoices.payments.store', $invoice), [
                'amount' => 70000,
                'payment_date' => '2026-07-02',
                'method' => 'cash',
            ])
            ->assertRedirect(route('school.finance.invoices.show', $invoice))
            ->assertSessionHasErrors('amount');

        $this->post(route('school.finance.invoices.payments.store', $invoice), [
            'amount' => 60000,
            'payment_date' => '2026-07-03',
            'method' => 'bank_transfer',
            'reference' => 'RCPT-002',
        ])->assertRedirect(route('school.finance.invoices.show', $invoice));

        $invoice->refresh();
        $this->assertSame(StudentFeeInvoice::STATUS_PAID, $invoice->status);
        $this->assertEquals(100000.00, (float) $invoice->paid_amount);
        $this->assertEquals(0.00, (float) $invoice->balance_amount);
    }

    public function test_invalid_zero_or_negative_fee_amounts_are_rejected(): void
    {
        [$school, $admin, , , , , $invoice] = $this->invoiceContext();
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->from(route('school.finance.fee-items.index'))
            ->post(route('school.finance.fee-items.store'), [
                'name' => 'Invalid Fee',
                'default_amount' => -1,
            ])
            ->assertRedirect(route('school.finance.fee-items.index'))
            ->assertSessionHasErrors('default_amount');

        $this->from(route('school.finance.invoices.show', $invoice))
            ->post(route('school.finance.invoices.payments.store', $invoice), [
                'amount' => 0,
                'payment_date' => '2026-07-01',
                'method' => 'manual',
            ])
            ->assertRedirect(route('school.finance.invoices.show', $invoice))
            ->assertSessionHasErrors('amount');
    }

    public function test_cross_school_finance_records_are_blocked(): void
    {
        [$schoolA, $adminA] = $this->schoolContext('school_admin');
        [, , , , , , $invoiceB] = $this->invoiceContext();

        $this->actAsSchoolRole($adminA, $schoolA, 'school_admin');

        $this->get(route('school.finance.invoices.show', $invoiceB))
            ->assertForbidden();

        $this->post(route('school.finance.invoices.payments.store', $invoiceB), [
            'amount' => 100,
            'payment_date' => '2026-07-01',
            'method' => 'manual',
        ])->assertForbidden();

        $this->get(route('school.finance.students.show', $invoiceB->student))
            ->assertForbidden();
    }

    public function test_student_finance_history_shows_authorized_records(): void
    {
        [$school, $admin, , , , $student, $invoice] = $this->invoiceContext();
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.finance.students.show', $student))
            ->assertOk()
            ->assertSee('Student Finance History')
            ->assertSee($invoice->invoice_number)
            ->assertSee('100,000.00');
    }

    public function test_finance_reports_calculate_totals_filters_and_school_scope(): void
    {
        [$school, $admin, $class, $session, $term, $student, $invoice] = $this->invoiceContext();
        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $invoice->forceFill([
            'issued_at' => '2026-07-05 10:00:00',
            'due_date' => '2026-07-20',
        ])->save();

        app(SchoolFinanceService::class)->recordPayment($school, $admin, $invoice, [
            'amount' => 40000,
            'payment_date' => '2026-07-10',
            'method' => 'cash',
            'reference' => 'JULY-CASH-001',
        ]);

        $secondClass = $this->createClass($school, 'JSS 2', fake()->unique()->lexify('??'));
        $secondStudent = $this->createStudent($school, $secondClass, 'FIN-003', 'Bayo');
        $secondFeeItem = $this->createFeeItem($school, $admin, 'Books', 50000);
        $this->createAssignment($school, $admin, $secondFeeItem, $secondClass, $session, $term, 50000);
        $secondInvoice = app(SchoolFinanceService::class)
            ->generateStudentInvoice($school, $admin, $secondStudent, [
                'academic_session_id' => $session->id,
                'term_id' => $term->id,
            ])['invoice'];
        $secondInvoice->forceFill([
            'issued_at' => '2026-08-05 10:00:00',
            'due_date' => '2026-08-20',
        ])->save();

        app(SchoolFinanceService::class)->recordPayment($school, $admin, $secondInvoice, [
            'amount' => 50000,
            'payment_date' => '2026-08-12',
            'method' => 'bank_transfer',
            'reference' => 'AUG-BANK-001',
        ]);

        [$otherSchool, $otherAdmin, $otherClass, $otherSession, $otherTerm, $otherStudent] = $this->financeContext('school_admin');
        $otherStudent->forceFill(['first_name' => 'Cross'])->save();
        $otherFeeItem = $this->createFeeItem($otherSchool, $otherAdmin, 'Other School Fee', 999999);
        $this->createAssignment($otherSchool, $otherAdmin, $otherFeeItem, $otherClass, $otherSession, $otherTerm, 999999);
        $otherInvoice = app(SchoolFinanceService::class)
            ->generateStudentInvoice($otherSchool, $otherAdmin, $otherStudent, [
                'academic_session_id' => $otherSession->id,
                'term_id' => $otherTerm->id,
            ])['invoice'];
        $otherInvoice->forceFill(['issued_at' => '2026-07-05 10:00:00'])->save();

        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $reports = app(SchoolFinanceReportService::class);
        $report = $reports->report($school);

        $this->assertEquals(150000.00, $report['summary']['total_invoiced']);
        $this->assertEquals(90000.00, $report['summary']['total_paid']);
        $this->assertEquals(60000.00, $report['summary']['total_outstanding']);
        $this->assertSame(1, $report['invoice_status_counts'][StudentFeeInvoice::STATUS_PART_PAID]);
        $this->assertSame(1, $report['invoice_status_counts'][StudentFeeInvoice::STATUS_PAID]);
        $this->assertEquals(40000.00, collect($report['payment_methods'])->firstWhere('method', 'cash')['total']);
        $this->assertEquals(50000.00, collect($report['payment_methods'])->firstWhere('method', 'bank_transfer')['total']);
        $this->assertEquals(60000.00, collect($report['outstanding_by_class'])->firstWhere('school_class_id', $class->id)['balance']);
        $this->assertEquals(60000.00, collect($report['student_balances'])->firstWhere('student_id', $student->id)['balance']);
        $this->assertCount(2, $report['recent_payments']);

        $julyReport = $reports->report($school, [
            'date_from' => '2026-07-01',
            'date_to' => '2026-07-31',
        ]);
        $this->assertEquals(100000.00, $julyReport['summary']['total_invoiced']);
        $this->assertEquals(40000.00, $julyReport['summary']['total_paid']);
        $this->assertEquals(60000.00, $julyReport['summary']['total_outstanding']);

        $classStatusReport = $reports->report($school, [
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'school_class_id' => $class->id,
            'invoice_status' => StudentFeeInvoice::STATUS_PART_PAID,
        ]);
        $this->assertEquals(100000.00, $classStatusReport['summary']['total_invoiced']);
        $this->assertEquals(40000.00, $classStatusReport['summary']['total_paid']);

        $bankTransferReport = $reports->report($school, [
            'payment_method' => 'bank_transfer',
        ]);
        $this->assertEquals(50000.00, $bankTransferReport['summary']['total_paid']);

        $this->get(route('school.finance.reports'))
            ->assertOk()
            ->assertSee('Finance Reports')
            ->assertSee('150,000.00')
            ->assertSee('90,000.00')
            ->assertSee('60,000.00')
            ->assertSee('Payment Method Summary')
            ->assertSee('Outstanding By Class')
            ->assertSee('Student Balances')
            ->assertDontSee('999,999.00')
            ->assertDontSee('Cross Student');
    }

    public function test_finance_actions_are_audit_logged_with_safe_metadata(): void
    {
        [$school, $admin, , , , , $invoice] = $this->invoiceContext();
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->post(route('school.finance.invoices.payments.store', $invoice), [
            'amount' => 1000,
            'payment_date' => '2026-07-01',
            'method' => 'manual',
            'reference' => 'SECRET-REFERENCE',
            'note' => 'contains-private-payment-note',
        ])->assertSessionHasNoErrors();

        foreach ([
            'finance_fee_item_created',
            'finance_fee_assignment_created',
            'finance_invoice_generated',
            'finance_payment_recorded',
        ] as $action) {
            $this->assertDatabaseHas('audit_logs', [
                'school_id' => $school->id,
                'user_id' => $admin->id,
                'action' => $action,
            ]);
        }

        $paymentAudit = AuditLog::query()
            ->where('action', 'finance_payment_recorded')
            ->firstOrFail();

        $encoded = json_encode($paymentAudit->metadata);
        $this->assertStringNotContainsString('SECRET-REFERENCE', $encoded);
        $this->assertStringNotContainsString('contains-private-payment-note', $encoded);
        $this->assertTrue($paymentAudit->metadata['has_reference']);

        AuditLog::create([
            'school_id' => $school->id,
            'user_id' => $admin->id,
            'action' => 'student_created',
            'event' => 'student_created',
            'category' => 'student',
            'metadata' => ['secret' => 'raw-student-secret'],
        ]);

        AuditLog::create([
            'school_id' => $school->id,
            'user_id' => $admin->id,
            'action' => 'finance_payment_recorded',
            'event' => 'finance_payment_recorded',
            'category' => 'finance',
            'metadata' => [
                'payment_id' => 999,
                'invoice_id' => $invoice->id,
                'secret' => 'raw-finance-secret',
                'reference' => 'RAW-REFERENCE-SHOULD-NOT-SHOW',
            ],
        ]);

        $this->get(route('school.finance.audit'))
            ->assertOk()
            ->assertSee('Finance Audit Review')
            ->assertSee('Finance Payment Recorded')
            ->assertSee('Payment Id')
            ->assertSee('999')
            ->assertDontSee('student_created')
            ->assertDontSee('raw-student-secret')
            ->assertDontSee('raw-finance-secret')
            ->assertDontSee('RAW-REFERENCE-SHOULD-NOT-SHOW')
            ->assertDontSee('SECRET-REFERENCE')
            ->assertDontSee('contains-private-payment-note');
    }

    public function test_fees_and_accounting_navigation_is_role_aware_and_deferred_boundaries_remain_clear(): void
    {
        config([
            'standalone.product_edition' => 'standalone',
            'standalone.offline_mode' => 'local_first',
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);

        [$school, $admin] = $this->schoolContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');

        $this->get(route('school.dashboard'))
            ->assertOk()
            ->assertSee('Fees &amp; Finance', false)
            ->assertSee('Fees/accounting foundation')
            ->assertSee('Finance reports and audit pack')
            ->assertSee('Finance reports and audit review are available')
            ->assertSee('Import/export tools')
            ->assertDontSee('Payment gateway automation is available');

        $this->assertFalse(Route::has('school.finance.reports.export'));
        $this->assertFalse(Route::has('school.finance.imports.index'));
        $this->assertFalse(Route::has('school.finance.offline-capture'));

        foreach (['teacher', 'result_officer'] as $role) {
            $user = $this->createUserForSchool($school, $role);
            $this->actAsSchoolRole($user, $school, $role);

            $this->get(route('school.dashboard'))
                ->assertOk()
                ->assertDontSee('Fees &amp; Finance', false)
                ->assertDontSee('Finance Operations');
        }
    }

    private function invoiceContext(): array
    {
        [$school, $admin, $class, $session, $term, $student] = $this->financeContext('school_admin');
        $this->actAsSchoolRole($admin, $school, 'school_admin');
        $feeItem = $this->createFeeItem($school, $admin, 'Tuition', 100000);
        $this->createAssignment($school, $admin, $feeItem, $class, $session, $term, 100000);
        $invoice = app(SchoolFinanceService::class)
            ->generateStudentInvoice($school, $admin, $student, [
                'academic_session_id' => $session->id,
                'term_id' => $term->id,
            ])['invoice'];

        return [$school, $admin, $class, $session, $term, $student, $invoice];
    }

    private function financeContext(string $role): array
    {
        [$school, $user] = $this->schoolContext($role);
        $class = $this->createClass($school, 'JSS 1', fake()->unique()->lexify('??'));
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
        $student = $this->createStudent($school, $class, fake()->unique()->bothify('FIN-###'), 'Ada');

        return [$school, $user, $class, $session, $term, $student];
    }

    private function schoolContext(string $role): array
    {
        $school = School::create([
            'name' => fake()->unique()->company().' Academy',
            'slug' => fake()->unique()->slug(),
            'email' => 'school@example.test',
            'phone' => '08030000000',
            'address' => 'Ilorin',
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        return [$school, $this->createUserForSchool($school, $role)];
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

    private function createStudent(School $school, SchoolClass $class, string $admissionNumber, string $firstName): Student
    {
        return Student::create([
            'school_id' => $school->id,
            'school_class_id' => $class->id,
            'admission_number' => $admissionNumber,
            'first_name' => $firstName,
            'last_name' => 'Student',
            'status' => 'active',
        ]);
    }

    private function createFeeItem(School $school, User $user, string $name, float $amount): FinanceFeeItem
    {
        return app(SchoolFinanceService::class)->createFeeItem($school, $user, [
            'name' => $name,
            'code' => fake()->unique()->lexify('???'),
            'default_amount' => $amount,
            'is_active' => true,
        ]);
    }

    private function createAssignment(
        School $school,
        User $user,
        FinanceFeeItem $feeItem,
        SchoolClass $class,
        AcademicSession $session,
        Term $term,
        float $amount
    ): FinanceFeeAssignment {
        return app(SchoolFinanceService::class)->createFeeAssignment($school, $user, [
            'fee_item_id' => $feeItem->id,
            'academic_session_id' => $session->id,
            'term_id' => $term->id,
            'school_class_id' => $class->id,
            'amount' => $amount,
            'is_active' => true,
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
