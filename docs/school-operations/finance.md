# School Finance Operations

Use **Fees & Finance** in the school workspace to set up fees and record school payments.

## Recommended Workflow

1. Open **Fee Items** and create reusable fee heads.
2. Open **Assignments** and attach fees to the relevant class, student, session, and term.
3. Open **Invoices** and generate a bill for one student or an active class.
4. Open an invoice and record each manual payment.
5. Use **Reports** to review billed, paid, outstanding, status, method, class, session, term, and student balance summaries.
6. Use **Audit Review** to inspect safe finance audit entries.
7. Use the invoice or student finance history to review individual billed, paid, and outstanding amounts.

Class invoice generation is idempotent for the same student, academic context, and assignment set. Repeating the same generation preserves the matching existing invoice.

## Access

School Admins and Accountants can use the module, including finance reports and finance audit review. Teachers, Result Officers, Students, and Parents cannot manage finance or view finance reports. All records are restricted to the active school workspace.

## Reports

Finance reports are available at **Fees & Finance > Reports**. They summarize:

- total invoiced;
- total paid;
- total outstanding;
- invoice discount/waiver totals where invoice-item discounts exist;
- invoice status counts for `draft`, `issued`, `part_paid`, `paid`, and `cancelled`;
- payment totals by method;
- payment totals by date;
- outstanding balances by class;
- student balances;
- class, session, and term summaries;
- recent payments;
- overdue invoices when due dates exist.

Supported filters are `date_from`, `date_to`, `academic_session_id`, `term_id`, `school_class_id`, `invoice_status`, `payment_method`, and `student_id`. Date filters apply to invoice issue dates for billed/outstanding summaries and payment dates for payment summaries.

## Finance Audit Review

Finance audit review is available at **Fees & Finance > Audit Review**. It reuses the existing audit log system and shows finance actions only, including fee item creation, assignment creation, invoice generation, payment recording, and future safe finance status actions when present.

The audit review displays safe finance identifiers, amounts, statuses, dates, methods, and reference-presence flags. It does not display raw payloads, payment notes, full references, secrets, stack traces, or unnecessary student biodata.

## Payment Safety

- Amounts must be greater than zero.
- Overpayments are rejected.
- Payment references are optional and limited in length.
- Payment notes are stored on the payment record but are not copied into safe audit metadata.
- No public invoice link is created.
- No payment gateway charge is initiated.

## Current Boundary

This module provides online fee setup, student billing, manual payments, balances, receipt history, finance reports, and finance audit review. Import/export is Stage 12 work. Payment gateway automation, offline fee capture, parent/student finance portal access, and full double-entry accounting remain deferred.

See `docs/standalone/fees-accounting-foundation.md` for architecture, permissions, audit behavior, and deferred scope.
See `docs/school-operations/finance-reports-and-audit.md` for the Stage 11 report and audit details.
