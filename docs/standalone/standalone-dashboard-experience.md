# Standalone Dashboard Experience

The standalone dashboard is the operating home for one private school installation. It reuses the existing Super Admin, School Admin, Teacher, Result Officer, and Accountant dashboards instead of creating a parallel dashboard system.

## Installation Owner

The Super Admin dashboard is the installation-owner view. It summarizes:

- installer completion;
- standalone license status;
- school profile and branding readiness;
- backup and guided-update readiness;
- local-first sync status;
- high-level standalone system health from `docs/standalone/system-health-and-scheduler-monitoring.md`;
- admissions, attendance, fees/finance, results, and CBT activity;
- a full school setup checklist.

The owner opens the existing school workspace to perform school-scoped work. SaaS subscriptions, demo sessions, marketplace promotion, lead pipelines, and sales tasks are not the main standalone flow and remain hidden by deployment gates.

## School Admin

The School Admin dashboard keeps the existing operational modules and adds a read-only readiness checklist for:

- school profile and contact details;
- branding and logo;
- active session and term;
- classes and subjects;
- staff and student records;
- attendance foundation;
- fees/accounting foundation;
- LMS material foundation and CBT activity links;
- live class foundation;
- admissions cycles;
- result and report settings;
- CBT setup;
- backup, license, and system-health signals owned by the installation administrator.

Admissions, attendance, fees/finance, results, CBT, scratch cards, communication, promotions, and user management continue to use their existing controllers, routes, services, feature checks, and authorization.

## Teacher

The Teacher dashboard remains assignment-scoped. It shows assigned classes, attendance, LMS learning materials, live classes, subjects, students, result work, and the existing CBT question-bank or theory-marking links only when those role features are enabled.

## Result Officer

The Result Officer dashboard remains focused on result entry, upload, review, publishing, and existing CBT result workflows. School audit logs remain restricted to the School Admin; the dashboard does not broaden that authorization boundary.

## Accountant And Bursar Boundary

The Accountant dashboard provides the school-fees foundation plus Stage 11 reports, Stage 12 finance CSV export, and audit review: fee setup, assignments, student invoices, manual payment recording, billed/paid/outstanding summaries, balances, recent finance activity, report links, selected finance export, and safe finance audit visibility. It does not provide gateway automation, offline fee capture, parent/student finance access, or double-entry accounting.

## Planned Modules

The dashboard labels these areas as available or **Planned**:

- attendance foundation is available for online class attendance;
- attendance-only offline capture and its server-side sync monitor are available when enabled;
- fees/accounting foundation is available;
- finance reports, finance CSV export, and audit review are available;
- LMS and CBT activity links are available for online class/subject materials, private resources, and links to existing CBT items;
- live class foundation is available for manual meeting links, class/subject schedules, LMS context links, status workflow, and recording links;
- live class provider automation is planned for Stage 17;
- full browser offline/PWA.

They are status labels, not links to placeholder implementations.

## Local-First Wording

The local database is the source of truth. A school can operate from a local computer or LAN server while that server and database are available. Attendance is currently online-first against that school database.

The attendance-only browser offline capture pilot does not make finance or the full portal available offline. The dashboard must not claim that fee capture or every browser task works without a connection to the school server.

The LMS foundation is also online-first. Stage 15 adds links to existing CBT items, but CBT remains the assessment engine. Stage 16 adds manual live-class scheduling beside LMS. The dashboard must not describe LMS as offline LMS, assignment submissions/grading, or a parent/student LMS portal. Live classes must not be described as offline, API-generated, OAuth-enabled, or provider-integrated.
