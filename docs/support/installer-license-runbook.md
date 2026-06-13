# Installer And License Runbook

## Purpose

Use this runbook when a standalone buyer, local owner, or managed client needs help with `/install`, installer lock state, local license activation, entitlement visibility, or license diagnostics.

## Access

The installer is public only while the deployment is in an allowed pre-install state. License and installer diagnostics belong to Super Admin, local owner, or approved Sanfaani support. School Admins do not manage installer or license pages unless they also hold the local owner role.

## Normal Workflow

1. Confirm the deployment is intended to be `single_school`.
2. Confirm hosting requirements, document root, PHP version, extensions, database, storage permissions, app key, mail intent, and scheduler setup.
3. Open `/install` only after hosting and `.env` preparation are complete.
4. Complete owner admin, school profile, SMTP intent, final review, and installation lock.
5. Review local license activation and diagnostics from the admin license pages.
6. Confirm redacted key display, local status, expiry, grace, domain matching, activation count, and entitlement visibility.

## Common Issues

- Document root points to the project root instead of `public`.
- Storage or `bootstrap/cache` is not writable.
- Database credentials are wrong or migrations are incomplete.
- Installer remains open after handoff.
- License key format is invalid or the local license record is missing.
- Buyer expects SaaS billing, online activation server behavior, or automatic remote deactivation.

## First Checks

- Confirm current URL, hosting provider, PHP version, and database driver.
- Confirm `/admin/standalone/status` does not expose secrets.
- Confirm installation lock and `SANFAANI_INSTALLED` state through safe diagnostics, not raw `.env` screenshots.
- Confirm local license status, entitlement count, and masked key output.
- Confirm audit entries exist for installer and license actions where the database is available.

## Safe Commands And UI Checks

```bash
php artisan test --filter=Installer
php artisan test --filter=License
php artisan route:list
php artisan standalone:status
php artisan deployment:check-readiness
```

Use hosting control panel screens for PHP extensions, document root, database, cron, and writable folders when shell access is unavailable.

## What Support Should Not Do

- Do not ask buyers to paste raw `.env`, database passwords, app keys, mail passwords, or license keys into normal chat.
- Do not remove installer locks or force reinstall on a live school without engineering approval and verified backup.
- Do not promise SaaS billing, payment gateway enforcement, online activation server, remote license server sync, or automatic deactivation.
- Do not run destructive setup or reset actions on a production installation.

## Escalation Points

Escalate when installer state is inconsistent, migrations fail unexpectedly, license status conflicts with diagnostics, license key storage may be exposed, the domain or deployment mode is wrong, or a buyer needs a non-standard public-folder mapping.

## Data And Privacy Warnings

Installer and license screens must show statuses and masked values only. Raw license keys, app keys, database credentials, SMTP passwords, provider tokens, and private server paths must stay out of screenshots and tickets.

## Backup And Security Reminders

Verify backup readiness before changing installer state, moving hosting accounts, touching production config, or reviewing update readiness.

## Related Docs

- [Installer And License Final Hardening](../standalone/installer-license-final-hardening.md)
- [Installer And License Flow](../standalone/installer-and-license-flow.md)
- [Standalone Installer User Guide](../installation/standalone-installer-user-guide.md)
- [Standalone Buyer Installation Flow](../installation/standalone-buyer-installation-flow.md)
- [License Activation](../licensing/license-activation.md)
