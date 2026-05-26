# Staging Mail And SMTP Checklist

Use this checklist to validate outbound email safety in staging.

## SMTP Placeholders

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.staging.example.test
MAIL_PORT=587
MAIL_USERNAME=replace_with_staging_smtp_username
MAIL_PASSWORD=replace_with_staging_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@staging.example.test
MAIL_FROM_NAME="${APP_NAME}"
SANFAANI_EMAIL_SAFETY_ENABLED=true
SANFAANI_EMAIL_FOOTER_ENABLED=true
SANFAANI_EMAIL_UNSUBSCRIBE_REQUIRED=true
```

## Safety Checks

- [ ] SMTP credentials are stored only in the staging `.env`.
- [ ] SMTP credentials are not committed.
- [ ] Staging sends only to approved staging recipients.
- [ ] Marketing unsubscribe links are present where required.
- [ ] Email footers identify the staging sender.
- [ ] Failed email logs do not expose credentials.
- [ ] No real customer mailing list is imported.

## Functional Checks

- [ ] Password reset email can be requested for a staging account.
- [ ] Demo or trial email sends only to a staging recipient.
- [ ] Marketing email safety checks pass.
- [ ] `php artisan security:audit` reports no email safety failures under production-style env values.

## Hold Conditions

- Real recipients receive staging email.
- SMTP credentials appear in logs, docs, screenshots, or tickets.
- `MAIL_MAILER=log` is used for a staging verification that needs real SMTP behavior.
