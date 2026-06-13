# Finance Support Runbook

## Purpose

Use this runbook when supporting fee items, assignments, invoices, manual payments, balances, finance reports, finance audit review, and finance CSV export.

## Access

School Admins and Accountants can use finance when the role has finance access. Teachers, Result Officers, Students, Parents, and public users do not manage finance or view finance reports by default.

## Normal Workflow

1. Create fee items.
2. Assign fees to class, student, session, term, or school-wide context.
3. Generate student or class invoices from active assignments.
4. Record manual payments against invoices.
5. Review balances, reports, and finance audit entries.
6. Export selected finance summaries only through the Import / Export workspace where authorized.

## Common Issues

- Invoice was expected but no matching active assignment exists.
- Re-running generation preserves an existing invoice and does not create a duplicate.
- Payment amount is zero, negative, or higher than the balance.
- Student/class/session/term belongs to another school or does not match.
- Report totals appear different because invoice issue dates and payment dates use different filters.
- User expects online payment gateway automation or full ERP accounting.

## First Checks

- Confirm the active school, student, class, session, term, fee assignment, invoice, and payment date.
- Review invoice items, discounts, payment list, and balance.
- Review finance reports with the exact filters used by the school.
- Review finance audit entries for fee item creation, assignment creation, invoice generation, and payment recording.
- Confirm no raw payment references or private notes are being exposed in audit views.

## Safe Commands And UI Checks

```bash
php artisan test --filter=Reports
php artisan route:list
php artisan standalone:status
```

Use Fees & Finance, Finance Reports, Finance Audit Review, and Import / Export as the primary support views.

## What Support Should Not Do

- Do not manually edit balances without an approved workflow.
- Do not create payment records from screenshots alone.
- Do not promise payment gateway automation for school fees.
- Do not describe the module as a full ERP, ledger, payroll, procurement, tax, or bank-reconciliation system.
- Do not expose payment notes, full references, gateway payloads, or secrets in tickets.

## Escalation Points

Escalate when totals conflict with saved invoices/payments, a finance record appears cross-school, overpayment rules behave incorrectly, audit history is missing for a finance action, or production finance data may need correction.

## Data And Privacy Warnings

Finance records can contain sensitive balances, references, and notes. Collect only record IDs, sanitized screenshots, dates, amounts, and filters needed for support.

## Backup And Security Reminders

Verify a backup before bulk imports, finance corrections, update work, or any maintenance touching invoice/payment data.

## Related Docs

- [Fees And Accounting Foundation](../standalone/fees-accounting-foundation.md)
- [School Finance Operations](../school-operations/finance.md)
- [Finance Reports And Audit](../school-operations/finance-reports-and-audit.md)
- [Import And Export Tools](../school-operations/import-export.md)
