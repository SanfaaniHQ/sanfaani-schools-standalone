# Finance Reports and Audit

Stage 11 adds school-scoped finance reports and finance audit review to the existing fees/accounting foundation. It does not rebuild invoices, payments, students, classes, sessions, terms, dashboards, permissions, or audit logs.

## Access

- School Admins can view finance reports and audit review.
- Accountants can view finance reports and audit review when finance access is enabled for their role.
- Teachers, Result Officers, Parents, and Students cannot view finance reports or finance audit review.
- Public users cannot access finance reports.
- Cross-school invoices, payments, students, classes, sessions, terms, and audit logs are not included.

## Report Filters

The reports page supports:

- `date_from`;
- `date_to`;
- `academic_session_id`;
- `term_id`;
- `school_class_id`;
- `invoice_status`;
- `payment_method`;
- `student_id`.

Date filters apply to invoice `issued_at` for billed, discount, status, outstanding, class, student, and overdue summaries. Date filters apply to `student_fee_payments.payment_date` for payment summaries and recent payments.

## Summary Calculations

- Total invoiced: sum of `student_fee_invoices.total_amount`.
- Total paid: sum of matching `student_fee_payments.amount`.
- Outstanding balance: sum of `student_fee_invoices.balance_amount`.
- Discount/waiver: sum of `student_fee_invoices.discount_amount`.
- Invoice status breakdown: count of invoices for `draft`, `issued`, `part_paid`, `paid`, and `cancelled`.
- Payment method summary: payment count and amount by `manual`, `cash`, `bank_transfer`, `card`, and `other`.
- Outstanding by class: non-cancelled invoices with positive balance grouped by class.
- Student balances: non-cancelled invoices with positive balance grouped by student.
- Class/session/term summaries: invoice totals grouped by class, academic session, and term.
- Recent payments: safe amount, date, method, student, and invoice number only.
- Overdue invoices: non-cancelled invoices with due dates before today and positive balance.

## Finance Audit Review

Finance audit review reuses `audit_logs` and filters to finance actions such as:

- `finance_fee_item_created`;
- `finance_fee_assignment_created`;
- `finance_invoice_generated`;
- `finance_payment_recorded`;
- safe future finance update/cancellation/recalculation actions if they are logged.

The view displays safe metadata only: finance record IDs, student ID, class ID, amount, payment date, method, status, invoice number, and whether a reference exists. It does not display raw payloads, full payment references, payment notes, stack traces, secrets, or unnecessary student biodata.

## Deferred Boundaries

- Import/export remains deferred to Stage 12.
- Online payment gateway automation is not implemented for school fees.
- Offline fee capture is not implemented.
- Parent/student finance portal visibility is not implemented.
- Full accounting, ledgers, journals, reconciliation, payroll, procurement, and tax accounting are not implemented.
