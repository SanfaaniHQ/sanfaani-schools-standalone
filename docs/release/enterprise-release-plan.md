# Enterprise Release Plan

This plan defines the safe release workflow for SaaS, single-school, managed client, white-label, marketplace, demo, and trial releases.

## Branches And Naming

- Feature branches: `feature/<scope>-<description>`.
- Release branches: `release/vX.Y.Z`.
- Hotfix branches: `hotfix/vX.Y.Z-description`.
- Managed client branches: `managed/<client>/<version>`.

## Versioning Format

Use `vMAJOR.MINOR.PATCH` for stable releases. Pre-release formats may use `vMAJOR.MINOR.PATCH-beta.N`, `vMAJOR.MINOR.PATCH-rc.N`, `vMAJOR.MINOR.PATCH-hotfix.N`, or `vMAJOR.MINOR.PATCH-security.N`.

## Commit Conventions

Use conventional prefixes such as `feat`, `fix`, `docs`, `test`, `perf`, `security`, and `chore`.

## Required Validation

- Run all focused test filters in `config/release.php`.
- Run `php artisan test`.
- Run `php artisan route:list`.
- Run `php artisan deployment:check-readiness`.
- Run `php artisan performance:audit`.
- Run `php artisan security:audit`.
- Run `php artisan marketplace:validate-package`.
- Run `git diff --check`.

## Go/No-Go

Release only when tests pass, docs are current, protected dirty files are excluded, backups are ready, update preflight expectations are documented, rollback notes are reviewed, and known risks are accepted.
