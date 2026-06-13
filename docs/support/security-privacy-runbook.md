# Security And Privacy Runbook

## Purpose

Use this runbook for support questions involving tenant isolation, secret handling, logs, screenshots, exports, backups, update packages, public access, and privacy-sensitive workflows.

## Access

Support staff, Super Admins, deployment engineers, and local owners can use this guide. School Admins can report school-scoped issues but should not receive secrets, private paths, backup files, update internals, or cross-school data.

## Normal Workflow

1. Confirm the affected environment, school, role, URL, and time window.
2. Identify the data class involved: student data, finance data, CBT data, admissions documents, credentials, backups, logs, license state, or update package metadata.
3. Collect sanitized evidence only.
4. Check the relevant module runbook and security docs.
5. Run read-only diagnostics where shell access exists.
6. Escalate immediately if cross-school access, secret exposure, public backup access, or data-loss risk is suspected.

## Common Issues

- Screenshots include secrets, private paths, student biodata, or payment data.
- Logs include raw provider payloads or stack traces.
- Public web root exposes private files.
- User sees data from another school.
- Support asks for raw `.env`, SQL dump, license key, or backup file through the wrong channel.
- Update or backup pages display too much internal detail.

## First Checks

- Confirm `APP_DEBUG` is off in production through safe diagnostics.
- Confirm document root points to `public` or follows a documented shared-hosting workaround.
- Confirm `.env`, backups, logs, private storage, and package files are not publicly reachable.
- Confirm school context and role middleware behavior.
- Confirm diagnostics redact app keys, database credentials, mail passwords, license keys, API tokens, sync tokens, private backup paths, and raw `.env` values.

## Safe Commands And UI Checks

```bash
php artisan route:list
php artisan standalone:status
php artisan deployment:check-readiness
php artisan performance:audit
php artisan security:audit
php artisan schedule:list
```

Use `/admin/standalone/status`, security diagnostics, deployment readiness, and hosting control panel file visibility checks.

## What Support Should Not Do

- Do not request raw `.env`, app keys, database passwords, SMTP passwords, license keys, payment keys, provider tokens, sync tokens, SQL dumps, full backups, or private package payloads in ordinary chat.
- Do not share one school's records with another school.
- Do not post production logs or screenshots publicly.
- Do not bypass authorization to troubleshoot faster.
- Do not continue update, restore, import, or maintenance work when backup readiness is unknown.

## Escalation Points

Escalate immediately for suspected cross-school data access, credential exposure, public backup/log exposure, protected-file changes, data-loss risk, suspicious admin activity, or diagnostics that reveal raw secrets.

## Data And Privacy Warnings

Use minimum necessary evidence. Redact names, admission numbers, guardian contact details, finance references, private notes, CBT private data, meeting passwords, backup paths, update internals, and provider payloads unless an approved secure process requires them.

## Backup And Security Reminders

Security incidents can require preserving evidence before cleanup. Coordinate with the owner and engineering before changing logs, backups, or affected data.

## Related Docs

- [Security Overview](../security/security-overview.md)
- [Logging And Secret Redaction](../security/logging-and-secret-redaction.md)
- [Tenant Isolation Audit](../security/tenant-isolation-audit.md)
- [Shared-Hosting Security Checklist](../security/shared-hosting-security-checklist.md)
- [Production Security Hardening](../security/production-security-hardening.md)
