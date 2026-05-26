# Real Staging Deployment Runbook

This runbook prepares a real staging deployment for Sanfaani Schools. It is a deployment validation plan, not deployment automation. Run each command only in the intended staging environment and keep secrets out of Git.

## Scope And Safety

- Do not modify `public/build.zip`.
- Do not modify `database/migrations/2026_05_01_173857_create_result_publications_table.php`.
- Do not generate release ZIPs.
- Do not run destructive deployment automation.
- Do not run unreviewed production changes.
- Do not commit `.env`, logs, backups, database dumps, private storage, generated archives, `vendor`, or `node_modules`.

## Server Requirements

- PHP 8.3 or newer for staging parity with production expectations.
- Composer available on the server, or dependencies installed during a controlled build handoff.
- Node.js and npm available locally or in CI for frontend build generation.
- MySQL or MariaDB database.
- Web server document root pointed to Laravel `public`, or a reviewed shared-hosting public-folder workaround.
- Writable `storage` and `bootstrap/cache`.
- Cron support for `php artisan schedule:run`.
- Queue mode chosen for the host: `sync` or `database` for shared hosting, worker process for VPS/cloud.
- SMTP test mailbox or staging SMTP provider.

## PHP Extension Checklist

Required baseline:

- `ctype`
- `curl`
- `fileinfo`
- `json`
- `mbstring`
- `openssl`
- `pdo`
- `tokenizer`
- `xml`

Recommended where available:

- `bcmath`
- `gd`
- `intl`
- `pdo_mysql`
- `redis`
- `zip`

Run:

```bash
php -m
php artisan deployment:check-readiness
```

## Source And Dependency Preparation

1. Deploy the reviewed branch or release candidate commit to staging.
2. Confirm the branch is `feature/v7-cbt-localization-hardening` or an approved release branch created from it.
3. Confirm protected dirty files are not staged.
4. Install PHP dependencies:

```bash
composer install --no-dev --optimize-autoloader
```

5. Build frontend assets locally or in CI:

```bash
npm ci
npm run build
```

6. Upload or deploy `public/build` only. Do not use `public/build.zip` as a staging package.

## Staging .env Setup

Use `docs/staging/staging-env-template.md` as the staging `.env` checklist. Required baseline:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://staging.example.test
APP_KEY=base64:generate-a-real-staging-key
DB_CONNECTION=mysql
MAIL_MAILER=smtp
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
SANFAANI_DEPLOYMENT_MODE=saas
SANFAANI_LICENSE_MODE=subscription
```

Generate the real staging key on the staging host only:

```bash
php artisan key:generate
```

Do not commit the generated `.env`.

## Database Setup

1. Create a staging database and database user.
2. Grant only the permissions needed by the staging app.
3. Configure `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`.
4. Do not use production data unless it has been approved, sanitized, and access-controlled.
5. Back up staging before migrations if staging already contains data.

Migration guidance:

```bash
php artisan migrate --pretend
php artisan migrate --force
```

Run migrations only after backup readiness and go/no-go approval for the staging target.

## Storage Link And Permissions

Set writable paths:

- `storage`
- `bootstrap/cache`

Create storage link if needed:

```bash
php artisan storage:link
```

If shared hosting blocks symlinks, use the documented storage-link workaround and keep private files outside public access.

## Cron And Scheduler Setup

Configure cron where available:

```bash
* * * * * cd /path/to/sanfaani-schools && php artisan schedule:run >> /dev/null 2>&1
```

Confirm demo expiry, queues, marketing tasks, and maintenance tasks are appropriate for the selected staging mode.

## Queue Setup

Shared hosting:

```dotenv
QUEUE_CONNECTION=database
SANFAANI_QUEUE_SYNC_FALLBACK=true
```

VPS/cloud:

```dotenv
QUEUE_CONNECTION=database
```

Use a reviewed worker process only when the host supports long-running workers. Keep staging jobs small and idempotent.

## SMTP Setup

Set staging SMTP values:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.staging.example
MAIL_PORT=587
MAIL_USERNAME=staging-mail-user
MAIL_PASSWORD=change-me
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@staging.example.test
MAIL_FROM_NAME="${APP_NAME}"
```

Validate mail through staging-only recipients. Do not send staging tests to real users.

## Required Validation Commands

```bash
php artisan test
php artisan route:list
php artisan staging:check-readiness
php artisan deployment:check-readiness
php artisan performance:audit
APP_ENV=production APP_DEBUG=false php artisan security:audit
php artisan release:check-readiness
php artisan marketplace:validate-package
git diff --check
```

## Backup Readiness

- Confirm `SANFAANI_BACKUPS_ENABLED=true` for modes that expose backup readiness.
- Confirm backups are not under public web root.
- Confirm pre-update backup metadata requirements are understood.
- Create a manual database backup before migration or update validation.
- Record backup location and owner privately.
- Automated restore remains planned; use manual restore plans and controlled restore drills only.

## Update Preflight

- Confirm `SANFAANI_UPDATES_ENABLED=true` where guided update review should be visible.
- Confirm update package metadata and preflight are used only for staging validation.
- Run update preflight tests or the relevant UI preflight for uploaded test metadata.
- Real update download, extraction, patching, migration orchestration, and application remain planned.

## Installer Validation

For single-school or marketplace buyer staging:

- `SANFAANI_DEPLOYMENT_MODE=single_school`
- `SANFAANI_INSTALLER_ENABLED=true`
- `SANFAANI_INSTALLED=false` before installer validation.
- Confirm installer welcome, requirements, permissions, database, environment, app key, migrations, admin, school, SMTP, review, and complete stages.
- Confirm reinstall lock behavior.

## License Validation

- Confirm `SANFAANI_LICENSE_VALIDATION_ENABLED=true`.
- Confirm selected license mode: `subscription`, `annual`, `lifetime`, `managed_contract`, `white_label`, `trial`, or `demo`.
- Confirm raw license keys are not committed.
- Confirm local activation and validation behavior.
- Remote license server sync remains planned.

## Branding Validation

- Confirm `SANFAANI_BRANDING_ENABLED=true`.
- Confirm `SANFAANI_BRAND_MODE` for the selected mode.
- Validate platform, school, managed, or white-label branding routes.
- Upload only safe staging assets.
- Confirm logo, favicon, public page, login, email footer, and report footer behavior.
- White-label domain provisioning and reseller tooling remain planned.

## Demo, Onboarding, And Marketing Validation

- Demo: validate request form, admin demo sessions, credentials, activity, and expiry. Demo reset remains disabled unless a safe demo-only reset pattern exists.
- Onboarding: validate role-based checklists and progress visibility for selected mode.
- Marketing: validate lead scoring, sales task, unsubscribe, and email-safety behavior using staging-only contacts. Provider-specific WhatsApp sending remains planned.

## Mode-By-Mode Testing

Use these checklists:

- `docs/staging/saas-mode-staging-checklist.md`
- `docs/staging/single-school-mode-staging-checklist.md`
- `docs/staging/managed-mode-staging-checklist.md`
- `docs/staging/white-label-staging-checklist.md`
- `docs/staging/marketplace-buyer-staging-checklist.md`
- `docs/staging/demo-trial-staging-checklist.md`

Record results in:

- `docs/staging/staging-smoke-test-checklist.md`
- `docs/staging/staging-go-no-go-report-template.md`

## Go/No-Go Criteria

Go when:

- Full test suite passes.
- Route list succeeds.
- Staging readiness command exits successfully.
- Deployment readiness has no failures.
- Performance audit has no failures.
- Security audit passes with production-style env overrides.
- Release readiness has no failures.
- Marketplace validation passes without creating a ZIP.
- Protected files are not staged.
- Smoke tests pass for the selected mode.
- Known limitations are accepted.

No-go when:

- Any validation command fails.
- Protected files are staged.
- Staging `.env` is missing or unsafe.
- Database, storage, cron, queue, SMTP, license, backup, update, branding, demo, onboarding, or marketing validation is incomplete for the selected mode.
- Staging copy claims full billing automation, real update application, automated restore, marketplace ZIP generation, full parent/student portals, white-label domain provisioning, or reseller tooling as completed.

## Rollback And Incident Handling

Use `docs/staging/staging-incident-rollback-checklist.md`.

Minimum incident actions:

1. Stop the rollout.
2. Preserve logs privately.
3. Confirm no public secrets or backups are exposed.
4. Revert to the previous known-good staging release.
5. Restore database manually only from an approved backup and only after owner approval.
6. Record root cause, owner, and follow-up.

This runbook does not automate rollback or restore.
