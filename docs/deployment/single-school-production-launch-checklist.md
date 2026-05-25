# Single-School Production Launch Checklist

Use this for direct single-school licensed deployments and marketplace buyers.

## Pre-Launch

- Hosting meets PHP/database requirements.
- Domain points to Laravel `public`.
- `.env` configured from safe template.
- `APP_ENV=production`.
- `APP_DEBUG=false`.
- `APP_KEY` generated.
- Database created and migrated.
- SMTP configured.
- Storage link or workaround verified.
- `storage` and `bootstrap/cache` writable.

## Commercial Foundation

- Deployment mode is `single_school`.
- License mode matches purchase.
- License validation enabled.
- Installer completed and locked.
- Updates enabled only as safe preflight foundation.
- Backups enabled as metadata/verification foundation.

## Launch Tests

- Admin login works.
- School profile complete.
- Students/classes/subjects/session/terms configured.
- Result entry and publishing tested.
- Public result checker tested.
- CBT flow tested if enabled.
- Email test passes.
- Backup metadata and manual database export created.

## After Launch

- Take a post-launch backup.
- Record deployed version and commit.
- Review logs privately.
- Confirm support contact and update process.
