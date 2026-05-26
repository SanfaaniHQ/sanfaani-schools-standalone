# Staging Database Migration Checklist

Use this checklist before running migrations in staging.

## Before Migration

- [ ] Confirm the target database is staging, not production.
- [ ] Confirm the staging database name and host privately.
- [ ] Confirm a fresh manual backup exists if the database contains data.
- [ ] Confirm backup location and restore owner are recorded outside Git.
- [ ] Confirm no production data is used unless sanitized and approved.
- [ ] Confirm no existing migration files are edited during deployment.
- [ ] Confirm `database/migrations/2026_05_01_173857_create_result_publications_table.php` is untouched.

## Preview

```bash
php artisan migrate:status
php artisan migrate --pretend
```

Review the preview for unexpected destructive operations before continuing.

## Execute

```bash
php artisan migrate --force
```

Run only after backup approval and deployment owner approval.

## After Migration

- [ ] Run `php artisan migrate:status`.
- [ ] Run `php artisan deployment:check-readiness`.
- [ ] Run the selected mode checklist.
- [ ] Verify login, dashboard, result, CBT, support, and communication paths.
- [ ] Record warnings and follow-up work in the signoff report.

## Rollback Boundary

Laravel migration rollback can be destructive. Use it only with explicit owner approval and a verified backup plan. Automated restore remains planned.
