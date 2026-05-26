# Staging Release Candidate Plan

This plan validates that Sanfaani Schools can move from commercialization foundation into staging. It is a validation and launch-preparation workflow only.

## Scope

- Confirm branch, commits, working tree state, and protected-file staging status.
- Confirm commercialization docs, staging docs, config files, feature flags, deployment modes, license modes, and route groups.
- Confirm required read-only commands are registered.
- Run tests, route listing, readiness commands, audits, marketplace validation, and whitespace checks.
- Validate staging behavior expectations for SaaS, `single_school`, managed, demo, trial, white_label, and marketplace buyer package paths.

## Out Of Scope

- No product features.
- No core business workflow changes.
- No migrations.
- No destructive database changes.
- No release ZIP generation.
- No deployment automation.
- No changes to `public/build.zip`.
- No changes to `database/migrations/2026_05_01_173857_create_result_publications_table.php`.

## Required Read-Only Commands

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

For local environments with `APP_ENV=local` or `APP_DEBUG=true`, run the security audit with production-style overrides:

```bash
APP_ENV=production APP_DEBUG=false php artisan security:audit
```

## Release Candidate Flow

1. Confirm branch is `feature/v7-cbt-localization-hardening`.
2. Confirm latest commit includes `docs(roadmap): add final commercialization roadmap and acceptance checklist`.
3. Confirm protected files are not staged.
4. Confirm final roadmap docs and staging docs exist.
5. Confirm required config files exist.
6. Confirm required feature flags, deployment modes, license modes, and route groups exist.
7. Run focused staging tests.
8. Run existing roadmap, UI, release, branding, security, performance, deployment, backup/update, marketplace, marketing, onboarding, demo, licensing, installer, tenant isolation, and feature/deployment tests.
9. Run full test suite.
10. Run route list and read-only readiness commands.
11. Complete smoke tests using `docs/staging/staging-smoke-test-results-template.md`.
12. Record go/no-go decision in `docs/staging/staging-go-no-go-checklist.md`.

## Honesty Boundary

The staging release candidate must not claim full billing/payment automation, marketplace ZIP generation, real update application, automated restore, full parent/student portal workflows, reseller tooling, or white-label domain provisioning. Those systems remain planned unless separately implemented and tested.

## Staging Exit Criteria

- Tests pass.
- Route list succeeds.
- `staging:check-readiness` exits successfully.
- Deployment readiness has no failures.
- Performance audit has no failures.
- Security audit passes with production-style overrides.
- Release readiness has no failures.
- Marketplace validation passes without creating a ZIP.
- `git diff --check` passes.
- Protected files are not staged.
- Known limitations are accepted.
