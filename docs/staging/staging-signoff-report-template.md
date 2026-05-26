# Staging Signoff Report Template

Use this template to record the outcome of a staging deployment execution. Record actual evidence; do not pre-fill results.

## Deployment Context

- Repository:
- Branch or tag:
- Commit:
- Staging URL:
- Deployment mode:
- License mode:
- Execution owner:
- Verification owner:
- Signoff owner:
- Execution window:

## Files And Artifacts

- Source commit reviewed:
- `public/build` asset source:
- `public/build.zip` excluded:
- Protected migration untouched:
- `.env` stored outside Git:
- Backup location recorded privately:

## Command Results

| Command | Exit result | Warnings | Evidence location |
| --- | --- | --- | --- |
| `composer install --no-dev --optimize-autoloader` |  |  |  |
| `npm ci` |  |  |  |
| `npm run build` |  |  |  |
| `php artisan migrate --pretend` |  |  |  |
| `php artisan migrate --force` |  |  |  |
| `php artisan test` |  |  |  |
| `php artisan route:list` |  |  |  |
| `php artisan staging:check-readiness` |  |  |  |
| `php artisan deployment:check-readiness` |  |  |  |
| `php artisan performance:audit` |  |  |  |
| `php artisan security:audit` with production-style env |  |  |  |
| `php artisan release:check-readiness` |  |  |  |
| `php artisan marketplace:validate-package` |  |  |  |
| `git diff --check` |  |  |  |

## Checklist Results

- Deployment execution checklist:
- Server command sequence:
- Env example used:
- Database migration checklist:
- Seed/demo data checklist:
- Mail/SMTP checklist:
- Queue/cron checklist:
- Storage permissions checklist:
- Domain/SSL checklist:
- First login checklist:
- Post-deploy verification:
- Mode checklist:

## Known Limitations

- Full billing automation remains planned.
- Real update application remains planned.
- Automated restore remains planned.
- Marketplace ZIP generation remains planned.
- White-label domain provisioning, full theme builder, and reseller tooling remain planned.

## Decision

- Decision:
- Accepted risks:
- Blockers:
- Required follow-up:
- Approved by:
- Approval date:
