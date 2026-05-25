# Marketplace Buyer Deployment

This guide is for buyers installing Sanfaani Schools from a marketplace package.

## Buyer Responsibilities

The buyer must configure hosting, domain, database, SMTP, storage permissions, cron or queue settings, and license values.

## Recommended Flow

1. Read `docs/marketplace/buyer-installation-checklist.md`.
2. Upload the clean package.
3. Copy `.env.marketplace.example` to `.env`.
4. Configure database and SMTP.
5. Set `SANFAANI_DEPLOYMENT_MODE=single_school`.
6. Set `SANFAANI_LICENSE_MODE=annual` or the purchased mode.
7. Run the installer.
8. Activate or validate the license.
9. Create initial backup metadata and manual database export.
10. Run `php artisan deployment:check-readiness` if terminal access is available.

## Shared Hosting

Use Namecheap/cPanel guidance. If terminal access is unavailable, use the installer and hosting panel tools where possible.

## Not Included

- Hosting account creation.
- Domain setup.
- Database credentials.
- SMTP credentials.
- Production license value.
- Deployment automation.
- Automated restore.
