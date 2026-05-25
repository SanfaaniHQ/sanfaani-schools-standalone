# Email Safety Checklist

Outbound email safety covers transactional, demo, onboarding, licensing, marketing, and operational notifications.

## Required

- Configure a valid `MAIL_FROM_ADDRESS`.
- Keep SMTP passwords in `.env` only.
- Do not render `.env`, stack traces, server paths, or raw tokens in email templates.
- Marketing emails must include unsubscribe/footer copy when `SANFAANI_EMAIL_UNSUBSCRIBE_REQUIRED=true`.
- Suppressed or unsubscribed contacts must not receive marketing automation mail.
- Demo credential emails must not include raw temporary passwords or internal paths.
- Test mail delivery through safe admin tools only, never from diagnostics.

## Shared Hosting Notes

cPanel and Namecheap mail limits can throttle bulk mail. Use small queue batches, retry carefully, and prefer verified SMTP credentials over PHP `mail()`.
