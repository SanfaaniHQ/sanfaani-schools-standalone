# Temporary License Disablement

License enforcement is temporarily disabled for Sanfaani Schools Standalone. Fresh and existing standalone installations operate without activation, a license key, remote validation, renewal checks, expiry checks, or a signing key.

The canonical switch is:

```dotenv
SANFAANI_LICENSE_VALIDATION_ENABLED=false
```

Its source-code default is `false` through `config('sanfaani.license_validation_enabled')`, so a fresh installation does not depend on a manually edited `.env` value to remain usable. `SANFAANI_LICENSE_MODE=annual` remains compatible but has no feature or entitlement effect while validation is disabled. An empty `SANFAANI_LICENSE_SIGNING_KEY` is valid.

## Dormant architecture

License migrations, tables, models, services, activation views, historical records, audit data, and architecture tests remain in the repository. With enforcement disabled:

- license middleware passes the request through before querying license records;
- activation and validation routes are not registered;
- license entitlements and license-mode lists do not restrict product features;
- renewal listeners, reminders, and renewal emails are inactive;
- installer, health, readiness, status, update, and backup UI omit license checks.

Authentication, authorization, role permissions, school scoping, deployment behavior, demo-user restrictions, and other security controls are unchanged.

## Existing installation deployment

Deploy the updated source, keep existing license tables and records, and rebuild Laravel caches:

```bash
cd /home/swifarpx/portal.sanfaani.net
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

No database migration is required for this change. Existing license records are ignored for access decisions and are preserved for a future restoration.

## Restoring enforcement later

Restoration is intentionally centralized. Set `SANFAANI_LICENSE_VALIDATION_ENABLED=true`, clear/rebuild Laravel config and route caches, and restore the conditionally hidden customer license UI where required. Before production restoration, run the licensing, middleware, entitlement, update, backup, installer, scheduler, and health regression suites and confirm a signing/verification key strategy.
