# Email and Notification Flow

Notifications use Laravel Notifications and respect the notification preference foundation where it is checked.

V1.1 introduces in-app support threads (database-backed timeline). Real-time websocket notifications are intentionally deferred.

## Implemented Email Events

- Staff account created: sent to the user email for school staff such as result officers and teachers.
- School created: sent to the school email when available.
- Student created: sent to guardian email when available.
- Result published: sent to guardian email when available.
- Scratch card request submitted: sent to the requesting School Admin.
- Scratch card payment confirmed: sent to School Admin users.
- Scratch cards generated: sent to School Admin users.
- Scratch card batch revoked: sent to School Admin users.

## Password Handling

Account emails do not include plaintext passwords. The administrator must provide credentials securely. If a future flow generates temporary passwords automatically, it must be explicit, short-lived, and handled carefully.

## Notification Preferences

The `notification_preferences` table supports:

- `email`
- `sms`
- `whatsapp`
- `in_app`

Email defaults to enabled. SMS and WhatsApp are placeholders for future provider integration and are disabled unless explicitly implemented.

## Available on Selected Plans

- SMS notifications
- WhatsApp notifications
- In-app notification inbox
- Queued mail delivery for higher volume production use

## Wiring Notes

When adding new events, prefer Laravel Notifications, keep messages practical, avoid secrets in payloads, and check preferences before sending where possible.
