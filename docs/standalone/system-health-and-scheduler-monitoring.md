# Standalone System Health And Scheduler Monitoring

The standalone system health page is an owner-only support view for a private Sanfaani Schools installation. It helps the installation owner and Sanfaani support confirm that the Laravel app, database, storage, queue, scheduler, mail, license, installer, backup, update, and local-first sync foundations are ready for handover.

Open it from:

```text
/admin/standalone/status
```

Only authorized installation owners should access this page. It must show safe summaries only: statuses, counts, relative paths, and configured/missing flags.

## What The Page Checks

- PHP version, Laravel version, database connectivity, app environment, and debug mode.
- `storage/app`, `storage/framework/cache`, and `storage/logs` writability.
- Disk free space, `upload_max_filesize`, and `post_max_size`.
- Queue connection, failed jobs table presence, and failed jobs count when the table exists.
- Scheduler heartbeat freshness.
- Mail sender/transport readiness.
- `APP_URL` presence and HTTPS guidance.
- Installer lock/status.
- License status without exposing the license key.
- Backup readiness and recent verified backup status.
- Guided update readiness and the pre-update backup requirement.
- Standalone sync/offline status without exposing endpoint tokens.
- Safe output/redaction posture.

## Scheduler And Cron

The scheduler heartbeat is recorded by:

```bash
php artisan standalone:scheduler-heartbeat
```

The command is registered in Laravel's scheduler. Configure the host to run Laravel's scheduler every minute.

For cPanel, add a cron job similar to:

```bash
* * * * * /usr/local/bin/php /home/account/apps/sanfaani-schools/artisan schedule:run >> /dev/null 2>&1
```

For a VPS or local server, use the server PHP binary and app path:

```bash
* * * * * /usr/bin/php /var/www/sanfaani-schools/artisan schedule:run >> /dev/null 2>&1
```

If the health page says the scheduler heartbeat is stale, confirm the cron command, PHP path, app path, permissions, and cache configuration.

The heartbeat and scheduler mutex use file cache by default so they do not require a database migration before the installer is complete:

```env
SANFAANI_SCHEDULER_HEARTBEAT_CACHE_STORE=file
SANFAANI_SCHEDULER_MUTEX_CACHE_STORE=file
SANFAANI_SCHEDULER_STALE_AFTER_MINUTES=15
```

## Queue Worker Setup

Small shared-hosting installs can use:

```env
QUEUE_CONNECTION=sync
```

In production this may show a warning because larger installs should process queued work outside the web request. For cPanel/database queues, use a cron-triggered worker:

```bash
* * * * * /usr/local/bin/php /home/account/apps/sanfaani-schools/artisan queue:work --stop-when-empty --tries=3 --timeout=60 >> /dev/null 2>&1
```

For VPS or cloud servers, run `queue:work` under Supervisor, systemd, or the host's worker manager and restart workers after deployment.

## Backup And Update Interpretation

Backup health passes only when recent verified backup metadata satisfies the configured pre-update window. If update readiness warns about backups, create and verify a backup before uploading or applying any update package.

Guided updates remain manual-review tooling. The health page does not run migrations, extract packages, restore backups, or change files.

## Disk, Upload, Mail, And URL Warnings

Disk warnings mean available free space is below `SANFAANI_HEALTH_DISK_FREE_WARNING_MB`. Free space should cover logs, uploads, cache files, backups, and update packages.

Upload warnings mean PHP upload limits are below the shared-hosting guidance. Increase `upload_max_filesize` and `post_max_size` from cPanel MultiPHP INI Editor, `php.ini`, or the host control panel.

Mail warnings mean the sender or transport is incomplete, or a non-delivery transport is being used in production. Confirm SMTP host, port, encryption, username, password, sender address, and DNS requirements.

APP URL/SSL warnings mean `APP_URL` is missing, points to localhost in production, or uses HTTP in production. Use the final school portal domain and HTTPS before handover.

## License, Installer, And Sync Interpretation

License health reports the current status and expiry window only. It must not expose the raw license key or secret.

Installer health reports whether the installed lock/config is present and whether installer access remains open. Complete and lock the installer before handover.

Standalone sync health reports whether sync is enabled, whether endpoint/token fields are configured, and the pending/failed outbox counts. It must not display the sync token or raw transport secret.

## Safe Output Rules

The health page must never display:

- database passwords;
- raw `.env` values;
- license secrets or raw license keys;
- sync tokens;
- API keys;
- SMTP passwords;
- full private backup paths;
- server secrets or absolute private paths.

Use the page for diagnosis, then ask the client for secrets only through an approved secure support channel when absolutely required.

## Sanfaani Support Troubleshooting Checklist

1. Confirm the owner can open `/admin/standalone/status`.
2. Confirm no secrets or private paths are visible in the browser.
3. Run `php artisan standalone:scheduler-heartbeat` and refresh the health page.
4. Confirm cron runs `php artisan schedule:run` every minute.
5. Check storage writability for `storage/app`, `storage/framework/cache`, and `storage/logs`.
6. Check disk free space and PHP upload limits.
7. Review queue mode and failed jobs count.
8. Verify SMTP sender and delivery transport.
9. Confirm `APP_URL` uses the school domain and HTTPS in production.
10. Confirm installer lock, license status, recent verified backup, and update readiness.
11. Review standalone sync pending/failed counts without exposing tokens.
