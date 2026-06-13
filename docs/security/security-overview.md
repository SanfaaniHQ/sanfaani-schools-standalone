# Security Overview

Security work currently focuses on tenant isolation, authorization boundaries, secret handling, and safe commercial gating.

## Existing Protections

- School context middleware.
- Active role middleware.
- School feature authorization.
- Feature and deployment behavior middleware.
- License validation middleware for restricted commercial routes.
- Installer reinstall lock.
- Demo credential expiry and encrypted temporary passwords.
- Marketing unsubscribe and suppression checks.

## Tenant Boundary

School-owned data must remain scoped to `school_id`. Platform-level data is global only where explicitly documented.

## Secret Rules

Never commit real `.env` files, database dumps, backups, logs, SMTP passwords, payment keys, license keys, or app keys.

## Further Work

Backup and guided update foundations now exist for metadata, verification, preflight, and support-safe diagnostics. Real update application, external update downloads, automated restore execution, online license-server enforcement, and dependency vulnerability remediation remain separate future security work.
