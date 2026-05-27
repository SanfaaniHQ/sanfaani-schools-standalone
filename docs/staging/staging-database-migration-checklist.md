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
- [ ] Confirm shared-hosting compatibility fixes are present for cPanel/Namecheap 1000-byte index limits and short foreign key names.
- [ ] Confirm the domain document root points to Laravel `public` before browser verification.

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

Do not run `migrate:fresh` on demo or production. `EnvironmentGuard` blocks destructive commands by design.

If an empty demo migration fails halfway and leaves a partial table, drop only the failed partial table after owner approval. Do not drop the full database, and do not run destructive commands against production or client data.

## After Migration

- [ ] Run `php artisan migrate:status`.
- [ ] Run `php artisan deployment:check-readiness`.
- [ ] Run the selected mode checklist.
- [ ] Verify login, dashboard, result, CBT, support, and communication paths.
- [ ] Record warnings and follow-up work in the signoff report.
- [ ] Treat readiness warnings as advisory unless the command marks them as `fail`.

## Rollback Boundary

Laravel migration rollback can be destructive. Use it only with explicit owner approval and a verified backup plan. Automated restore remains planned.
