# Logging And Secret Redaction

Logs are operational records, not a safe place for secrets.

## Redaction Rules

The redaction foundation masks:

- Passwords and SMTP passwords.
- API keys and access keys.
- Bearer/basic authorization values.
- License keys.
- Database URLs and DSNs.
- Absolute application and storage paths.

## Operational Rules

- Avoid `LOG_LEVEL=debug` in production.
- Keep log retention short on shared hosting.
- Do not expose logs through public routes.
- Review failed queue payloads before sharing with support.
- Backup and update logs should show sanitized messages and context only.
