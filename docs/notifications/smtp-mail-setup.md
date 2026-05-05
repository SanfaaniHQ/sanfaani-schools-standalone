# SMTP Mail Setup

Sanfaani Schools uses Laravel mail and notifications. Credentials must live only in `.env`; never hardcode or commit SMTP usernames, passwords, app passwords, API keys, or gateway secrets.

V1.1 adds admin dashboard mail settings with encrypted password storage and masked UI placeholders.

## Local Testing

Use log mail locally:

```dotenv
MAIL_MAILER=log
MAIL_FROM_ADDRESS="sanfaanisaas@gmail.com"
MAIL_FROM_NAME="Sanfaani Schools"
```

Emails will be written to Laravel logs instead of being delivered.

## Production SMTP

Use:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="sanfaanisaas@gmail.com"
MAIL_FROM_NAME="Sanfaani Schools"
```

Provider examples:

- Gmail SMTP with an app password.
- Namecheap Private Email SMTP.
- Zoho Mail SMTP.
- Mailgun, Postmark, or Resend later if transactional delivery volume grows.

Do not paste real Gmail passwords or app passwords into documentation, Blade, JavaScript, Git, tickets, screenshots, or support chats.

## Test Mail

After configuring `.env`, run:

```bash
php artisan optimize:clear
php artisan tinker
```

Then:

```php
$school = App\Models\School::where('status', 'active')->latest()->firstOrFail();

Illuminate\Support\Facades\Notification::route('mail', 'sanfaanisaas@gmail.com')
    ->notify(new App\Notifications\SchoolCreatedNotification($school));
```

Use a safe test school record. Do not send student data to external addresses during testing.

## Troubleshooting

- Confirm `MAIL_HOST`, `MAIL_PORT`, username, password, and encryption.
- Confirm the hosting provider allows outbound SMTP.
- Confirm the from address is verified or allowed by the SMTP provider.
- Check `storage/logs` privately.
- Clear config cache after changing `.env`.
- Use `MAIL_MAILER=log` to confirm notification code runs without SMTP.
