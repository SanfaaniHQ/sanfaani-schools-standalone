# Queue Performance Guide

Sanfaani Schools supports shared-hosting-safe queue fallbacks and stronger worker strategies for VPS/cloud deployments.

## Shared Hosting

- `QUEUE_CONNECTION=sync` is acceptable for small installations and demos.
- Use `database` queues when cron is available but persistent workers are not.
- Keep jobs small and idempotent.
- Prefer chunk sizes near `SANFAANI_BULK_OPERATION_CHUNK_SIZE`.
- Avoid long-running web requests for bulk messaging, imports, backup verification, and update checks.

## Cron Strategy

Add a cron entry where supported:

```bash
* * * * * php /home/account/path/to/artisan schedule:run >> /dev/null 2>&1
```

Use the real cPanel/Namecheap path shown by the hosting account. Do not publish sensitive absolute paths in screenshots or support tickets.

## VPS And Cloud

- Use Supervisor, systemd, or a managed worker service.
- Set worker memory and timeout limits.
- Restart workers during deployments.
- Monitor failed jobs.

## Bulk Work

- Bulk messages should resolve recipients once, store batch metadata, and process in chunks.
- Exports above `SANFAANI_MAX_EXPORT_ROWS` should be queued or split.
- Backup and update jobs should remain preflight/metadata-only on shared hosting until the full automation layer exists.
