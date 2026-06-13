# Installer And License Final Hardening

Stage 21 hardens the existing standalone installer and local licensing foundation. It does not rebuild the installer, create a SaaS billing platform, add payment enforcement, or contact an online license server.

## Installer Readiness

The installer remains a guided single-school setup flow. It now reports safer readiness checks for:

- PHP version and required extensions.
- Writable Laravel folders using relative paths only.
- Database connection and migration count without database usernames, passwords, or raw database names.
- `.env`, app key, queue, cache, session, filesystem, mail, scheduler, backup metadata, update package review, and production debug status.
- Final-review support diagnostics that show statuses only.

The installer does not run destructive resets, wipe data, force migrations, activate licenses, create backups, apply updates, or automate marketplace packaging.

## Safe Diagnostic Output

Diagnostics may be shared with Sanfaani support because they hide:

- app keys;
- database credentials;
- mail credentials;
- raw `.env` values;
- license keys;
- sync tokens and API keys;
- private backup paths;
- absolute server paths.

Installer audit logging is best-effort. If the database or `audit_logs` table is not available during pre-install checks, the installer continues instead of blocking setup.

## License Status And Activation

The license workflow remains local:

- submitted keys are validated for a basic safe format;
- raw keys are hashed before storage;
- status pages show masked/fingerprint-style key output only;
- local status, expiry, grace, domain matching, activation count, and entitlement counts are displayed;
- entitlement and module visibility are shown without changing existing feature gates;
- activation and validation actions are audit logged with safe metadata only.

Remote validation remains disabled unless a future safe client integration is implemented and tested. The existing `LicenseServerClient` foundation does not call a real external server.

## Access Rules

- Super Admin/local owner can manage license status and diagnostics through the admin area.
- Public users cannot access admin license pages.
- Teacher, accountant, result officer, student, and parent roles cannot manage installer or license pages.
- The installer remains public only while the deployment is in an allowed pre-install state and reinstall protection is not active.
- Local and test environments are not aggressively hard-blocked by missing licenses.

## Deferred Boundaries

Stage 21 does not implement:

- SaaS subscription billing;
- payment gateway enforcement;
- online activation server;
- customer billing portal;
- automatic remote deactivation;
- destructive reinstall/reset tools;
- secret management UI;
- remote entitlement delivery.

Support should use the admin license page, standalone status page, audit logs, and this document when handing off an installed school.
