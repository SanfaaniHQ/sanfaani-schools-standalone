# Release Workflow

## Purpose

Release notes explain what changed for customers, support, and deployment teams.

## Required Sections

- Summary.
- Added.
- Changed.
- Fixed.
- Security.
- Deployment notes.
- Migration notes.
- Known limitations.

## Commercial Notes

When a release touches deployment modes, feature flags, installer, licensing, demo, onboarding, marketing, tenant isolation, updates, backups, marketplace, or white-label behavior, update the matching docs in the same PR.

## Planned Systems

If a release only adds a gate or placeholder for a future system, state that the system is planned and not complete.

## Required Validation Before Notes Are Published

- `php artisan test`
- `php artisan route:list`
- `php artisan deployment:check-readiness`
- `php artisan performance:audit`
- `php artisan security:audit`
- `php artisan marketplace:validate-package`
- `git diff --check`

Release notes must mention update preflight, backup readiness, installer, licensing, branding, tenant isolation, demo, onboarding, marketing, marketplace, white-label, and managed-client impacts when those areas are touched.
