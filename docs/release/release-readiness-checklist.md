# Release Readiness Checklist

- Confirm release branch and version name.
- Confirm changelog entry.
- Confirm release notes.
- Confirm no unintended dirty files.
- Confirm protected dirty files are excluded unless intentionally resolved.
- Confirm `.env`, secrets, logs, caches, vendor, node_modules, and public archives are excluded from package plans.
- Run focused test filters.
- Run the full suite.
- Run `php artisan route:list`.
- Run `git diff --check`.
- Run `deployment:check-readiness`, `performance:audit`, `security:audit`, and `marketplace:validate-package`.
- Confirm installer, license, demo, onboarding, marketing, update, backup, branding, and tenant isolation expectations.
- Confirm backup-before-release and rollback validation.
- Record final go/no-go approval.
