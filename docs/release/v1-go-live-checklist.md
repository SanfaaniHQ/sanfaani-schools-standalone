# v1 Go-Live Checklist

Release label: `v1.0.0-rc1`

Use this checklist before deploying Sanfaani Schools Standalone for a real school.

## Repository And Release

- [ ] Repository is clean before final release commit.
- [ ] Latest release commit hash is recorded.
- [ ] Stage 27 release docs are reviewed.
- [ ] `git status --short` is reviewed.
- [ ] Final release commit is pushed by the user.
- [ ] No Git tags are created unless separately approved.
- [ ] No dependency upgrade is mixed into the release commit.
- [ ] GitHub dependency vulnerabilities are reviewed separately as a maintenance/security backlog.

## Validation

- [ ] `php artisan route:list` passed.
- [ ] `php artisan test --filter=Standalone` passed.
- [ ] `php artisan test --filter=Admission` passed.
- [ ] `php artisan test --filter=AdmissionSecurity` passed.
- [ ] `php artisan test --filter=Attendance` passed.
- [ ] `php artisan test --filter=Offline` passed.
- [ ] `php artisan test --filter=Finance` passed.
- [ ] `php artisan test --filter=Reports` passed.
- [ ] `php artisan test --filter=Report` passed.
- [ ] `php artisan test --filter=Lms` passed.
- [ ] `php artisan test --filter=LMS` passed.
- [ ] `php artisan test --filter=Cbt` passed.
- [ ] `php artisan test --filter=CBT` passed.
- [ ] `php artisan test --filter=LiveClass` passed.
- [ ] `php artisan test --filter=Communication` passed.
- [ ] `php artisan test --filter=Notification` passed.
- [ ] `php artisan test --filter=Branding` passed.
- [ ] `php artisan test --filter=WhiteLabel` passed.
- [ ] `php artisan test --filter=Installer` passed.
- [ ] `php artisan test --filter=License` passed.
- [ ] `php artisan test --filter=Update` passed.
- [ ] `php artisan test --filter=Backup` passed.
- [ ] `php artisan test --filter=Health` passed.
- [ ] `php artisan test --filter=Dashboard` passed.
- [ ] Full `php artisan test` passed.
- [ ] `npm run build` passed.
- [ ] `git diff --check` passed.
- [ ] Protected-file and env-file diff check passed.

## Protected Files And Secrets

- [ ] `public/build.zip` has no diff.
- [ ] `database/migrations/2026_05_01_173857_create_result_publications_table.php` has no diff.
- [ ] `.env` has no diff and is not staged.
- [ ] `.env.local` has no diff and is not staged.
- [ ] Secrets, license keys, payment keys, SMTP credentials, logs, backups, database dumps, and generated archives are not committed.

## Production Environment

- [ ] Production `.env` is reviewed outside Git.
- [ ] `APP_ENV=production`.
- [ ] `APP_DEBUG=false`.
- [ ] Production `APP_KEY` is configured.
- [ ] Production `APP_URL` matches the live domain.
- [ ] Production database connection is configured.
- [ ] Database user permissions are reviewed.
- [ ] Storage disk and upload paths are configured.
- [ ] `storage` and `bootstrap/cache` are writable.
- [ ] Cache driver is selected.
- [ ] Session driver is selected.
- [ ] Queue driver is selected.
- [ ] Scheduler/cron is configured.
- [ ] Mail/SMTP is configured and tested.
- [ ] Domain and SSL are configured.
- [ ] Public directory mapping points to Laravel `public`.
- [ ] Demo/test data is removed before real production use.

## Standalone Product Setup

- [ ] Standalone mode is confirmed for the client.
- [ ] Installer status is reviewed.
- [ ] License status is reviewed.
- [ ] School admin account is secured.
- [ ] School profile and branding are configured.
- [ ] Admissions settings are reviewed.
- [ ] Finance settings are reviewed.
- [ ] Result settings and scratch card/result checker settings are reviewed.
- [ ] LMS/CBT/live-class settings are reviewed.
- [ ] Communication and notification settings are reviewed.
- [ ] Support escalation path is configured.

## Backup, Update, And Restore Readiness

- [ ] Backup location is configured.
- [ ] First verified backup is created.
- [ ] Backup retention is documented.
- [ ] Restore procedure is reviewed.
- [ ] Backup-before-update policy is accepted.
- [ ] Update readiness is reviewed.
- [ ] Guided update limits are explained to the school.
- [ ] No destructive auto-update behavior is promised.

## Deployment Readiness

- [ ] `php artisan schedule:list` reviewed.
- [ ] `php artisan standalone:status` reviewed.
- [ ] `php artisan deployment:check-readiness` reviewed.
- [ ] `php artisan performance:audit` reviewed.
- [ ] Warnings are either resolved or accepted with owners.
- [ ] Shared-hosting or VPS/cloud-specific instructions are followed.
- [ ] Rollback and restore caution is understood.

## Client Handoff

- [ ] Support docs are handed over.
- [ ] Known limitations are explained to client.
- [ ] Dependency vulnerabilities are tracked separately.
- [ ] Admin login and first account custody are transferred securely.
- [ ] Client confirms school name, logo, domain, email sender, and support contacts.
- [ ] Post-launch smoke test is completed.
- [ ] Go-live approval is recorded.
