<?php

namespace App\Services\Finance;

use App\Models\AuditLog;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFeeInvoice;
use App\Models\StudentFeePayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class SchoolFinanceReportService
{
    public function report(School $school, array $filters = []): array
    {
        $invoiceQuery = $this->invoiceQuery($school, $filters);
        $paymentQuery = $this->paymentQuery($school, $filters);

        return [
            'filters' => $filters,
            'summary' => $this->summary($invoiceQuery, $paymentQuery),
            'invoice_status_counts' => $this->invoiceStatusCounts($invoiceQuery),
            'payment_methods' => $this->paymentMethodTotals($paymentQuery),
            'payments_by_date' => $this->paymentTotalsByDate($paymentQuery),
            'outstanding_by_class' => $this->outstandingByClass($school, $invoiceQuery),
            'student_balances' => $this->studentBalances($school, $invoiceQuery),
            'class_session_term_summaries' => $this->classSessionTermSummaries($school, $invoiceQuery),
            'recent_payments' => $this->recentPayments($paymentQuery),
            'overdue' => $this->overdueInvoices($invoiceQuery),
        ];
    }

    public function auditLogQuery(School $school, array $filters = []): Builder
    {
        return AuditLog::query()
            ->where('school_id', $school->id)
            ->whereIn('action', $this->financeAuditActions())
            ->when(filled($filters['action'] ?? null), fn (Builder $query) => $query->where('action', $filters['action']))
            ->when(filled($filters['date_from'] ?? null), fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['date_to']))
            ->when(filled($filters['student_id'] ?? null), function (Builder $query) use ($filters): void {
                $studentId = (int) $filters['student_id'];

                $query->where(function (Builder $query) use ($studentId): void {
                    $query->where('new_values->student_id', $studentId)
                        ->orWhere('metadata->student_id', $studentId)
                        ->orWhere('payload->student_id', $studentId);
                });
            })
            ->when(filled($filters['school_class_id'] ?? null), function (Builder $query) use ($filters): void {
                $classId = (int) $filters['school_class_id'];

                $query->where(function (Builder $query) use ($classId): void {
                    $query->where('new_values->school_class_id', $classId)
                        ->orWhere('metadata->school_class_id', $classId)
                        ->orWhere('payload->school_class_id', $classId);
                });
            });
    }

    public function financeAuditActions(): array
    {
        return [
            'finance_fee_item_created',
            'finance_fee_item_updated',
            'finance_fee_assignment_created',
            'finance_fee_assignment_updated',
            'finance_invoice_generated',
            'finance_invoice_cancelled',
            'finance_invoice_recalculated',
            'finance_payment_recorded',
        ];
    }

    public function safeAuditMetadata(AuditLog $log): array
    {
        $values = array_replace(
            $log->new_values ?: [],
            $log->metadata ?: [],
            $log->payload ?: [],
        );

        $safe = [];

        foreach ($this->safeAuditKeys() as $key) {
            if (Arr::has($values, $key)) {
                $safe[$key] = Arr::get($values, $key);
            }
        }

        return $safe;
    }

    private function summary(Builder $invoiceQuery, Builder $paymentQuery): array
    {
        $invoiceRow = (clone $invoiceQuery)
            ->selectRaw('COUNT(*) as invoices')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_invoiced')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as total_discount')
            ->first();

        $outstanding = (clone $invoiceQuery)
            ->where('status', '!=', StudentFeeInvoice::STATUS_CANCELLED)
            ->sum('balance_amount');

        $paymentRow = (clone $paymentQuery)
            ->selectRaw('COUNT(*) as payments')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_paid')
            ->first();

        $totalInvoiced = (float) ($invoiceRow->total_invoiced ?? 0);
        $totalDiscount = (float) ($invoiceRow->total_discount ?? 0);

        return [
            'invoices' => (int) ($invoiceRow->invoices ?? 0),
            'payments' => (int) ($paymentRow->payments ?? 0),
            'total_invoiced' => $totalInvoiced,
            'total_discount' => $totalDiscount,
            'total_net' => max($totalInvoiced - $totalDiscount, 0),
            'total_paid' => (float) ($paymentRow->total_paid ?? 0),
            'total_outstanding' => (float) $outstanding,
        ];
    }

    private function invoiceStatusCounts(Builder $invoiceQuery): array
    {
        $counts = (clone $invoiceQuery)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return collect(StudentFeeInvoice::STATUSES)
            ->mapWithKeys(fn (string $status): array => [$status => (int) ($counts[$status] ?? 0)])
            ->all();
    }

    private function paymentMethodTotals(Builder $paymentQuery): array
    {
        $rows = (clone $paymentQuery)
            ->select('method')
            ->selectRaw('COUNT(*) as payments')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->groupBy('method')
            ->get()
            ->keyBy('method');

        return collect(StudentFeePayment::METHODS)
            ->map(function (string $method) use ($rows): array {
                $row = $rows->get($method);

                return [
                    'method' => $method,
                    'payments' => (int) ($row->payments ?? 0),
                    'total' => (float) ($row->total ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    private function paymentTotalsByDate(Builder $paymentQuery): array
    {
        return (clone $paymentQuery)
            ->select('payment_date')
            ->selectRaw('COUNT(*) as payments')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->groupBy('payment_date')
            ->orderBy('payment_date')
            ->get()
            ->map(fn (StudentFeePayment $payment): array => [
                'date' => $payment->payment_date?->toDateString(),
                'payments' => (int) $payment->payments,
                'total' => (float) $payment->total,
            ])
            ->all();
    }

    private function outstandingByClass(School $school, Builder $invoiceQuery): array
    {
        $rows = (clone $invoiceQuery)
            ->where('status', '!=', StudentFeeInvoice::STATUS_CANCELLED)
            ->where('balance_amount', '>', 0)
            ->select('school_class_id')
            ->selectRaw('COUNT(*) as invoices')
            ->selectRaw('COALESCE(SUM(balance_amount), 0) as balance')
            ->groupBy('school_class_id')
            ->orderByDesc('balance')
            ->limit(12)
            ->get();

        $classes = SchoolClass::query()
            ->where('school_id', $school->id)
            ->whereIn('id', $rows->pluck('school_class_id')->filter()->all())
            ->get()
            ->keyBy('id');

        return $rows
            ->map(fn ($row): array => [
                'school_class_id' => $row->school_class_id ? (int) $row->school_class_id : null,
                'class' => $this->classLabel($classes->get($row->school_class_id)),
                'invoices' => (int) $row->invoices,
                'balance' => (float) $row->balance,
            ])
            ->all();
    }

    private function studentBalances(School $school, Builder $invoiceQuery): array
    {
        $rows = (clone $invoiceQuery)
            ->where('status', '!=', StudentFeeInvoice::STATUS_CANCELLED)
            ->where('balance_amount', '>', 0)
            ->select('student_id')
            ->selectRaw('COUNT(*) as invoices')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_invoiced')
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as total_paid')
            ->selectRaw('COALESCE(SUM(balance_amount), 0) as balance')
            ->groupBy('student_id')
            ->orderByDesc('balance')
            ->limit(20)
            ->get();

        $students = Student::query()
            ->where('school_id', $school->id)
            ->whereIn('id', $rows->pluck('student_id')->filter()->all())
            ->with('schoolClass')
            ->get()
            ->keyBy('id');

        return $rows
            ->map(function ($row) use ($students): array {
                $student = $students->get($row->student_id);

                return [
                    'student_id' => (int) $row->student_id,
                    'student' => $student?->fullName() ?? 'Student #'.$row->student_id,
                    'admission_number' => $student?->admission_number,
                    'class' => $this->classLabel($student?->schoolClass),
                    'invoices' => (int) $row->invoices,
                    'total_invoiced' => (float) $row->total_invoiced,
                    'total_paid' => (float) $row->total_paid,
                    'balance' => (float) $row->balance,
                ];
            })
            ->all();
    }

    private function classSessionTermSummaries(School $school, Builder $invoiceQuery): array
    {
        $rows = (clone $invoiceQuery)
            ->select('school_class_id', 'academic_session_id', 'term_id')
            ->selectRaw('COUNT(*) as invoices')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_invoiced')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as total_discount')
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as total_paid')
            ->selectRaw('COALESCE(SUM(balance_amount), 0) as balance')
            ->groupBy('school_class_id', 'academic_session_id', 'term_id')
            ->orderByDesc('balance')
            ->limit(20)
            ->get();

        $classes = $school->schoolClasses()
            ->whereIn('id', $rows->pluck('school_class_id')->filter()->all())
            ->get()
            ->keyBy('id');
        $sessions = $school->academicSessions()
            ->whereIn('id', $rows->pluck('academic_session_id')->filter()->all())
            ->get()
            ->keyBy('id');
        $terms = $school->terms()
            ->whereIn('id', $rows->pluck('term_id')->filter()->all())
            ->get()
            ->keyBy('id');

        return $rows
            ->map(fn ($row): array => [
                'class' => $this->classLabel($classes->get($row->school_class_id)),
                'session' => $sessions->get($row->academic_session_id)?->name ?? 'Any session',
                'term' => $terms->get($row->term_id)?->name ?? 'Any term',
                'invoices' => (int) $row->invoices,
                'total_invoiced' => (float) $row->total_invoiced,
                'total_discount' => (float) $row->total_discount,
                'total_paid' => (float) $row->total_paid,
                'balance' => (float) $row->balance,
            ])
            ->all();
    }

    private function recentPayments(Builder $paymentQuery)
    {
        return (clone $paymentQuery)
            ->with(['student', 'invoice'])
            ->latest('payment_date')
            ->latest('id')
            ->limit(10)
            ->get();
    }

    private function overdueInvoices(Builder $invoiceQuery): array
    {
        $query = (clone $invoiceQuery)
            ->where('status', '!=', StudentFeeInvoice::STATUS_CANCELLED)
            ->where('balance_amount', '>', 0)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today());

        $row = (clone $query)
            ->selectRaw('COUNT(*) as invoices')
            ->selectRaw('COALESCE(SUM(balance_amount), 0) as balance')
            ->first();

        return [
            'invoices' => (int) ($row->invoices ?? 0),
            'balance' => (float) ($row->balance ?? 0),
            'items' => $query
                ->with(['student', 'schoolClass'])
                ->orderBy('due_date')
                ->limit(10)
                ->get(),
        ];
    }

    private function invoiceQuery(School $school, array $filters): Builder
    {
        $query = StudentFeeInvoice::query()
            ->where('school_id', $school->id);

        $this->applyInvoiceFilters($query, $filters, true);

        return $query;
    }

    private function paymentQuery(School $school, array $filters): Builder
    {
        $query = StudentFeePayment::query()
            ->where('school_id', $school->id)
            ->when(filled($filters['student_id'] ?? null), fn (Builder $query) => $query->where('student_id', (int) $filters['student_id']))
            ->when(filled($filters['payment_method'] ?? null), fn (Builder $query) => $query->where('method', $filters['payment_method']))
            ->when(filled($filters['date_from'] ?? null), fn (Builder $query) => $query->whereDate('payment_date', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn (Builder $query) => $query->whereDate('payment_date', '<=', $filters['date_to']));

        if ($this->hasInvoiceContextFilters($filters)) {
            $query->whereHas('invoice', function (Builder $query) use ($filters): void {
                $this->applyInvoiceFilters($query, $filters, false);
            });
        }

        return $query;
    }

    private function applyInvoiceFilters(Builder $query, array $filters, bool $includeDateRange): void
    {
        $query
            ->when(filled($filters['academic_session_id'] ?? null), fn (Builder $query) => $query->where('academic_session_id', (int) $filters['academic_session_id']))
            ->when(filled($filters['term_id'] ?? null), fn (Builder $query) => $query->where('term_id', (int) $filters['term_id']))
            ->when(filled($filters['school_class_id'] ?? null), fn (Builder $query) => $query->where('school_class_id', (int) $filters['school_class_id']))
            ->when(filled($filters['student_id'] ?? null), fn (Builder $query) => $query->where('student_id', (int) $filters['student_id']))
            ->when(filled($filters['invoice_status'] ?? null), fn (Builder $query) => $query->where('status', $filters['invoice_status']));

        if ($includeDateRange) {
            $query
                ->when(filled($filters['date_from'] ?? null), fn (Builder $query) => $query->whereDate('issued_at', '>=', $filters['date_from']))
                ->when(filled($filters['date_to'] ?? null), fn (Builder $query) => $query->whereDate('issued_at', '<=', $filters['date_to']));
        }
    }

    private function hasInvoiceContextFilters(array $filters): bool
    {
        foreach (['academic_session_id', 'term_id', 'school_class_id', 'student_id', 'invoice_status'] as $key) {
            if (filled($filters[$key] ?? null)) {
                return true;
            }
        }

        return false;
    }

    private function classLabel(?SchoolClass $class): string
    {
        if (! $class) {
            return 'No class';
        }

        return trim($class->name.' '.$class->section);
    }

    private function safeAuditKeys(): array
    {
        return [
            'fee_item_id',
            'assignment_id',
            'invoice_id',
            'invoice_number',
            'payment_id',
            'student_id',
            'school_class_id',
            'academic_session_id',
            'term_id',
            'amount',
            'total_amount',
            'balance_amount',
            'status',
            'old_status',
            'new_status',
            'payment_date',
            'method',
            'has_reference',
            'items',
            'target',
            'created_by',
            'generated_by',
            'received_by',
        ];
    }
}
