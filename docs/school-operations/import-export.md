# Import And Export Tools

Stage 12 adds a school-scoped CSV workspace at **Import / Export**. It reuses existing students, attendance, finance, authorization, and audit systems instead of creating a separate data-transfer subsystem.

## Access

- School Admins can use student CSV export, student import template, student import preview/commit, attendance summary export, and finance summary export.
- Accountants can access the workspace for finance summary CSV export only when `finance.view` is enabled.
- Teachers, Result Officers, Parents, Students, and public users cannot access these tools.
- Every query is restricted to the active school context.

## Student CSV Export

Student export includes safe operational fields only:

- admission number;
- first, middle, and last name;
- gender;
- date of birth;
- class;
- guardian name, phone, and email;
- status.

The export supports class, status, and search filters. It does not export addresses, passwords, tokens, raw metadata, notes, or unrelated schools.

## Student Import

Student import is preview-first:

1. Download the CSV template.
2. Upload a CSV with the required columns: `admission_number`, `first_name`, `last_name`, `gender`, and `class`.
3. Review validation results.
4. Confirm import only after the preview passes.

Preview does not write records. Commit revalidates class membership and duplicate admission numbers before creating students. Imports create new students only; they do not update existing students. The per-file limit is 200 non-empty rows.

## Attendance Summary Export

Attendance export produces class/date summary rows with present, absent, late, excused, and total counts. It supports date, date range, class, status, academic session, and term filters. Notes and raw offline sync payloads are excluded.

## Finance Summary Export

Finance export produces invoice and payment summary rows. It supports date range, class, academic session, term, invoice status, and payment method filters.

The export includes safe invoice/payment totals and context. It excludes payment references, payment notes, raw metadata, secrets, and full accounting data.

## Audit

Each download, preview, validation failure, and committed import writes a safe audit log entry. Audit metadata records tool name, module, filters, row counts, file name, and actor ID where relevant. It does not store uploaded CSV rows or raw file contents.

## Deferred Boundaries

- No full database export is implemented here.
- No public export endpoint is added.
- No Excel, PDF, unrestricted dump, or backup replacement is added.
- No password import, payment gateway import, offline attendance import, or finance transaction import is added.
- Full database backup/export remains in the backup system.
