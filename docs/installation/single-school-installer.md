# Single-School Installer

The installer foundation supports single-school deployments and managed deployments where explicitly allowed.

## Access Requirements

The installer is available only when:

- deployment mode allows installer access;
- `standalone_installer` feature is enabled;
- the app is not already installed;
- `storage/app/installed.lock` does not exist.

## Current Installer Stages

- Welcome.
- Requirements check.
- Folder permission check using relative path labels only.
- Database connection check without exposing usernames, passwords, or raw database names.
- Environment setup guidance.
- App key status.
- Migration readiness.
- Admin account setup.
- School profile setup.
- SMTP placeholder.
- Final review.
- Installation lock.

## Shared Hosting Notes

The installer is designed for cPanel and Namecheap-style hosting where shell access may be limited. It provides guidance and safe fallback notes instead of requiring destructive shell operations.

## Stage 21 Readiness Checks

The installer now checks or summarizes:

- PHP version and required/optional extensions.
- Writable storage, framework, log, and bootstrap cache folders.
- Public storage link readiness.
- Database connectivity and migration count.
- `.env` and app key presence without printing raw values.
- Queue, cache, session, filesystem, and mail configuration status.
- Scheduler/cron monitor status.
- Backup metadata and guided update package review readiness.
- Production debug warning.
- Support-safe final-review diagnostics.

The installer may write safe audit rows for checks and finalization when the database and `audit_logs` table are already available. Audit failures must not block installation.

## Not Implemented Yet

- License activation is separate and local foundation-level.
- Update manager review is separate and does not extract packages from the installer.
- Backup manager review is separate and does not restore from the installer.
- Marketplace package installer automation is planned.
- Destructive reinstall/reset tools are not implemented.
