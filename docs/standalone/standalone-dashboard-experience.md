# Standalone Dashboard Experience

The standalone dashboard is the operating home for one private school installation. It reuses the existing Super Admin, School Admin, Teacher, and Result Officer dashboards instead of creating a parallel dashboard system.

## Installation Owner

The Super Admin dashboard is the installation-owner view. It summarizes:

- installer completion;
- standalone license status;
- school profile and branding readiness;
- backup and guided-update readiness;
- local-first sync status;
- high-level standalone system health from `docs/standalone/system-health-and-scheduler-monitoring.md`;
- admissions, attendance, results, and CBT activity;
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
- admissions cycles;
- result and report settings;
- CBT setup;
- backup, license, and system-health signals owned by the installation administrator.

Admissions, attendance, results, CBT, scratch cards, communication, promotions, and user management continue to use their existing controllers, routes, services, feature checks, and authorization.

## Teacher

The Teacher dashboard remains assignment-scoped. It shows assigned classes, attendance, subjects, students, result work, and the existing CBT question-bank or theory-marking links only when those role features are enabled.

## Result Officer

The Result Officer dashboard remains focused on result entry, upload, review, publishing, and existing CBT result workflows. School audit logs remain restricted to the School Admin; the dashboard does not broaden that authorization boundary.

## Accountant And Bursar Boundary

There is no complete accountant or bursar dashboard in this release. Existing financial actions are limited to the admission-payment and scratch-card payment workflows already implemented. Full fees and accounting is planned and must not be presented as complete.

## Planned Modules

The dashboard labels these areas as available or **Planned**:

- attendance foundation is available for online class attendance;
- offline attendance capture is planned for a later stage;
- LMS and learning content;
- live classes;
- full fees and accounting;
- full browser offline/PWA.

They are status labels, not links to placeholder implementations.

## Local-First Wording

The local database is the source of truth. A school can operate from a local computer or LAN server while that server and database are available. Attendance is currently online-first against that school database.

Browser offline attendance capture and full browser offline/PWA are not complete yet. The dashboard must not claim that every browser task works without a connection to the school server.
