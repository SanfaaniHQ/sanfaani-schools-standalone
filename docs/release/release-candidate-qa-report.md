# Release Candidate QA Report

Date prepared: 2026-06-13

Release candidate base commit: `26a1647 docs(website): add Next.js school website integration foundation`

Branch expectation: `main`

## Purpose

This report is the final release-candidate QA record for the standalone Sanfaani Schools codebase. It consolidates the route, configuration, middleware, documentation, and automated regression checks required before a manual release commit.

This report does not approve deployment by itself. It records the evidence that must be reviewed before a release manager signs off.

## Scope Reviewed

- Public Laravel website, admissions, school public pages, result checker, CBT attempt, marketing tracking, and authentication routes.
- Authenticated dashboard, workspace selection, onboarding, school operations, super admin, support access, notification, search, and profile routes.
- School modules for admissions, attendance, offline sync, finance, reports, LMS, CBT, live classes, communications, branding, student lifecycle, academic setup, results, and role-aware navigation.
- Standalone, installer, licensing, update, backup, deployment readiness, performance, security, staging, marketplace, demo, onboarding, marketing, support, and website add-on documentation.
- Middleware and service boundaries for roles, license checks, deployment behavior, feature flags, communication features, school feature flags, installer access, demo-safe actions, valid school context, locale, and idle timeout.
- Laravel/Vite packaging surface. This repository remains a Laravel application and does not contain a Next.js app.

## Major Modules In Candidate

| Area | Candidate evidence |
| --- | --- |
| Standalone operations | Standalone status command, local-first/offline service foundation, support runbooks |
| Admissions | Public forms, tracking, website API contract, throttling, embed guidance, conversion workflows |
| Attendance/offline | Attendance foundation, offline capture/sync monitor docs and tests |
| Finance/reports | School finance service, reports pack, report card snapshots, scratch card/result publishing flows |
| LMS/CBT/live classes | LMS classroom/material services, CBT attempt/grading/integration, live-class provider abstraction |
| Communications | Notification preferences, bulk communication, communication templates, logs, retry flow |
| Branding/white-label | Branding resolver/assets/email tests, public-page and white-label positioning docs |
| Installer/license/update/backup | Installer hardening, license validation/entitlements, update preflight, backup metadata and verification |
| Deployment/support | Deployment readiness, performance audit, staging docs, support runbooks, commercial handoff docs |
| Website add-on | Separate Next.js website repo strategy and Laravel link contract only |

## Required Automated Validation

Run these commands from the repository root before release signoff:

```bash
php artisan route:list
php artisan test --filter=Standalone
php artisan test --filter=Admission
php artisan test --filter=AdmissionSecurity
php artisan test --filter=Attendance
php artisan test --filter=Offline
php artisan test --filter=Finance
php artisan test --filter=Reports
php artisan test --filter=Report
php artisan test --filter=Lms
php artisan test --filter=LMS
php artisan test --filter=Cbt
php artisan test --filter=CBT
php artisan test --filter=LiveClass
php artisan test --filter=Communication
php artisan test --filter=Notification
php artisan test --filter=Branding
php artisan test --filter=WhiteLabel
php artisan test --filter=Installer
php artisan test --filter=License
php artisan test --filter=Update
php artisan test --filter=Backup
php artisan test --filter=Health
php artisan test --filter=Dashboard
php artisan test
npm run build
git diff --check
git status --short
git diff --name-only -- public/build.zip database/migrations/2026_05_01_173857_create_result_publications_table.php .env .env.local
```

Optional read-only readiness commands, when registered:

```bash
php artisan schedule:list
php artisan standalone:status
php artisan deployment:check-readiness
php artisan performance:audit
```

## Stage 26 Command Results

| Command group | Status | Notes |
| --- | --- | --- |
| Route list | Pass | `php artisan route:list` completed and registered 523 routes. |
| Focused PHPUnit filters | Pass | All required filters passed. `Standalone` was rerun after the safe diagnostics fix and passed 41 tests with 316 assertions. |
| Full PHPUnit suite | Pass | Final post-fix `php artisan test` passed 836 tests with 3,731 assertions. |
| Vite production build | Pass with advisory | `npm run build` completed. Vite reported plugin timing guidance only; no protected artifact was modified. |
| Whitespace/status/protected-file checks | Pass | Final checks must show no whitespace errors and no protected-file diffs. The only expected dirty files are Stage 26 docs plus the standalone diagnostics guard/test. |
| Optional readiness commands | Pass with launch warnings | `schedule:list`, `standalone:status`, `deployment:check-readiness`, and `performance:audit` are registered and runnable. Readiness warnings are deployment setup notes, not code failures. |

