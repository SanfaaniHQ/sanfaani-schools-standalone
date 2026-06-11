# Queue And Cron Strategy

Sanfaani Schools can run with simple shared-hosting defaults or stronger VPS/cloud workers.

## Scheduler

Laravel scheduler cron:

```bash
* * * * * php /path/to/app/artisan schedule:run >> /dev/null 2>&1
```

Use the correct PHP binary path for the host.

Standalone installs record a scheduler heartbeat through the scheduled `standalone:scheduler-heartbeat` command. The owner health page uses that heartbeat to warn when cron is not running or has gone stale. See `docs/standalone/system-health-and-scheduler-monitoring.md`.

## Shared Hosting

- Use `QUEUE_CONNECTION=sync` for simple deployments.
- Use `database` queue only if cron can reliably process jobs.
- Avoid long-running `queue:work` processes when the host kills background tasks.

Cron-triggered queue fallback:

```bash
* * * * * php /path/to/app/artisan queue:work --stop-when-empty --tries=3 --timeout=60 >> /dev/null 2>&1
```

## VPS Or Cloud

- Use Redis or database queues.
- Run `queue:work` under Supervisor, systemd, or platform workers.
- Restart workers after deployment.

## Jobs To Watch

- Demo expiry.
- Marketing automation.
- Email sending.
- Backup verification jobs.
- Update preflight jobs.

Do not run deployment, migration, restore, or package extraction jobs from web requests.
