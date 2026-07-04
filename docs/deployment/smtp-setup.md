# SMTP Setup

Sanfaani Schools has two separate mail sources:

1. School SMTP, configured by an authorised administrator in **Email Delivery**.
2. The optional installation-level platform fallback, configured through Laravel `.env` values.

School SMTP does not require an `.env` change. Its password is encrypted in the database, masked in the UI, and resolved for the current school at send time. See [School SMTP And Platform Fallback](../notifications/smtp-mail-setup.md) for provider guidance and diagnostics.

## Optional platform fallback

An operator may configure the platform fallback on the server:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=platform@example.com
MAIL_PASSWORD=replace-on-server
MAIL_SCHEME=smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="platform@example.com"
MAIL_FROM_NAME="Sanfaani Schools"
```

For implicit TLS on port 465, use `MAIL_PORT=465`, `MAIL_SCHEME=smtps`, and `MAIL_ENCRYPTION=ssl`. For STARTTLS on port 587, use `MAIL_PORT=587`, `MAIL_SCHEME=smtp`, and `MAIL_ENCRYPTION=tls`. Platform `.env` values are installation secrets and must not be committed. A `log` fallback is useful for development but does not deliver external email.

After changing only platform `.env` values, run:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

School settings saved through the UI purge their runtime mailer automatically and do not depend on `config:cache` being cleared.

## Production verification

1. Save school SMTP in **Email Delivery**.
2. Test school SMTP and confirm the UI names `school_smtp` as the transport.
3. Test platform fallback separately if fallback is intended.
4. Confirm the SMTP server accepted the message.
5. Confirm inbox or spam-folder arrival separately.
6. Verify SPF, DKIM, and DMARC for production sending domains.

Never interpret “accepted by SMTP server” as guaranteed inbox delivery.
