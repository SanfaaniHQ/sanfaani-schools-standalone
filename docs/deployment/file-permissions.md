# File Permissions

Permissions must allow Laravel to write runtime files without exposing secrets.

## Writable

- `storage`
- `bootstrap/cache`

## Typical Shared Hosting Values

- Directories: `0755`
- Files: `0644`
- Writable runtime folders may require owner/group adjustment from the hosting panel.

## VPS Ownership

Use a deploy user and web server group, for example:

```bash
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
```

## Avoid

- Project-wide `0777`.
- Public `.env`.
- Public logs.
- Public backups.
- Public private uploads.

## Verify

Run:

```bash
php artisan deployment:check-readiness
```

The command reports writable path status without changing permissions.
