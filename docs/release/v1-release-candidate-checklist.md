# v1 Release Candidate Checklist

Candidate base commit: `26a1647 docs(website): add Next.js school website integration foundation`

Prepared for: final QA, regression, and release-candidate handoff

## Guardrails

- [ ] Confirm current branch is `main`.
- [ ] Confirm latest commit is `26a1647 docs(website): add Next.js school website integration foundation` or a later approved release-candidate commit.
- [ ] Confirm the working tree was clean before Stage 26 edits began.
- [ ] Do not commit, push, stage, reset, stash, clean, checkout, revert, or discard changes during QA.
- [ ] Do not modify `public/build.zip`.
- [ ] Do not modify `database/migrations/2026_05_01_173857_create_result_publications_table.php`.
- [ ] Do not modify `.env`.
- [ ] Do not modify `.env.local`.
- [ ] Do not run dependency upgrades such as `composer update`, `npm update`, or package-major changes.
- [ ] Do not add a Next.js website app to this Laravel repository.

## Automated Validation

- [ ] `php artisan route:list`
- [ ] `php artisan test --filter=Standalone`
- [ ] `php artisan test --filter=Admission`
- [ ] `php artisan test --filter=AdmissionSecurity`
- [ ] `php artisan test --filter=Attendance`
- [ ] `php artisan test --filter=Offline`
- [ ] `php artisan test --filter=Finance`
- [ ] `php artisan test --filter=Reports`
- [ ] `php artisan test --filter=Report`
- [ ] `php artisan test --filter=Lms`
- [ ] `php artisan test --filter=LMS`
- [ ] `php artisan test --filter=Cbt`
- [ ] `php artisan test --filter=CBT`
- [ ] `php artisan test --filter=LiveClass`
- [ ] `php artisan test --filter=Communication`
- [ ] `php artisan test --filter=Notification`
- [ ] `php artisan test --filter=Branding`
- [ ] `php artisan test --filter=WhiteLabel`
- [ ] `php artisan test --filter=Installer`
- [ ] `php artisan test --filter=License`
- [ ] `php artisan test --filter=Update`
- [ ] `php artisan test --filter=Backup`
- [ ] `php artisan test --filter=Health`
- [ ] `php artisan test --filter=Dashboard`
- [ ] `php artisan test`
- [ ] `npm run build`
- [ ] `git diff --check`
- [ ] `git status --short`
- [ ] `git diff --name-only -- public/build.zip database/migrations/2026_05_01_173857_create_result_publications_table.php .env .env.local`

## Optional Read-Only Readiness Commands

- [ ] `php artisan schedule:list`
- [ ] `php artisan standalone:status`
- [ ] `php artisan deployment:check-readiness`
- [ ] `php artisan performance:audit`

## Manual Functional QA

- [ ] Public landing page loads.
- [ ] Public features, pricing, contact, demo, privacy, and terms pages load.
- [ ] Admissions landing, apply, tracking, embed, and public admissions API behavior match configuration.
- [ ] Public school pages, school admissions links, school contact links, and result-checker links load.
- [ ] Auth login, admin login, password reset, and workspace selection routes load.
- [ ] Super admin dashboard and platform settings respect deployment behavior.
- [ ] School admin dashboard and school operations modules load according to enabled features.
- [ ] Teacher workflows load only assigned teaching, attendance, LMS, CBT, result, and communication surfaces.
- [ ] Result officer workflows load result review, publishing, report, and scratch card surfaces.
- [ ] Accountant workflows load finance, fee, payment, balance, and report surfaces.
- [ ] Parent and student dashboards are limited to intended profile, learning, CBT, notification, result, and payment surfaces.
- [ ] Notifications, search, and profile routes work for authenticated roles.
- [ ] Demo-safe behavior blocks unsafe actions in demo/trial mode.

## Module QA

- [ ] Standalone status, dashboard summary, sync foundation, and system health.
- [ ] Installer requirements, setup, access control, and reinstall prevention.
- [ ] Licensing activation, validation, entitlement, diagnostics, and audit trail.
- [ ] Update dashboard, manifest parsing, preflight checks, entitlement, package metadata, and rollback planning.
- [ ] Backup dashboard, backup creation metadata, verification, retention, and restore guidance.
- [ ] Admissions public application, tracking, admin workflow, conversion, security, and website integration.
- [ ] Attendance capture and offline attendance sync monitor.
- [ ] Finance invoices, payments, balances, reports, and audit views.
- [ ] Reports pack, report card snapshots, result publishing, public result checker, and scratch cards.
- [ ] LMS classrooms, materials, resource storage, access checks, and CBT integration.
- [ ] CBT question import/rendering, attempts, autosave, grading, result integration, and public access links.
- [ ] Live-class scheduling, provider registry, manual provider, Zoom, Google Meet, Microsoft Teams, and future API provider boundaries.
- [ ] Communications templates, logs, bulk batches, retries, notifications, and recipient resolution.
- [ ] Branding assets, resolver, validation, email branding, and white-label documentation boundaries.
- [ ] Deployment readiness, performance diagnostics, security diagnostics, staging docs, and support runbooks.

## Documentation QA

- [ ] `docs/README.md` describes current product state without overstating deferred systems.
- [ ] `docs/SUMMARY.md` links release-candidate docs.
- [ ] `docs/documentation-url-map.md` exposes release-candidate docs for the future documentation site.
- [ ] Support runbooks cover triage, backup/restore, update, installer/license, offline sync, live classes, finance, LMS/CBT, communications, branding, reports, security/privacy, and handoff.
- [ ] Commercial docs cover buyer feature list, pricing/plan positioning, demo readiness, sales discovery, implementation handoff, module boundaries, standalone-vs-SaaS positioning, support/maintenance positioning, and release readiness.
- [ ] Website add-on docs state that the Next.js site is a future separate repository and not part of this Laravel codebase.

## Security And Privacy QA

- [ ] Public POST routes are throttled where appropriate.
- [ ] Authenticated surfaces require auth and role/feature/deployment middleware.
- [ ] School context and tenant boundaries are enforced.
- [ ] Admissions API and embed behavior are configuration controlled.
- [ ] Demo-safe middleware protects unsafe demo actions.
- [ ] Secrets, credentials, logs, backups, database dumps, generated archives, `.env`, and `.env.local` are not included in package or commit scope.
- [ ] Production readiness notes require `APP_DEBUG=false`, real `APP_KEY`, reviewed SMTP, scheduler, queue, and backup setup.

## Dependency And Vulnerability Backlog

- [ ] Record dependency audit output in a separate maintenance issue if a formal dependency review is requested.
- [ ] Do not mix dependency upgrades into the RC commit unless a release manager explicitly approves a dependency-fix scope.
- [ ] Treat vulnerability remediation, package upgrades, and lockfile churn as separate work from documentation-only RC QA.

## Known Deferred Features

- [ ] Real update download and application.
- [ ] Automated restore execution.
- [ ] Marketplace ZIP generation and marketplace API integration.
- [ ] Full billing/payment automation.
- [ ] Full parent and student portal workflows.
- [ ] White-label domain provisioning, full theme builder, and reseller tooling.
- [ ] Actual Next.js school website repository and implementation.

## Go/No-Go Signoff

- [ ] All required automated validation commands have run.
- [ ] Any warning from optional readiness commands is reviewed and accepted.
- [ ] Manual role QA is complete.
- [ ] Protected files have no diff.
- [ ] No release-blocking issue remains open.
- [ ] Release manager records final go/no-go decision.
