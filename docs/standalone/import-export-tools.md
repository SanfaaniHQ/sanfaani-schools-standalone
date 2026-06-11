# Standalone Import And Export Tools

Stage 12 adds controlled CSV import/export tools for a standalone school installation. The feature is intentionally narrow: it moves selected school operational data without exposing database dumps, secrets, or raw internal metadata.

## Available Tools

- Student CSV export.
- Student CSV template download.
- Student CSV preview and optional commit.
- Attendance summary CSV export.
- Finance invoice/payment summary CSV export.

## Standalone Boundary

The local Laravel application and database remain the source of truth. CSV tools are for selected operational extracts and safe student onboarding only. Backup-grade export remains owned by the backup subsystem.

The feature does not add:

- SaaS-wide export;
- full database dump;
- public data export;
- payment gateway automation;
- offline fee capture;
- offline attendance import;
- parent/student finance access;
- full accounting ledgers or reconciliation.

## Security Notes

All tools run through the existing authenticated school workspace and active-school context. Student and attendance exports are limited to School Admins. Finance export is available to School Admins and Accountants with finance access. Audit logs store safe metadata only, not uploaded file contents.

## Related Documentation

- `docs/school-operations/import-export.md`
- `docs/school-operations/finance.md`
- `docs/school-operations/finance-reports-and-audit.md`
- `docs/standalone/fees-accounting-foundation.md`
