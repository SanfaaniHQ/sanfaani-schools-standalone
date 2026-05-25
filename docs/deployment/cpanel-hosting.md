# cPanel Hosting

cPanel deployments follow the same shared-hosting rules as Namecheap but may vary by provider.

## Checklist

- Confirm PHP version meets the application requirement.
- Confirm required PHP extensions are enabled.
- Create the database and user in cPanel.
- Configure `.env`.
- Set document root to `public/`.
- Ensure `storage` and `bootstrap/cache` are writable.
- Configure cron or queue fallback only when the host supports it.

## Installer Use

The installer foundation checks requirements, permissions, database connection, migration readiness, school setup, admin setup, SMTP placeholder data, and installation lock state.

It does not execute destructive migrations or run blind seeders.

## Planned

Marketplace packaging and one-click buyer installation are planned.
