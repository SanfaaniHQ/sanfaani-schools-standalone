# Production Security Hardening

Sanfaani Schools production deployments must run with `APP_ENV=production` and `APP_DEBUG=false`.

The security diagnostics foundation is read-only. It reports unsafe settings, outbound email concerns, token expiry guidance, and logging redaction posture without sending email, clearing caches, rotating logs, running migrations, writing `.env`, or calling external services.

## Production Checklist

- Set `APP_DEBUG=false`.
- Set `APP_ENV=production`.
- Configure `APP_KEY`.
- Keep `.env`, storage private folders, logs, backups, and cache files outside the public document root.
- Use HTTPS for login, installer, license activation, unsubscribe, and result access flows.
- Keep demo credentials temporary and avoid sending raw passwords by email.
- Review queue failure payloads for secrets before enabling persistent workers.
- Use signed or encrypted tokens for public links.
- Confirm backup and update logs redact secrets before exposing them in the admin UI.

## Never Expose

- `.env` contents.
- Database passwords.
- Mail passwords.
- License keys.
- API tokens.
- Stack traces.
- Absolute server paths.
- Backup archive contents.
- Private storage paths.
