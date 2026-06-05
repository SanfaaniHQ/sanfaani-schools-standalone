# Standalone Buyer Installation Flow

This flow is for standalone single-school packages. SaaS buyers do not get code; they use hosted access at `sanfaanischools.online`. Standalone buyers receive a private single-school package, while marketplace buyers can buy the package plus optional done-for-you installation support.

## Before Upload

- Choose the correct package profile: `technical` for technical buyers, `cpanel_ready` for shared-hosting buyers, or `managed_handover` for Sanfaani handover docs.
- Confirm the package does not include `.env`, real secrets, `.git`, `node_modules`, logs, caches, sessions, compiled framework views, backups, SQL dumps, or `public/build.zip`.
- For cPanel, prefer a package that includes `vendor/` and `public/build/`.

## Hosting Setup

1. Upload the standalone package to the hosting account or server.
2. Extract the package outside the public web root where possible.
3. Point the document root to `/public`.
4. Create the database and database user.
5. Configure `.env` from a safe template.
6. Open the installer and complete the standalone setup.
7. Set `SANFAANI_INSTALLED=true` after installation is complete.

The installer is for standalone mode. Non-technical buyers should buy done-for-you installation rather than editing server paths, Composer dependencies, npm builds, or database settings alone.

## Recommended Standalone Env

```env
SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_LICENSE_MODE=annual
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false
```
