# Staging Smoke Test Results Template

Copy this template into the staging release record. Do not commit secrets, passwords, private URLs, database dumps, logs, backups, or generated archives.

## Release Candidate

- Branch:
- Commit:
- Date:
- Reviewer:
- Target mode:
- Target URL:

## Environment

- `APP_ENV`:
- `APP_DEBUG`:
- `SANFAANI_DEPLOYMENT_MODE`:
- `SANFAANI_LICENSE_MODE`:
- `SANFAANI_BRAND_MODE`:
- `SANFAANI_INSTALLER_ENABLED`:
- `SANFAANI_DEMO_ENABLED`:
- `SANFAANI_ONBOARDING_ENABLED`:
- `SANFAANI_MARKETING_AUTOMATION_ENABLED`:
- `SANFAANI_UPDATES_ENABLED`:
- `SANFAANI_BACKUPS_ENABLED`:

## Command Results

| Command | Result | Notes |
| --- | --- | --- |
| `php artisan staging:check-readiness` |  |  |
| `php artisan test` |  |  |
| `php artisan route:list` |  |  |
| `php artisan deployment:check-readiness` |  |  |
| `php artisan performance:audit` |  |  |
| `php artisan security:audit` |  |  |
| `php artisan release:check-readiness` |  |  |
| `php artisan marketplace:validate-package` |  |  |
| `git diff --check` |  |  |

## Mode Behavior

| Area | Expected | Actual | Pass/Fail | Notes |
| --- | --- | --- | --- | --- |
| Required env values |  |  |  |  |
| Enabled features |  |  |  |  |
| Hidden features |  |  |  |  |
| Admin routes |  |  |  |  |
| School routes |  |  |  |  |
| Onboarding/demo/licensing behavior |  |  |  |  |
| Backup/update behavior |  |  |  |  |
| Branding behavior |  |  |  |  |
| Known limitations accepted |  |  |  |  |

## Honest Scope Confirmation

- [ ] Full billing/payment workflow remains planned.
- [ ] Real update application remains planned.
- [ ] Automated restore remains planned.
- [ ] Marketplace ZIP generation remains planned.
- [ ] Full parent/student portals remain planned where incomplete.
- [ ] White-label domain provisioning and reseller tooling remain planned.

## Decision

- Go/no-go:
- Required follow-up:
- Owner:
- Approval:
