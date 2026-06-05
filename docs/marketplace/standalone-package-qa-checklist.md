# Standalone Package QA Checklist

Use this checklist before a standalone buyer package is released, shared with a marketplace buyer, or handed to Sanfaani support for done-for-you installation.

## Generate The cPanel-Ready Package

Prepare dependencies and assets on a safe build machine:

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan marketplace:build-package --profile=cpanel_ready --dry-run
php artisan marketplace:build-package --profile=cpanel_ready
```

The dry run writes a manifest preview only. The real build writes a ZIP and sibling manifest under `storage/app/marketplace-packages/`.

## Inspect Package Contents

Run the read-only inspector against the generated ZIP:

```bash
php artisan marketplace:inspect-package storage/app/marketplace-packages/package-name.zip
```

Also inspect the archive listing with a ZIP viewer or `unzip -l` on a local machine if available. Do not extract it into a live hosting account until the checks pass.

## Must Be Excluded

- `.env` must not be in the ZIP.
- `public/build.zip` must not be in the ZIP.
- `.git` must not be in the ZIP.
- `node_modules` must not be in the ZIP.
- Logs, caches, sessions, backups, SQL dumps, and private storage must not be in the ZIP.

## cPanel-Ready Expectations

- `vendor/` should be present when Composer dependencies were prepared for a non-technical or cPanel buyer.
- `public/build/manifest.json` should be present when frontend assets were built.
- `docs/installation/standalone-buyer-installation-flow.md` and `docs/installation/standalone-installer-acceptance-test.md` should be present.
- The sibling builder manifest should stay beside the ZIP for release tracking.

## Installer Acceptance

- The hosting document root must point to Laravel `/public`.
- The buyer or installer must create a MySQL or MariaDB database and database user before using `/install`.
- `.env` must be created from a safe template on the buyer hosting account, not shipped inside the ZIP.
- Confirm these standalone values:

```env
SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_LICENSE_MODE=annual
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false
```

- Open `/install` only after hosting, database credentials, file permissions, and app key are ready.

## Non-Technical Buyer Guidance

Non-technical buyers should upload the approved package only when their host or Sanfaani support is ready to configure it. They should not edit server paths, create ad hoc ZIPs, expose `.env`, change database credentials by guesswork, run migrations on a live database without guidance, or assume cPanel setup is fully automatic.

Sanfaani done-for-you setup is the recommended option when the buyer cannot point the document root to `/public`, create the database safely, configure `.env`, verify `/install`, or complete handover checks.

## Release Validation

```bash
php artisan test --filter=MarketplacePackageInspectionTest
php artisan test --filter=MarketplacePackageBuilderTest
php artisan marketplace:build-package --profile=cpanel_ready --dry-run
php artisan test
php artisan route:list
git diff --check
```
