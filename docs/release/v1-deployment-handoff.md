# v1 Deployment Handoff

Release label: `v1.0.0-rc1`

This handoff defines what each party must receive, configure, explain, and verify before Sanfaani Schools Standalone is used by a real school.

## Developer Handoff

The developer hands over:

- Reviewed release commit hash.
- Release notes and known limitations.
- Validation results for route list, focused filters, full suite, Vite build, readiness commands, whitespace check, and protected-file check.
- Deployment mode and standalone boundary notes.
- Environment variable guidance without real secrets.
- Migration and deployment command guidance.
- Backup-before-update policy.
- Rollback and restore caution.
- Support runbook index.

The developer does not hand over:

- Real `.env` secrets in Git.
- Unreviewed dependency upgrades.
- Git tags unless separately approved.
- A Next.js website repo.
- An automatic update server or remote license server.

## School/Admin Handoff

The school or administrator receives:

- Live URL.
- Admin login custody through a secure channel.
- School profile and branding checklist.
- Role/account setup checklist.
- Admissions, finance, reports, LMS, CBT, live-class, communication, and support workflow overview.
- Backup and update caution notes.
- Known limitations and backlog summary.
- Support escalation path.

The school should confirm:

- School name, logo, contact details, address, and sender email.
- First school admin account owner.
- Admissions policy and required documents.
- Fee structure and finance users.
- Result publishing policy.
- Backup and restore expectations.
- Support contact and escalation owner.

## Hosting Or Server Team Handoff

The hosting/server team configures:

- PHP 8.3+ and required extensions.
- Composer dependencies.
- Node/npm build artifacts.
- Web root mapped to Laravel `public`.
- Production `.env` outside Git.
- Database and database user.
- Writable `storage` and `bootstrap/cache`.
- Storage link or host-specific storage workaround.
- Domain and SSL.
- Mail/SMTP.
- Queue strategy.
- Cron entry for `php artisan schedule:run` every minute where supported.
- Backup location and retention.

The hosting/server team verifies:

- Home page loads over HTTPS.
- Admin login loads.
- Assets load from `public/build`.
- Uploads render correctly.
- Mail can send.
- Scheduler heartbeat becomes healthy after cron is configured.
- First backup can be created and verified.

## Business And Support Handoff

The business/support team explains:

- v1 included capabilities.
- Known limitations and backlog.
- Standalone is a private single-school install.
- Laravel is the source of truth.
- Website add-on is future separate repo/add-on.
- Offline support is attendance-focused.
- Live classes use manual links unless provider automation is separately built.
- Updates are guided/manual and require backup review.
- Dependency vulnerabilities are tracked separately.

Support should use:

- `docs/support/support-runbooks.md`
- `docs/support/issue-triage.md`
- `docs/support/release-handoff-checklist.md`
- Module-specific support runbooks for backup, update, installer/license, offline sync, live classes, finance, LMS/CBT, communications, branding, reports, and security/privacy.

## Post-Deployment Checks

After deployment:

- Confirm HTTPS and canonical domain.
- Confirm login and first admin access.
- Confirm production `.env` is not exposed.
- Confirm `APP_DEBUG=false`.
- Confirm route list can be generated.
- Confirm storage upload and public asset access.
- Confirm mail delivery.
- Confirm scheduler heartbeat.
- Confirm queue behavior.
- Confirm school branding.
- Confirm admissions public page.
- Confirm public result checker route.
- Confirm backup creation and verification.
- Confirm support contact and escalation path.

## Rollback And Restore Caution

Rollback and restore must be treated as controlled operations.

- Take a backup before updates, configuration changes, or package replacement.
- Verify backup metadata and storage location before relying on it.
- Do not run destructive database commands during routine deployment.
- Do not restore over production without written approval and a verified rollback plan.
- Preserve logs and evidence when investigating incidents.

## Backup-Before-Update Policy

Before any guided update:

- Confirm update package identity.
- Confirm protected paths are not modified.
- Confirm a recent verified backup exists.
- Confirm the update preflight passes.
- Confirm rollback notes are reviewed.
- Schedule maintenance time if the school depends on the system during business hours.

## Escalation Path

Escalate to engineering when:

- Route registration fails.
- Full test suite fails.
- Protected files are dirty.
- Production `.env` or secrets appear exposed.
- Tenant/school data isolation is suspected.
- Backup verification fails.
- Update preflight fails.
- Scheduler, queue, or mail cannot be made healthy.
- A client requests deferred functionality as if it were included in v1.
