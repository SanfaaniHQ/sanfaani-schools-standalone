# Staging Queue And Cron Checklist

Use this checklist to validate scheduled tasks and queued work for the selected staging host.

## Queue Strategy

Shared hosting baseline:

```dotenv
QUEUE_CONNECTION=database
SANFAANI_QUEUE_SYNC_FALLBACK=true
```

Simple single-school fallback:

```dotenv
QUEUE_CONNECTION=sync
SANFAANI_QUEUE_SYNC_FALLBACK=true
```

VPS or cloud baseline:

```dotenv
QUEUE_CONNECTION=database
SANFAANI_QUEUE_SYNC_FALLBACK=false
```

Use a long-running worker only where the host supports it.

## Cron

```cron
* * * * * cd /path/to/sanfaani-schools && php artisan schedule:run >> /dev/null 2>&1
```

## Checks

- [ ] `jobs` and failed job storage are migrated.
- [ ] Scheduler command is configured through the host control panel or server cron.
- [ ] Demo expiry, marketing tasks, cleanup tasks, and maintenance tasks are understood for the selected mode.
- [ ] Queue jobs are small and idempotent.
- [ ] Failed jobs do not expose secrets.
- [ ] Worker logs are private.
- [ ] `php artisan performance:audit` records queue and scheduler guidance.

## Hold Conditions

- The host cannot run the selected queue strategy.
- Scheduler output writes to a public path.
- Long-running workers are configured on shared hosting without support.
