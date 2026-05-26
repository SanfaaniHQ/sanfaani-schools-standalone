# Staging Incident Rollback Checklist

Use this checklist when staging deployment validation fails or staging behavior is unsafe.

## Immediate Response

- [ ] Stop the staging rollout.
- [ ] Record the failing command, route, screen, or workflow.
- [ ] Preserve logs privately.
- [ ] Confirm no secrets, backups, logs, database dumps, or private files are public.
- [ ] Notify the staging owner and release owner.
- [ ] Do not run destructive fixes without approval.

## Rollback Decision

- [ ] Identify previous known-good staging commit.
- [ ] Confirm database state before rollback.
- [ ] Confirm manual backup availability.
- [ ] Confirm whether code rollback alone is sufficient.
- [ ] Confirm whether database rollback is required.
- [ ] Assign rollback owner and verifier.

## Code Rollback

- [ ] Re-deploy previous known-good code through the approved staging process.
- [ ] Do not use `public/build.zip` as a rollback artifact.
- [ ] Re-run `composer install --no-dev --optimize-autoloader` only if dependencies changed.
- [ ] Re-deploy reviewed `public/build` assets only.
- [ ] Re-run route and readiness checks.

## Database And Storage Recovery

- [ ] Restore database manually only from an approved backup and only after owner approval.
- [ ] Confirm storage files required by the staging workflow are present.
- [ ] Confirm backup files remain outside public web root.
- [ ] Confirm `.env` remains private and unchanged unless explicitly approved.

## Post-Rollback Validation

- [ ] Login works.
- [ ] Dashboard loads.
- [ ] Mode-specific checklist passes.
- [ ] `php artisan route:list` passes.
- [ ] `php artisan staging:check-readiness` passes.
- [ ] `php artisan deployment:check-readiness` has no failures.
- [ ] `php artisan performance:audit` has no failures.
- [ ] `APP_ENV=production APP_DEBUG=false php artisan security:audit` passes.
- [ ] `php artisan release:check-readiness` has no failures.
- [ ] `php artisan marketplace:validate-package` passes without creating a ZIP.

## Incident Record

- Incident:
- Start time:
- Detection source:
- Impact:
- Root cause:
- Rollback action:
- Validation result:
- Owner:
- Follow-up:

## Boundary

This checklist does not implement automated restore or automated rollback. Automated restore remains planned, and real update application remains planned.
