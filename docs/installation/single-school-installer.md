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
- Folder permission check.
- Database connection check.
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

## Not Implemented Yet

- License activation is separate and foundation-level.
- Update manager is planned.
- Backup manager is planned.
- Marketplace package installer automation is planned.
