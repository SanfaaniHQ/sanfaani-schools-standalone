# Marketplace Package Checklist

Use this checklist before creating or sharing a cPanel-ready marketplace ZIP.

## Package Profile

Use the `cpanel_ready` profile for upload-and-configure cPanel buyers:

```bash
php artisan marketplace:build-package --profile=cpanel_ready --dry-run
php artisan marketplace:build-package --profile=cpanel_ready
```

Keep the sibling manifest beside the ZIP. Inspect the generated archive before release:

```bash
php artisan marketplace:inspect-package storage/app/marketplace-packages/sanfaani-schools-standalone-v1.0.0-cpanel.zip
```

## Include

The cPanel-ready ZIP should include:

- `app/`
- `bootstrap/`
- `bootstrap/cache/.gitignore`
- `config/`
- `database/`
- `docs/` needed for buyer installation and support
- `lang/`
- `public/`
- `public/build/`
- `resources/`
- `routes/`
- `storage/app/.gitignore`
- `storage/app/public/.gitignore`
- `storage/framework/cache/.gitignore`
- `storage/framework/sessions/.gitignore`
- `storage/framework/views/.gitignore`
- `vendor/`
- `artisan`
- `composer.json`
- `composer.lock`
- `package.json`
- `package-lock.json`
- `postcss.config.js`
- `tailwind.config.js`
- `vite.config.js`
- `.env.example`
- `.env.marketplace.example`
- `CHANGELOG.md`
- `DEPLOYMENT.md`
- `README.md`

## Exclude

The cPanel-ready ZIP must exclude:

- `.git/`
- `.env`
- `.env.local`
- `.env.backup`
- `.env.production`
- `.env.*.local`
- `node_modules/`
- `tests/` unless preparing a technical/developer verification package
- `phpunit.xml` unless preparing a technical/developer verification package
- `storage/logs/*`
- `storage/framework/cache/*` except the safe placeholder
- `storage/framework/sessions/*` except the safe placeholder
- `storage/framework/views/*` except the safe placeholder
- `storage/app/backups/`
- `storage/app/private/`
- `storage/app/database/`
- `storage/app/updates/`
- `storage/app/marketplace-packages/`
- local SQLite/database files
- local cache files
- npm debug logs
- Composer cache
- IDE files
- OS metadata files
- local backup files
- SQL dumps
- generated archives
- `public/build.zip` unless explicitly approved in a separate release task

## Pre-Package Checks

Run:

```bash
composer validate
npm run build
php artisan route:list
php artisan test --filter=Installer
php artisan test --filter=License
php artisan test --filter=Marketplace
php artisan test --filter=Deployment
php artisan test --filter=Standalone
git diff --check
git diff --name-only -- public/build.zip database/migrations/2026_05_01_173857_create_result_publications_table.php .env .env.local
```

Do not package from a tree where protected files are dirty.

## Archive Inspection

Confirm:

- `.env` is absent.
- `.git` is absent.
- `node_modules` is absent.
- `public/build.zip` is absent.
- `vendor/` is present for cPanel-ready packages.
- `public/build/manifest.json` is present.
- Installer docs are present.
- License docs are present.
- cPanel deployment docs are present.
- The sibling manifest lists the selected profile and output path.

## Buyer Handoff

Tell the buyer or installer:

- Create the cPanel database manually. Normal buyer-selected names such as `swifarpx_fazportal`, `client_school_portal`, or `portal_db` are supported.
- Create `.env` manually from the safe template.
- Point the domain to Laravel `public`.
- Configure SMTP manually.
- Configure cron manually.
- Open `https://portal.example.com/install` and complete `/install`.
- Before setup, `/` should enter the installer flow and `/login` should not encourage login. After setup, `/` should point to login or the portal flow.
- Do not manually create `storage/app/installed.lock`; completing the installer creates the school, first admin, and lock together.
- Activate the license after first admin login from `Admin -> License`.
- Shared-hosting-safe migrations are supported for the standalone sync tables.
- Seller-side license generation requires `SANFAANI_LICENSE_SIGNING_KEY`; customer portal activation does not require the generator key.
- Keep backups outside public web access.
- Never delete `storage/app/installed.lock` on a live install without support approval.
