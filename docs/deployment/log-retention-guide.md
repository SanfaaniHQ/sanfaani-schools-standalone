# Log Retention Guide

Logs help support, but uncontrolled logs can fill shared-hosting accounts and inflate backup size.

## Defaults

- `SANFAANI_LOG_RETENTION_DAYS=14`
- Production should use `LOG_LEVEL=error` unless actively diagnosing an issue.
- `APP_DEBUG=false` must remain enforced in production.

## Shared Hosting

- Review `storage/logs` regularly.
- Keep logs outside public access.
- Download diagnostic excerpts only when needed.
- Never include logs in marketplace packages or default backup archives.

## Managed/VPS/Cloud

- Prefer centralized log collection where available.
- Rotate logs with hosting tools, Laravel scheduled tasks, or platform log retention settings.
- Keep retention aligned with support and privacy requirements.

## Safety Rules

- Do not expose `.env`, database credentials, SMTP passwords, license keys, API tokens, or absolute sensitive paths in logs.
- Redact secrets before sending support screenshots.
- Prune logs through reviewed maintenance steps, not from the public web UI.