## Issues Found And Safe Fixes

| Severity | Area | Finding | Resolution |
| --- | --- | --- | --- |
| Low | Standalone diagnostics | `php artisan standalone:status` crashed when the configured SQLite database file was missing in a fresh release-candidate environment. | `StandaloneSyncService` now treats missing or unavailable sync tables as not ready, allowing the read-only status command to report `tables not migrated`. A regression test covers the missing database path. |

## Manual QA Checklist

- Confirm first load of public landing, features, pricing, contact, demo, legal, admissions, public school page, result checker, and portal login routes.
- Confirm school admin can access dashboard, academic setup, students, admissions, attendance, finance, reports, LMS, CBT, live classes, communications, branding, and settings according to role/feature gates.
- Confirm teacher can access assigned classes, attendance, LMS/CBT workflows, result entry/review, notifications, and profile areas according to permissions.
- Confirm result officer can access result workspace, report workflows, review/publish flows, and result checker configuration according to permissions.
- Confirm accountant can access fees, invoices, payments, balances, finance reports, and export flows according to permissions.
- Confirm parent and student roles are limited to their intended dashboard, profile, notification, learning, CBT, result, and payment surfaces.
- Confirm super admin can access platform dashboard, schools, support access, release/deployment diagnostics, commercial foundations, and standalone status where deployment behavior allows.
- Confirm demo/trial mode blocks unsafe destructive actions through `demo.safe` behavior.

## Role QA Boundaries

| Role | Must verify |
| --- | --- |
| Super admin | Platform dashboards, school management, support access, deployment mode behavior, licensing, update/backup diagnostics |
| School admin | School operations, admissions, academic setup, students, finance, reports, communications, branding, LMS/CBT/live class settings |
| Teacher | Assigned class/subject access, attendance, LMS materials, CBT access, result entry and notifications |
| Result officer | Result review/publishing, reports, scratch card/result checker workflows |
| Accountant | Fees, payment records, balances, finance report exports |
| Parent | Child-specific dashboard, notifications, published results, payment-related surfaces |
| Student | Student dashboard, learning materials, CBT attempts, published results |

## Security And Privacy Review

- Public submissions must remain throttled and validated.
- Admissions API defaults and embed behavior must remain controlled by configuration.
- Website add-on guidance must keep the Next.js site in a separate repository and avoid copying secrets, private records, or protected admin APIs.
- `.env`, `.env.local`, credentials, license keys, backups, logs, caches, database dumps, and public archives must not be added to release packaging.
- Tenant and school context must remain enforced by middleware, policies, and service boundaries.
- Production launch must use `APP_DEBUG=false`, a real `APP_KEY`, reviewed SMTP settings, configured queue/scheduler behavior, and a real backup plan.

## Deployment Readiness Notes

- `public/build.zip` is a protected legacy/package artifact and is not required for Laravel runtime deployment.
- Real deployment should use reviewed built assets from `npm run build`, not ad hoc public archives.
- Scheduler setup should run `php artisan schedule:run` every minute where hosting supports cron.
- Queue strategy must be selected per hosting mode: sync/database for simple shared hosting, workers for VPS/cloud.
- Real update download/application, automated restore execution, marketplace API integration, and full billing automation remain outside this release candidate.

## Known Limitations And Deferred Items

- Real update download and application are not complete.
- Automated restore execution is not complete.
- Marketplace ZIP generation and marketplace API integration are not complete.
- Full billing/payment automation is not complete.
- Full parent and student portal workflows are not complete.
- White-label domain provisioning, full theme builder, and reseller tooling are not complete.
- The Next.js school website add-on is documentation and integration contract only; no Next.js app is included in this Laravel repository.
- Dependency vulnerability remediation is tracked as a separate maintenance backlog and must not be mixed into this release candidate without a dedicated dependency review.

## Go/No-Go Criteria

Go is acceptable only when:

- Required automated commands pass or any warning is explicitly documented and accepted.
- Manual role QA is completed for every release role.
- Protected files show no diff.
- `.env` and `.env.local` show no diff and are not staged.
- No unexpected generated package/archive is added.
- Known limitations above are reflected in buyer, support, and deployment documentation.
- A release manager confirms the candidate is ready for manual commit.

No-go is required when:

- Full PHPUnit suite fails.
- `npm run build` fails.
- Protected files are dirty.
- A release-blocking route, auth, tenant, school context, license, installer, backup, update, or deployment issue is found.
- Documentation claims a planned/deferred system is complete.
