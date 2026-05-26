# Staging Storage And Permissions Checklist

Use this checklist to validate writable paths and public asset access.

## Required Writable Paths

- [ ] `storage`
- [ ] `storage/app`
- [ ] `storage/app/public`
- [ ] `storage/framework/cache`
- [ ] `storage/framework/sessions`
- [ ] `storage/framework/views`
- [ ] `storage/logs`
- [ ] `bootstrap/cache`

## Commands

```bash
php artisan storage:link
php artisan deployment:check-readiness
php artisan performance:audit
```

Use host-specific permission commands only after the staging owner approves them.

## Public Exposure Checks

- [ ] `.env` is not reachable in the browser.
- [ ] `storage/logs` is not reachable in the browser.
- [ ] `storage/framework` is not reachable in the browser.
- [ ] Backups and database dumps are not under public web root.
- [ ] `vendor` and `node_modules` are not under public web root.
- [ ] `public/build` assets load.
- [ ] `public/build.zip` is not used or linked.

## Shared Hosting Notes

- If symlinks are blocked, use the documented storage-link workaround.
- Keep private files outside public access.
- Do not loosen permissions beyond the host requirement.
