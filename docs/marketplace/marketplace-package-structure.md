# Marketplace Package Structure

The marketplace package should be a clean Laravel application bundle with buyer docs and safe environment templates. The package must not contain local data, credentials, backups, logs, generated archives, or dependency folders.

## Root Structure

- `app/`: application code.
- `bootstrap/`: Laravel bootstrap files.
- `config/`: configuration, including deployment, licensing, updates, backups, and packaging manifests.
- `database/`: migrations, seeders, and safe schema assets only.
- `docs/`: buyer, installation, licensing, update, backup, support, and marketplace documentation.
- `public/`: public assets required by the application, excluding unsafe generated archives.
- `resources/`: Blade, CSS, JS, language, and frontend source assets.
- `routes/`: web, installer, console, and auth route files.
- `tests/`: included for developer/buyer verification packages when allowed by marketplace rules.
- `artisan`, `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `vite.config.js`.
- `.env.example` and `.env.marketplace.example`.
- `README.md`.

## Package Modes

- Marketplace single-school: default buyer package with installer and annual license mode.
- Direct single-school: direct sales package with support-assisted onboarding.
- Managed client: operational handover package for Sanfaani-managed clients.
- White-label: brandable package where license terms allow.
- Demo sales: controlled demo/trial package with demo enabled only in safe sales environments.

## Build Boundary

The `marketplace:validate-package` command validates readiness without creating archives. The `marketplace:build-package` command creates standalone marketplace package ZIPs and manifest JSON files under `storage/app/marketplace-packages/`.

Package ZIPs must never include `.env`, real secrets, `.git`, `node_modules`, logs, caches, sessions, compiled framework views, backups, or `public/build.zip`. The `cpanel_ready` profile may include `vendor/` and `public/build/` when present; the `technical` profile excludes `vendor/` and lets the buyer run Composer and npm.
