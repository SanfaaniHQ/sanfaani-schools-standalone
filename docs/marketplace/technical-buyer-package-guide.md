# Technical Buyer Package Guide

The `technical` package is for developers, IT teams, VPS admins, and marketplace buyers who can run terminal commands. SaaS buyers do not get code; standalone buyers get a single-school package they install and maintain under the license terms.

## Package Shape

The technical package includes source code, docs, routes, config, migrations, frontend source assets, Composer files, npm files, and safe env examples. It excludes `vendor`, excludes `node_modules`, and can exclude built assets such as `public/build` because the buyer can run the build steps.

The buyer should run:

```bash
composer install --no-dev
npm install
npm run build
php artisan migrate --force
```

## Safety Rules

Never include `.env`, real secrets, `.git`, logs, caches, sessions, compiled framework views, backups, SQL dumps, or `public/build.zip`.

The document root must point to `/public`. The installer is for standalone mode, not SaaS access, SaaS tenant onboarding, or payment/billing automation.

Recommended standalone env:

```env
SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_LICENSE_MODE=annual
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false
```
