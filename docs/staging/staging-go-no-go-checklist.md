# Staging Go/No-Go Checklist

Use this checklist for final staging release candidate approval.

## Go Criteria

- [ ] `php artisan test` passes.
- [ ] `php artisan route:list` passes.
- [ ] `php artisan staging:check-readiness` exits successfully.
- [ ] `php artisan deployment:check-readiness` has no failures.
- [ ] `php artisan performance:audit` has no failures.
- [ ] `php artisan security:audit` passes with production-style overrides.
- [ ] `php artisan release:check-readiness` has no failures.
- [ ] `php artisan marketplace:validate-package` passes and creates no ZIP.
- [ ] `git diff --check` passes.
- [ ] Protected files are not staged.
- [ ] Staging mode matrix is reviewed.
- [ ] Smoke test template is filled for the target mode.
- [ ] Known limitations are accepted.

## No-Go Criteria

- [ ] Any validation command fails.
- [ ] `public/build.zip` is staged.
- [ ] `database/migrations/2026_05_01_173857_create_result_publications_table.php` is staged.
- [ ] Staging sales/support copy claims full billing/payment automation.
- [ ] Staging copy presents real update application as shipped.
- [ ] Staging copy presents automated restore as shipped.
- [ ] Staging copy claims marketplace ZIP generation is complete.
- [ ] Staging copy claims full parent/student portals are complete where workflows are incomplete.
- [ ] Staging copy claims white-label domain provisioning or reseller tooling is complete.
- [ ] A production secret, backup, log, or generated archive is staged.

## Approval Record

- Decision:
- Approver:
- Date:
- Accepted risks:
- Required follow-up before production:
