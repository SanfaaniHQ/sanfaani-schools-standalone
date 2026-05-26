# Staging Smoke Test Checklist

Run this checklist after deployment and before go/no-go approval.

## Core Platform

- [ ] Login works for Super Admin.
- [ ] Dashboard loads without errors.
- [ ] Workspace selection works for multi-role users.
- [ ] School list, create, edit, archive, and restore paths load for authorized users.
- [ ] School Admin dashboard loads.
- [ ] Student, class, subject, term, and staff screens load.
- [ ] Teacher assignment screens load.
- [ ] Result entry, review, publishing, public result checker, and verification flows load.
- [ ] CBT dashboard, exam, question bank, public access, save, submit, and result routes load.
- [ ] Support thread routes load.
- [ ] Communication routes load.

## Commercial Foundations

- [ ] Installer routes load where mode allows.
- [ ] License activation and validation routes load where mode allows.
- [ ] Demo request and demo admin routes load where mode allows.
- [ ] Onboarding progress is visible where mode allows.
- [ ] Marketing dashboard, lead scoring, sales tasks, and unsubscribe are validated where mode allows.
- [ ] Update dashboard and preflight are validated as foundation workflows.
- [ ] Backup dashboard, verification, retention, and restore-plan guidance are validated as foundation workflows.
- [ ] Branding routes load and safe staging assets render.

## Readiness Commands

- [ ] `php artisan staging:check-readiness`
- [ ] `php artisan deployment:check-readiness`
- [ ] `php artisan performance:audit`
- [ ] `APP_ENV=production APP_DEBUG=false php artisan security:audit`
- [ ] `php artisan release:check-readiness`
- [ ] `php artisan marketplace:validate-package`

## Safety

- [ ] `.env` is not publicly accessible.
- [ ] Logs and backups are not under public web root.
- [ ] `public/build.zip` is not used for deployment packaging.
- [ ] No real user emails receive staging messages.
- [ ] No production payment keys are configured.
- [ ] Manual backup exists before migrations or update preflight.

## Honest Scope

- [ ] Full billing/payment workflow remains planned.
- [ ] Real update application remains planned.
- [ ] Automated restore remains planned.
- [ ] Marketplace ZIP generation remains planned.
- [ ] Full parent/student portals remain planned where incomplete.
