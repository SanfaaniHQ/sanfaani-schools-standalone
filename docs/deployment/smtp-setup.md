# SMTP Setup

Email delivery requires buyer-owned or operator-owned SMTP credentials.

## Required Values

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=change-me
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## Shared Hosting

- Use cPanel email, Namecheap Private Email, or a trusted SMTP provider.
- Confirm port 587/TLS or 465/SSL with the provider.
- Some hosts block external SMTP ports; ask support if delivery fails.

## Security

- Store passwords only in `.env` or the cloud secret manager.
- Do not commit SMTP credentials.
- Do not include real SMTP values in marketplace packages.
- Rotate credentials after staff changes or support handover.

## Troubleshooting

- Authentication failed: username/password or app password is wrong.
- Connection timeout: port blocked or wrong host.
- Mail from mismatch: sender address not verified.
- Cached settings: run `php artisan optimize:clear` after `.env` changes.
