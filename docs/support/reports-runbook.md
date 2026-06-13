# Reports Runbook

## Purpose

Use this runbook when supporting the Reports Center, linked module reports, filters, exports, and report-boundary questions.

## Access

School Admins can access the Reports Center. Super Admins can access it only inside a school context. Accountants keep their finance reports. Teachers, Result Officers, Students, and Parents do not receive full Reports Center access by default.

## Normal Workflow

1. Open Reports Center from the school workspace.
2. Apply date, class, session, term, or module status filters.
3. Review overview cards and linked module summaries.
4. Open detailed module reports for attendance, finance, LMS/CBT, communications, or other supported areas.
5. Use existing protected CSV exports only where the current role and module allow them.

## Common Issues

- User lacks Reports Center permission.
- Filter applies to one module but not another because the underlying data does not support it safely.
- Date filter meaning differs by module, such as invoice issue date versus payment date.
- Expected raw detail is intentionally hidden for privacy.
- User expects a custom BI builder, public reports, scheduled delivery, or report-card redesign.

## First Checks

- Confirm active school and user role.
- Record exact filters used.
- Compare overview card with the linked module report.
- Confirm no raw CBT answers, admission documents, payment references, meeting passwords, notification payloads, backup paths, or update internals are visible.
- Confirm export link is an existing protected export, not a public report endpoint.

## Safe Commands And UI Checks

```bash
php artisan test --filter=Reports
php artisan route:list
php artisan standalone:status
```

Use Reports Center, detailed module reports, and Import / Export as primary checks.

## What Support Should Not Do

- Do not promise custom BI, drag-and-drop report design, public report endpoints, scheduled delivery, email/SMS/WhatsApp report delivery, or cross-school analytics.
- Do not export raw databases through report tools.
- Do not expose CBT answers, payment secrets, admission documents, meeting passwords, notification private payloads, backup paths, or update internals.
- Do not treat a filtered summary mismatch as a bug until module-specific filter rules are checked.

## Escalation Points

Escalate when reports show cross-school data, private data appears in summaries, totals do not match linked module reports after filters are confirmed, or exports reveal fields outside the documented safe scope.

## Data And Privacy Warnings

Reports are for operational summaries and module navigation. Keep sensitive student, payment, admission, CBT, backup, update, and provider data out of screenshots and exported support evidence.

## Backup And Security Reminders

Reports reflect current database state. Verify backup readiness before imports, finance corrections, result publication, updates, or other work that can change report totals.

## Related Docs

- [Reports Pack](../standalone/reports-pack.md)
- [School Reports Operations](../school-operations/reports.md)
- [Finance Reports And Audit](../school-operations/finance-reports-and-audit.md)
- [Import And Export Tools](../school-operations/import-export.md)
