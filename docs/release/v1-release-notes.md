# Sanfaani Schools Standalone v1 Release Notes

Release label: `v1.0.0-rc1`

Release base commit: `d85a24a chore(release): prepare final QA release candidate`

Product boundary: Sanfaani Schools Standalone is a Laravel private single-school installation. Laravel remains the source of truth for admissions, school operations, users, roles, results, finance, learning, support, and operational records.

## Release Summary

`v1.0.0-rc1` packages the completed standalone readiness work into a production-reviewable release candidate. It is not a rebuild and does not introduce a new frontend app, SaaS billing platform, remote license server, or automated update server.

This release is ready to be reviewed as the first v1 standalone release candidate after final validation, environment review, and manual production deployment preparation.

## Included Capabilities

### Standalone School Portal

- Private standalone school portal posture for single-school installations.
- Standalone boundary cleanup separating standalone, SaaS, managed, demo, and marketplace behaviors.
- Role-aware dashboards and navigation for super admin, school admin, teacher, result officer, accountant, parent, and student contexts.
- Standalone dashboard finalization with local-first wording and operational status indicators.

### Admissions

- Admissions foundation with public admissions pages.
- Public admissions apply, track, embed, and school public-page links.
- Admission workflow, conversion, document handling, interviews, notes, payments metadata, and admin review.
- Website add-on admissions guidance for link-only v1 integration, future embed, and future API use.

### Security And System Health

- Security hardening across authentication, authorization, tenant/school context, signed links, secret redaction, email safety, and production diagnostics.
- System health and scheduler monitor foundation.
- Scheduler heartbeat command for confirming cron activity.
- Read-only deployment, security, performance, staging, and release readiness command foundations.

### Backup, Restore, And Updates

- Backup metadata, verification, retention, restore planning, and pre-update backup readiness.
- Restore guidance and backup-before-update policy.
- Guided update package review, entitlement checks, preflight checks, update logs, and rollback planning.
- Protected path guardrails for `.env`, `.env.local`, `public/build.zip`, and the protected result-publications migration.

### Attendance And Offline

- Attendance management foundation.
- Attendance reports and permissions.
- Browser/PWA offline attendance capture pilot.
- Offline sync admin monitor and support runbook.

Offline support in v1 is attendance-focused browser capture and sync. A full offline school portal is not claimed.

### Finance, Reports, And Import/Export

- Fees/accounting foundation.
- Finance reports and audit views.
- Import/export tools for school operations.
- Reports center, reports pack, report card snapshots, result publishing workflows, scratch cards, and public result checking.

### LMS, CBT, And Live Classes

- LMS foundation for classrooms, topics, materials, resources, and access control.
- LMS and CBT integration for assigning CBT activities from learning materials.
- CBT question import/rendering, attempts, autosave, grading, public access, and result integration.
- Manual live classes and provider abstraction.
- Live-class provider placeholders for Google Meet, Zoom, and Microsoft Teams are present, but real API automation is not enabled in v1.

### Communications And Notifications

- Communication center foundation.
- Notification templates, logs, preferences, recipient resolution, bulk batches, retry handling, and audit visibility.
- Email safety diagnostics and support runbooks.

External WhatsApp/SMS provider automation is not claimed unless configured and proven separately.

### Branding And White-Label

- Branding and white-label consolidation.
- Platform/school branding assets, tenant theme resolution, branded emails, public page branding, and operational branding guidance.
- White-label domain provisioning, full theme builder, and reseller tooling are deferred.

### Installer, License, And Commercial Readiness

- Standalone installer readiness foundation.
- Local/commercial license activation, validation, entitlement, diagnostics, and audit foundation.
- Support runbooks and release handoff guidance.
- Commercial packaging review, buyer feature list, pricing/plan positioning, sales discovery, demo readiness, implementation handoff, module boundaries, and support positioning.

### Website Add-On Documentation

- Next.js school website add-on strategy exists as documentation only.
- The future school website must be a separate repository.
- The Laravel app remains the operational source of truth.

## Not Included In v1

- Next.js website repository or website implementation.
- Full offline school portal.
- Real Google Meet, Zoom, or Microsoft Teams API automation.
- SaaS billing/payment gateway automation.
- Remote license server.
- Online update server, auto-download, destructive auto-apply, or automatic code patching.
- Automated restore execution.
- Marketplace API integration and marketplace ZIP generation as a completed production workflow.
- Custom BI/report builder.
- Full ERP.
- Advanced LMS submissions, discussions, analytics, or full learning marketplace workflows.
- Dependency vulnerability remediation. GitHub dependency vulnerabilities remain a separate maintenance backlog.

## Production Deployment Notes

Before using v1 for a real school:

- Configure a real production `.env` outside Git.
- Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY`, `APP_URL`, database, mail, queue, cache, session, storage, and scheduler values.
- Configure domain, SSL, server document root, writable storage/cache paths, cron, queue strategy, backup location, and mail delivery.
- Remove demo/test data and secure all admin accounts.
- Create and verify a first backup.
- Review deployment readiness, performance audit, support handoff, known limitations, and dependency vulnerability backlog.

## Validation Requirement

The final release commit must be preceded by:

- Route list validation.
- Focused PHPUnit filters for completed modules.
- Full PHPUnit suite.
- Vite production build.
- Optional read-only readiness commands where available.
- Whitespace diff check.
- Protected-file and env-file diff check.

Record exact command results in the final Stage 27 report before manual commit.
