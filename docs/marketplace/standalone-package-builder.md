# Standalone Package Builder

The marketplace standalone package builder creates safe buyer-ready package artifacts for non-SaaS commercial formats. SaaS buyers use `sanfaanischools.online` and do not get code. Standalone buyers receive a private single-school package, and marketplace buyers can buy the package with optional installation support.

## Command

```bash
php artisan marketplace:build-package --profile=technical
php artisan marketplace:build-package --profile=cpanel_ready
php artisan marketplace:build-package --profile=managed_handover
php artisan marketplace:build-package --profile=technical --dry-run
```

The command writes ZIP packages and manifest JSON files under `storage/app/marketplace-packages/`. A dry run writes a manifest preview but does not create a ZIP.

## Profiles

- `technical`: includes source code, excludes `vendor`, excludes `node_modules`, and expects the buyer to run Composer and npm.
- `cpanel_ready`: includes source code, includes `vendor` when present, includes `public/build` when present, and excludes `node_modules`.
- `managed_handover`: includes docs and checklists for Sanfaani team/client handover without secrets.

## Safety Rules

Never include `.env`, real secrets, `.git`, `node_modules`, logs, caches, sessions, compiled framework views, backups, or `public/build.zip`. The builder fails if `.env` or `public/build.zip` would be included.

For `cpanel_ready`, run `composer install --no-dev` before packaging if the package should include `vendor`, and run `npm run build` before packaging if the package should include `public/build`. The command warns when `vendor/` or `public/build/manifest.json` is missing.

The standalone web server document root must point to `/public`. The installer is for standalone mode, not SaaS tenant onboarding or payment/billing automation.

## Recommended Standalone Env

```env
SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_LICENSE_MODE=annual
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false
```

## Validation Sequence

```bash
php artisan test --filter=MarketplacePackageBuilderTest
php artisan test --filter=MarketplacePackageValidationTest
php artisan marketplace:validate-package
php artisan marketplace:build-package --profile=technical --dry-run
php artisan marketplace:build-package --profile=cpanel_ready --dry-run
php artisan test
php artisan route:list
git diff --check
```
