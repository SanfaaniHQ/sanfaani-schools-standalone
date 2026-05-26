# Staging Mode Switching Guide

Use this guide when switching a staging environment between SaaS, single-school, managed, white-label, demo, trial, or marketplace-buyer validation. Mode switching changes configuration only; it does not add runtime features.

## General Sequence

1. Record the current mode and commit in the signoff report.
2. Back up the staging database if the environment contains data.
3. Update `.env` values for the target mode.
4. Clear cached config.
5. Re-run the mode checklist and readiness commands.
6. Record warnings and failures separately.

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan staging:check-readiness
php artisan deployment:check-readiness
```

## SaaS Mode

```dotenv
SANFAANI_DEPLOYMENT_MODE=saas
SANFAANI_LICENSE_MODE=subscription
SANFAANI_INSTALLER_ENABLED=false
SANFAANI_INSTALLED=true
SANFAANI_DEMO_ENABLED=true
SANFAANI_MARKETING_AUTOMATION_ENABLED=true
```

Expected focus: platform schools, subscriptions foundation, demo, onboarding, marketing, updates, backups, branding, performance, and security diagnostics.

## Single-School Mode

```dotenv
SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_LICENSE_MODE=annual
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false
SANFAANI_DEMO_ENABLED=false
SANFAANI_MARKETING_AUTOMATION_ENABLED=false
```

Expected focus: installer, license activation, school operations, local settings, backups, updates, branding, performance, and security diagnostics.

## Managed Mode

```dotenv
SANFAANI_DEPLOYMENT_MODE=managed
SANFAANI_LICENSE_MODE=managed_contract
SANFAANI_INSTALLER_ENABLED=false
SANFAANI_INSTALLED=true
SANFAANI_BACKUPS_ENABLED=true
SANFAANI_UPDATES_ENABLED=true
```

Expected focus: managed support, managed backups guidance, guided updates, branding, performance, security, and client handoff checks.

## White-Label Validation

```dotenv
SANFAANI_DEPLOYMENT_MODE=managed
SANFAANI_LICENSE_MODE=white_label
SANFAANI_WHITE_LABEL_ENABLED=true
SANFAANI_BRAND_MODE=white_label
```

Expected focus: branding routes, logo, favicon, public page, email footer, and report footer. White-label domain provisioning, full theme builder, and reseller tooling remain planned.

## Demo Or Trial Validation

```dotenv
SANFAANI_DEPLOYMENT_MODE=saas
SANFAANI_LICENSE_MODE=trial
SANFAANI_DEMO_ENABLED=true
SANFAANI_ONBOARDING_TRIAL_ENABLED=true
SANFAANI_MARKETING_AUTOMATION_ENABLED=true
```

Expected focus: demo request, demo credentials, onboarding, sales tasks, unsubscribe, and staging-only emails. Trial-to-paid billing conversion remains planned.

## Marketplace Buyer Validation

```dotenv
SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_LICENSE_MODE=annual
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false
SANFAANI_MARKETING_AUTOMATION_ENABLED=false
```

Expected focus: buyer installation path, license activation, package validation, safe env template, and single-school readiness. Marketplace ZIP generation and marketplace API integration remain planned.

## Required Recheck After Any Switch

```bash
php artisan route:list
php artisan staging:check-readiness
php artisan deployment:check-readiness
php artisan performance:audit
APP_ENV=production APP_DEBUG=false php artisan security:audit
php artisan release:check-readiness
php artisan marketplace:validate-package
```
