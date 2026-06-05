# Standalone Installer Acceptance Test

Use this test after the package QA checklist passes and before a buyer handover is marked complete.

## Preconditions

- The uploaded package was inspected with `php artisan marketplace:inspect-package`.
- The package does not contain `.env`, `public/build.zip`, `.git`, or `node_modules`.
- The domain document root points to Laravel `/public`.
- A MySQL or MariaDB database and database user exist.
- Database credentials are ready: host, port, database name, username, and password.
- `.env` was created on the hosting account from `.env.marketplace.example` or another safe template.
- The standalone mode values are set:

```env
SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_LICENSE_MODE=annual
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false
```

## Route Acceptance

Open `/install` through the buyer domain. The URL should be served from `/public`; if the browser exposes parent folders or source files, stop and fix the document root.

Expected flow:

1. `/install`
2. `/install/requirements`
3. `/install/permissions`
4. `/install/database`
5. `/install/environment`
6. `/install/app-key`
7. `/install/migrations`
8. `/install/admin`
9. `/install/school`
10. `/install/smtp`
11. `/install/review`
12. `/install/complete`

## Database Acceptance

- The database screen must use the standalone `.env` values.
- The database user must belong to this school installation only.
- Pending migrations must be reviewed before execution.
- Do not run destructive database commands against real buyer data.
- If the host does not allow CLI migration commands, document the host-approved migration path before continuing.

## Completion Acceptance

Before handover:

- Admin owner login has been created.
- School name, slug, contact details, and SMTP intent have been reviewed.
- Installation lock is present after completion.
- `/install` is no longer reusable after completion.
- License activation or validation is handled through the standalone annual license process, not SaaS subscription setup.

## Done-For-You Option

Sanfaani done-for-you setup is appropriate when the buyer cannot configure `/public`, database credentials, `.env`, permissions, app key, SMTP, or the `/install` flow. Support should describe this as a guided service, not fully automatic hosting setup.

## Non-Technical Buyer Guidance

Non-technical buyers should follow the checklist, provide hosting access only through approved channels, and wait for support when unsure. They should not expose `.env`, move Laravel folders into public web access, edit credentials by guesswork, run migrations repeatedly, or delete installation locks.
