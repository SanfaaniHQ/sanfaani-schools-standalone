# cPanel Ready Package Guide

The `cpanel_ready` package is for shared-hosting and non-technical buyers who need a package that is closer to upload-and-configure. SaaS buyers do not get code; they use hosted access at `sanfaanischools.online`. Standalone buyers get a single-school package.

## What It Should Contain

- Application source code and buyer documentation.
- `vendor/` when present, so the buyer does not need to run Composer on cPanel.
- `public/build/` when present, so frontend assets are already built.
- Safe environment examples such as `.env.example` and `.env.marketplace.example`.

## What It Must Not Contain

- `.env` or real secrets.
- `.git`.
- `node_modules`.
- Logs, caches, sessions, compiled framework views, backups, SQL dumps, or `public/build.zip`.

Run:

```bash
php artisan marketplace:build-package --profile=cpanel_ready --dry-run
php artisan marketplace:build-package --profile=cpanel_ready
```

The dry run warns if `public/build/manifest.json` is missing or if `vendor/` is missing.

## Hosting Notes

The document root must point to `/public`. If the buyer cannot set that on shared hosting, they should buy done-for-you installation support so Sanfaani can configure the account safely.

The installer is for standalone mode. Recommended env guidance:

```env
SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_LICENSE_MODE=annual
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false
```
