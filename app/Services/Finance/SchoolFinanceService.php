<?php

namespace App\Services\Finance;

use App\Models\FinanceFeeAssignment;
use App\Models\FinanceFeeItem;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFeeInvoice;
use App\Models\StudentFeePayment;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Communications\SchoolNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SchoolFinanceService
{
    public function __construct(
        private AuditLogService $auditLog,
        private SchoolNotificationService $notifications,
    ) {}

    public function invoiceStatuses(): array
    {
        return StudentFeeInvoice::STATUSES;
    }

    public function paymentMethods(): array
    {
        return StudentFeePayment::METHODS;
    }

    public function summary(School $school): array
    {
        $invoiceRow = StudentFeeInvoice::query()
            ->where('school_id', $school->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'issued' THEN 1 ELSE 0 END) as issued")
            ->selectRaw("SUM(CASE WHEN status = 'part_paid' THEN 1 ELSE 0 END) as part_paid")
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            ->selectRaw('SUM(total_amount) as total_amount')
            ->selectRaw('SUM(discount_amount) as discount_amount')
            ->selectRaw('SUM(paid_amount) as paid_amount')
            ->selectRaw('SUM(balance_amount) as balance_amount')
            ->first();

        return [
            'fee_items' => FinanceFeeItem::query()->where('school_id', $school->id)->count(),
            'active_fee_items' => FinanceFeeItem::query()->where('school_id', $school->id)->where('is_active', true)->count(),
            'assignments' => FinanceFeeAssignment::query()->where('school_id', $school->id)->count(),
            'active_assignments' => FinanceFeeAssignment::query()->where('school_id', $school->id)->where('is_active', true)->count(),
            'invoices' => (int) ($invoiceRow->total ?? 0),
            'issued_invoices' => (int) ($invoiceRow->issued ?? 0),
            'part_paid_invoices' => (int) ($invoiceRow->part_paid ?? 0),
            'paid_invoices' => (int) ($invoiceRow->paid ?? 0),
            'cancelled_invoices' => (int) ($invoiceRow->cancelled ?? 0),
            'outstanding_invoices' => StudentFeeInvoice::query()
                ->where('school_id', $school->id)
                ->where('balance_amount', '>', 0)
                ->where('status', '!=', StudentFeeInvoice::STATUS_CANCELLED)
                ->count(),
            'total_billed' => (float) ($invoiceRow->total_amount ?? 0),
            'total_discount' => (float) ($invoiceRow->discount_amount ?? 0),
            'total_paid' => (float) ($invoiceRow->paid_amount ?? 0),
            'total_balance' => (float) ($invoiceRow->balance_amount ?? 0),
            'payments' => StudentFeePayment::query()->where('school_id', $school->id)->count(),
            'latest_payment_at' => StudentFeePayment::query()->where('school_id', $school->id)->latest('payment_date')->value('payment_date'),
            'latest_invoice_at' => StudentFeeInvoice::query()->where('school_id', $school->id)->latest('issued_at')->value('issued_at'),
        ];
    }

    public function createFeeItem(School $school, User $actor, array $data): FinanceFeeItem
    {
        $code = filled($data['code'] ?? null) ? Str::upper(trim((string) $data['code'])) : null;

        if ($code && FinanceFeeItem::query()
            ->where('school_id', $school->id)
            ->where('code', $code)
            ->exists()) {
            throw ValidationException::withMessages([
                'code' => 'The fee item code has already been used in this school.',
            ]);
        }

        $item = FinanceFeeItem::create([
            'school_id' => $school->id,
            'name' => trim((string) $data['name']),
            'code' => $code,
            'description' => $data['description'] ?? null,
            'default_amount' => $this->positiveAmount($data['default_amount'] ?? 0, 'default_amount'),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_by' => $actor->id,
        ]);

        $this->auditLog->log('finance_fee_item_created', $item, $school, newValues: [
            'name' => $item->name,
            'code' => $item->code,
            'default_amount' => $item->default_amount,
            'is_active' => $item->is_active,
        ], metadata: [
            'fee_item_id' => $item->id,
            'created_by' => $actor->id,
        ]);

        return $item;
    }

    public function createFeeAssignment(School $school, User $actor, array $data): FinanceFeeAssignment
    {
        $feeItem = $this->feeItemForSchool($school, (int) $data['fee_item_id']);
        $student = filled($data['student_id'] ?? null) ? $this->studentForSchool($school, (int) $data['student_id']) : null;
        $class = filled($data['school_class_id'] ?? null) ? $this->classForSchool($school, (int) $data['school_class_id']) : null;
        $sessionId = filled($data['academic_session_id'] ?? null) ? (int) $data['academic_session_id'] : null;
        $termId = filled($data['term_id'] ?? null) ? (int) $data['term_id'] : null;

        $this->assertAcademicSession($school, $sessionId);
        $this->assertTerm($school, $termId, $sessionId);

        if ($student && $class && (int) $student->school_class_id !== (int) $class->id) {
            throw ValidationException::withMessages([
                'student_id' => 'The selected student is not currently in the selected class.',
            ]);
        }

        $assignment = FinanceFeeAssignment::create([
            'school_id' => $school->id,
            'fee_item_id' => $feeItem->id,
            'academic_session_id' => $sessionId,
            'term_id' => $termId,
            'school_class_id' => $class?->id,
            'student_id' => $student?->id,
            'amount' => $this->positiveAmount($data['amount'] ?? $feeItem->default_amount, 'amount'),
            'due_date' => $data['due_date'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_by' => $actor->id,
            'metadata' => [
                'source' => 'school_finance_foundation',
            ],
        ]);

        $this->auditLog->log('finance_fee_assignment_created', $assignment, $school, newValues: [
            'fee_item_id' => $assignment->fee_item_id,
            'academic_session_id' => $assignment->academic_session_id,
            'term_id' => $assignment->term_id,
            'school_class_id' => $assignment->school_class_id,
            'student_id' => $assignment->student_id,
            'amount' => $assignment->amount,
            'due_date' => $assignment->due_date?->toDateString(),
            'is_active' => $assignment->is_active,
        ], metadata: [
            'assignment_id' => $assignment->id,
            'created_by' => $actor->id,
            'target' => $this->assignmentTarget($assignment),
        ]);

        return $assignment;
    }

    public function generateStudentInvoice(School $school, User $actor, Student $student, array $context = []): array
    {
        $this->assertStudentModel($school, $student);

        $classId = filled($context['school_class_id'] ?? null)
            ? (int) $context['school_class_id']
            : ($student->school_class_id ? (int) $student->school_class_id : null);
        $class = $classId ? $this->classForSchool($school, $classId) : null;
        $sessionId = filled($context['academic_session_id'] ?? null) ? (int) $context['academic_session_id'] : null;
        $termId = filled($context['term_id'] ?? null) ? (int) $context['term_id'] : null;

        $this->assertAcademicSession($school, $sessionId);
        $this->assertTerm($school, $termId, $sessionId);

        $assignments = $this->assignmentsForStudent($school, $student, $class?->id, $sessionId, $termId);

        if ($assignments->isEmpty()) {
            throw ValidationException::withMessages([
                'fee_assignment' => 'No active fee assignments match this student and academic context.',
            ]);
        }

        $signature = $this->assignmentSignature($assignments);
        $existing = $this->existingInvoiceForSignature($school, $student, $class?->id, $sessionId, $termId, $signature);

        if ($existing) {
            return [
                'invoice' => $existing->load(['items.feeItem', 'payments', 'student', 'schoolClass', 'academicSession', 'term']),
                'created' => false,
            ];
        }

        return DB::transaction(function () use ($school, $actor, $student, $class, $sessionId, $termId, $assignments, $context, $signature): array {
            $invoice = StudentFeeInvoice::create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'school_class_id' => $class?->id,
                'academic_session_id' => $sessionId,
                'term_id' => $termId,
                'invoice_number' => $this->nextInvoiceNumber($school),
                'status' => StudentFeeInvoice::STATUS_ISSUED,
                'due_date' => $context['due_date'] ?? $assignments->pluck('due_date')->filter()->sort()->first(),
                'issued_at' => now(),
                'created_by' => $actor->id,
                'metadata' => [
                    'assignment_signature' => $signature,
                    'assignment_ids' => $assignments->pluck('id')->values()->all(),
                    'source' => 'school_finance_foundation',
                ],
            ]);

            foreach ($assignments as $assignment) {
                $invoice->items()->create([
                    'school_id' => $school->id,
                    'fee_item_id' => $assignment->fee_item_id,
                    'description' => $assignment->feeItem?->name ?? 'School fee',
                    'amount' => $assignment->amount,
                    'discount_amount' => 0,
                    'metadata' => [
                        'fee_assignment_id' => $assignment->id,
                    ],
                ]);
            }

            $this->recalculateInvoice($invoice);

            $this->auditLog->log('finance_invoice_generated', $invoice, $school, newValues: [
                'student_id' => $invoice->student_id,
                'school_class_id' => $invoice->school_class_id,
                'academic_session_id' => $invoice->academic_session_id,
                'term_id' => $invoice->term_id,
                'total_amount' => $invoice->total_amount,
                'balance_amount' => $invoice->balance_amount,
                'status' => $invoice->status,
            ], metadata: [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'items' => $assignments->count(),
                'generated_by' => $actor->id,
            ]);
            $this->notifications->logFinanceInvoiceGenerated($school, $actor, $invoice->refresh());

            return [
                'invoice' => $invoice->refresh()->load(['items.feeItem', 'payments', 'student', 'schoolClass', 'academicSession', 'term']),
                'created' => true,
            ];
        });
    }

    public function generateClassInvoices(School $school, User $actor, SchoolClass $class, array $context = []): array
    {
        $this->assertClassModel($school, $class);

        $students = Student::query()
            ->where('school_id', $school->id)
            ->where('school_class_id', $class->id)
            ->where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $created = 0;
        $existing = 0;
        $invoices = collect();

        foreach ($students as $student) {
            $result = $this->generateStudentInvoice($school, $actor, $student, [
                ...$context,
                'school_class_id' => $class->id,
            ]);

            $result['created'] ? $created++ : $existing++;
            $invoices->push($result['invoice']);
        }

        return [
            'created' => $created,
            'existing' => $existing,
            'total' => $invoices->count(),
            'invoices' => $invoices,
        ];
    }

    public function recordPayment(School $school, User $actor, StudentFeeInvoice $invoice, array $data): StudentFeePayment
    {
        $this->assertInvoiceModel($school, $invoice);
        $this->recalculateInvoice($invoice);

        if ($invoice->status === StudentFeeInvoice::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'invoice' => 'Payments cannot be recorded against a cancelled invoice.',
            ]);
        }

        $amount = $this->positiveAmount($data['amount'] ?? 0, 'amount');
        $method = (string) ($data['method'] ?? 'manual');

        if ($amount > ((float) $invoice->balance_amount + 0.00001)) {
            throw ValidationException::withMessages([
                'amount' => 'The payment amount cannot exceed the outstanding balance.',
            ]);
        }

        if (! in_array($method, $this->paymentMethods(), true)) {
            throw ValidationException::withMessages([
                'method' => 'The selected payment method is invalid.',
            ]);
        }

        return DB::transaction(function () use ($school, $actor, $invoice, $data, $amount, $method): StudentFeePayment {
            $payment = StudentFeePayment::create([
                'school_id' => $school->id,
                'student_fee_invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'amount' => $amount,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'method' => $method,
                'reference' => filled($data['reference'] ?? null) ? trim((string) $data['reference']) : null,
                'received_by' => $actor->id,
                'note' => $data['note'] ?? null,
                'metadata' => [
                    'source' => 'manual_school_finance_entry',
                ],
            ]);

            $this->recalculateInvoice($invoice);

            $this->auditLog->log('finance_payment_recorded', $payment, $school, newValues: [
                'student_fee_invoice_id' => $payment->student_fee_invoice_id,
                'student_id' => $payment->student_id,
                'amount' => $payment->amount,
                'payment_date' => $payment->payment_date?->toDateString(),
                'method' => $payment->method,
            ], metadata: [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'received_by' => $actor->id,
                'has_reference' => filled($payment->reference),
            ]);
            $this->notifications->logFinancePaymentRecorded($school, $actor, $payment->refresh(), $invoice->refresh());

            return $payment->refresh();
        });
    }

    public function recalculateInvoice(StudentFeeInvoice $invoice): StudentFeeInvoice
    {
        $invoice->load(['items', 'payments']);

        $total = $invoice->items->sum(fn ($item) => (float) $item->amount);
        $discount = $invoice->items->sum(fn ($item) => (float) $item->discount_amount);
        $paid = $invoice->payments->sum(fn ($payment) => (float) $payment->amount);
        $net = max($total - $discount, 0);
        $balance = max($net - $paid, 0);

        $status = $invoice->status;

        if ($status !== StudentFeeInvoice::STATUS_CANCELLED) {
            $status = match (true) {
                $net > 0 && $paid >= $net => StudentFeeInvoice::STATUS_PAID,
                $paid > 0 => StudentFeeInvoice::STATUS_PART_PAID,
                default => StudentFeeInvoice::STATUS_ISSUED,
            };
        }

        $invoice->forceFill([
            'total_amount' => round($total, 2),
            'discount_amount' => round($discount, 2),
            'paid_amount' => round($paid, 2),
            'balance_amount' => round($balance, 2),
            'status' => $status,
        ])->save();

        return $invoice->refresh();
    }

    public function studentHistory(School $school, Student $student): array
    {
        $this->assertStudentModel($school, $student);

        return [
            'invoices' => StudentFeeInvoice::query()
                ->where('school_id', $school->id)
                ->where('student_id', $student->id)
                ->with(['items.feeItem', 'payments.receiver', 'schoolClass', 'academicSession', 'term'])
                ->latest('id')
                ->paginate(10),
            'summary' => [
                'total_billed' => (float) StudentFeeInvoice::query()->where('school_id', $school->id)->where('student_id', $student->id)->sum('total_amount'),
                'total_paid' => (float) StudentFeeInvoice::query()->where('school_id', $school->id)->where('student_id', $student->id)->sum('paid_amount'),
                'total_balance' => (float) StudentFeeInvoice::query()->where('school_id', $school->id)->where('student_id', $student->id)->sum('balance_amount'),
            ],
        ];
    }

    public function invoicesQuery(School $school): Builder
    {
        return StudentFeeInvoice::query()
            ->where('school_id', $school->id)
            ->with(['student', 'schoolClass', 'academicSession', 'term'])
            ->latest('id');
    }

    private function assignmentsForStudent(School $school, Student $student, ?int $classId, ?int $sessionId, ?int $termId): Collection
    {
        return FinanceFeeAssignment::query()
            ->where('school_id', $school->id)
            ->where('is_active', true)
            ->with('feeItem')
            ->where(function (Builder $query) use ($student, $classId): void {
                $query->where('student_id', $student->id);

                if ($classId) {
                    $query->orWhere(function (Builder $query) use ($classId): void {
                        $query->whereNull('student_id')
                            ->where('school_class_id', $classId);
                    });
                }

                $query->orWhere(function (Builder $query): void {
                    $query->whereNull('student_id')
                        ->whereNull('school_class_id');
                });
            })
            ->when($sessionId, fn (Builder $query) => $query->where(function (Builder $query) use ($sessionId): void {
                $query->whereNull('academic_session_id')
                    ->orWhere('academic_session_id', $sessionId);
            }))
            ->when($termId, fn (Builder $query) => $query->where(function (Builder $query) use ($termId): void {
                $query->whereNull('term_id')
                    ->orWhere('term_id', $termId);
            }))
            ->orderBy('id')
            ->get();
    }

    private function existingInvoiceForSignature(School $school, Student $student, ?int $classId, ?int $sessionId, ?int $termId, string $signature): ?StudentFeeInvoice
    {
        return StudentFeeInvoice::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('status', '!=', StudentFeeInvoice::STATUS_CANCELLED)
            ->where('school_class_id', $classId)
            ->where('academic_session_id', $sessionId)
            ->where('term_id', $termId)
            ->get()
            ->first(fn (StudentFeeInvoice $invoice): bool => data_get($invoice->metadata, 'assignment_signature') === $signature);
    }

    private function assignmentSignature(Collection $assignments): string
    {
        return sha1($assignments->pluck('id')->sort()->values()->implode('|'));
    }

    private function nextInvoiceNumber(School $school): string
    {
        do {
            $number = 'SFI-'.$school->id.'-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (StudentFeeInvoice::query()
            ->where('school_id', $school->id)
            ->where('invoice_number', $number)
            ->exists());

        return $number;
    }

    private function positiveAmount(mixed $value, string $field): float
    {
        $amount = round((float) $value, 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                $field => 'The amount must be greater than zero.',
            ]);
        }

        return $amount;
    }

    private function feeItemForSchool(School $school, int $id): FinanceFeeItem
    {
        return FinanceFeeItem::query()
            ->where('school_id', $school->id)
            ->findOrFail($id);
    }

    private function classForSchool(School $school, int $id): SchoolClass
    {
        return SchoolClass::query()
            ->where('school_id', $school->id)
            ->findOrFail($id);
    }

    private function studentForSchool(School $school, int $id): Student
    {
        return Student::query()
            ->where('school_id', $school->id)
            ->findOrFail($id);
    }

    private function assertStudentModel(School $school, Student $student): void
    {
        if ((int) $student->school_id !== (int) $school->id) {
            abort(403, 'You cannot access finance records for this student.');
        }
    }

    private function assertClassModel(School $school, SchoolClass $class): void
    {
        if ((int) $class->school_id !== (int) $school->id) {
            abort(403, 'You cannot access finance records for this class.');
        }
    }

    private function assertInvoiceModel(School $school, StudentFeeInvoice $invoice): void
    {
        if ((int) $invoice->school_id !== (int) $school->id) {
            abort(403, 'You cannot access finance records for this invoice.');
        }
    }

    private function assertAcademicSession(School $school, ?int $sessionId): void
    {
        if (! $sessionId) {
            return;
        }

        if (! $school->academicSessions()->whereKey($sessionId)->exists()) {
            throw ValidationException::withMessages([
                'academic_session_id' => 'The selected academic session does not belong to this school.',
            ]);
        }
    }

    private function assertTerm(School $school, ?int $termId, ?int $sessionId = null): void
    {
        if (! $termId) {
            return;
        }

        $query = $school->terms()->whereKey($termId);

        if ($sessionId) {
            $query->where('academic_session_id', $sessionId);
        }

        if (! $query->exists()) {
            throw ValidationException::withMessages([
                'term_id' => 'The selected term does not belong to this school or session.',
            ]);
        }
    }

    private function assignmentTarget(FinanceFeeAssignment $assignment): string
    {
        return match (true) {
            filled($assignment->student_id) => 'student',
            filled($assignment->school_class_id) => 'class',
            default => 'school',
        };
    }
}
