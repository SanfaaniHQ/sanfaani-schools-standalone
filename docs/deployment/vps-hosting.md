# VPS Hosting

VPS deployments are recommended for SaaS and managed client environments where queue workers, scheduled tasks, and monitoring need reliable control.

## Suggested Stack

- PHP 8.3+.
- MySQL or MariaDB.
- Nginx or Apache.
- Supervisor or systemd for queue workers.
- Redis where available.
- Scheduled Laravel task runner.
- Separate backup and log retention processes.

## Deployment Steps

- Clone or upload the release.
- Install Composer dependencies.
- Build frontend assets.
- Configure `.env`.
- Run migrations.
- Configure storage link.
- Start queue workers.
- Cache config/routes/views for production.

## Current Limits

The product does not yet ship a full update manager or backup manager. VPS operators must use their own deployment and backup discipline until those systems are implemented.
