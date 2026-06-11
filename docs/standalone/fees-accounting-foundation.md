# Fees and Accounting Foundation

Stage 10 adds the first online school-fees foundation to Sanfaani Schools Standalone. It extends the existing Laravel school workspace, role checks, audit logs, dashboards, students, classes, sessions, and terms. It does not replace the existing admissions payments, scratch-card payments, result-access transactions, subscriptions, or payment gateway settings.

## Available Now

- School-scoped fee items such as Tuition, Transport, Books, Uniform, and Exam Fee.
- Fee assignments for a class, selected student, academic session, term, or school-wide context.
- Student invoice generation from active fee assignments.
- Class invoice generation with duplicate matching protection.
- Manual payment recording against an invoice.
- Automatic billed, discounted, paid, and outstanding balance calculation.
- Invoice statuses: `issued`, `part_paid`, `paid`, and `cancelled` where a future safe cancellation workflow uses that state.
- Student finance history for authorized school finance users.
- School dashboard and accountant workspace visibility.
- Audit events for fee items, assignments, invoices, and payments.

## Fee Items and Assignments

A fee item is a reusable fee head with a default amount. An assignment connects that fee item to an academic and student context. The assignment amount is the amount used when an invoice is generated.

Assignments may target:

- one class;
- one student;
- a session or term;
- a school-wide context when no class or student is selected.

The selected class, student, session, and term must belong to the current school. A student and class combination is rejected when the student is not currently in that class.

## Invoice Generation

Invoices are generated from active assignments matching the student and selected academic context. Class generation processes active students currently attached to that class.

The service records the assignment IDs used to generate each invoice. Running the same generation again preserves the matching invoice rather than creating another bill. Cancelled invoices are excluded from this duplicate match so a later approved replacement workflow can be added without deleting history.

## Payment Recording and Balances

Stage 10 records manual payments only. Supported method labels are:

- manual;
- cash;
- bank transfer;
- card;
- other.

The invoice is recalculated after every payment:

`balance = total billed - item discounts - payments`

A positive payment below the balance changes the invoice to `part_paid`. Paying the remaining balance changes it to `paid`. Zero, negative, and overpayment amounts are rejected.

References and notes use normal Laravel validation. Audit metadata records only safe identifiers, amounts, methods, and whether a reference was supplied. It does not copy the payment note or reference into audit metadata.

## Permissions and School Scope

- School Admin: view and manage school finance.
- Accountant: view and manage school finance when the accountant role is assigned.
- Super Admin: may access the school finance workspace through the existing allowed school/support context.
- Teacher: no finance management access.
- Result Officer: no finance management access.
- Student and Parent: no finance management or finance portal access in this stage.

Every fee item, assignment, invoice, item, and payment stores `school_id`. Controllers and the finance service validate the current school before reading or changing finance records. Cross-school invoices, payments, students, classes, sessions, and terms are blocked.

## Audit Events

The existing audit log system records:

- `finance_fee_item_created`;
- `finance_fee_assignment_created`;
- `finance_invoice_generated`;
- `finance_payment_recorded`.

The audit trail contains safe school-scoped IDs and financial totals. It does not create a second finance audit system.

## Deferred Boundaries

- Advanced finance reports, debt analytics, exports, and a full finance audit/reporting pack are planned for Stage 11.
- Import/export workflows are planned for Stage 12.
- Payment gateway automation is not implemented by this school-fees foundation.
- Existing gateway settings and result/admissions payment workflows remain separate and preserved.
- Offline fee capture is not implemented.
- Parent/student finance portal visibility is not implemented.
- Double-entry accounting, journals, ledgers, bank reconciliation, tax accounting, payroll, and procurement are not implemented.

The hosted or locally installed Laravel portal/database remains the source of truth. Stage 10 is an online school-operations foundation, not a full accounting platform.
