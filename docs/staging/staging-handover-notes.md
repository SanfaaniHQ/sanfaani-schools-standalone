# Staging Handover Notes

These notes define what the next reviewer or release owner should receive before staging validation begins.

## Handover Package

- Branch: `feature/v7-cbt-localization-hardening`
- Expected latest commit: `docs(roadmap): add final commercialization roadmap and acceptance checklist`
- Staging docs: `docs/staging/`
- Final roadmap docs: `docs/roadmap/`
- Required config: `config/staging.php`
- Read-only command: `php artisan staging:check-readiness`

## Protected Files

Do not stage, modify, package, or deploy these files as part of staging validation:

- `database/migrations/2026_05_01_173857_create_result_publications_table.php`
- `public/build.zip`

## Validation Owner Notes

- Run the full validation set before marking the release candidate ready.
- Run `php artisan security:audit` with production-style overrides when local `.env` uses local/debug values.
- Use the staging environment matrix to choose mode-specific smoke tests.
- Record results in the smoke test template.
- Keep generated archives, backups, logs, database dumps, private storage, and secrets out of Git.

## Required Commands

```bash
php artisan test
php artisan route:list
php artisan staging:check-readiness
php artisan deployment:check-readiness
php artisan performance:audit
php artisan security:audit
php artisan release:check-readiness
php artisan marketplace:validate-package
git diff --check
```

## Launch Boundary

The staging release candidate validates foundations. It does not mean the following are complete:

- Full billing/payment automation.
- Real update application.
- Automated restore.
- Marketplace ZIP generation.
- Full parent/student portals.
- White-label domain provisioning.
- Reseller tooling.

## Handover Decision

- Ready for staging validation:
- Blockers:
- Accepted risks:
- Owner:
- Date:
