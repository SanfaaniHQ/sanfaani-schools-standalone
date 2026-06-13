# Reports Pack

The Reports Pack adds a school-scoped Reports Center for standalone installations without replacing existing module reports.

The center is available at `/school/reports` for School Admins and Super Admins acting inside a school context. It reuses existing records, services, protected routes, and CSV tools.

## Included Summaries

- Students, classes, subjects, sessions, and terms.
- Admissions applications by safe workflow status.
- Attendance records, present/absent/late/excused counts, and single-class/day missing indicators.
- Finance billed, paid, outstanding, and overdue summaries through the existing finance report service.
- LMS classrooms, published materials, active LMS-CBT links, CBT exams, attempts, and result publication counts.
- Live-class status counts for scheduled, live, completed, and cancelled sessions.
- Communication notification logs and templates.
- Offline attendance sync receipt status and high-level system, backup, and update readiness signals.

## Filters

The Reports Center supports practical filters where the underlying module can apply them safely:

- date range;
- class;
- academic session;
- term;
- module-specific status.

Some modules do not have every filter field. When a filter does not safely apply to a module, that module keeps its school-scoped summary boundary.

## Export Boundary

The Reports Center only links to existing protected CSV tools:

- student export;
- attendance summary export;
- finance summary export;
- the Import / Export workspace.

It does not add new export routes, PDF engines, full database exports, or public report endpoints.

## Privacy Rules

- Reports are scoped to the active school.
- CBT answers, answer payloads, client snapshots, security snapshots, and candidate secrets are not loaded.
- Admission documents, tracking tokens, private notes, and admission payment payloads are not shown.
- Finance references, payment notes, gateway payloads, and payment secrets remain out of the report center.
- Live-class meeting passwords, provider credentials, provider payloads, and private meeting metadata are not exposed.
- Notification private payloads, backup paths, update internals, environment values, and sync tokens are not exposed.
- Blade output remains escaped.

## Roles

School Admins can access the Reports Center. Super Admins can access it only while acting in a school context.

Teachers, Accountants, Result Officers, Students, and Parents do not receive full Reports Center access by default. They keep their existing role-specific pages, such as teacher class reports, finance reports for Accountants, and result or CBT workflows for Result Officers.

## Not Included

- BI engine.
- Custom report builder.
- Drag-and-drop report designer.
- Public reports.
- Parent/student report portal.
- New PDF engine or report-card redesign.
- Report scheduling.
- Email, SMS, or WhatsApp report delivery.
- Cross-school reports.
