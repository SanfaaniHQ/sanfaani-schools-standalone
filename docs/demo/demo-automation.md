# Demo Automation

The demo automation foundation supports public demo requests, role-based demo users, demo sessions, credentials, activity tracking, and expiry.

## Current Foundation

Models:

- `DemoRequest`
- `DemoSession`
- `DemoCredential`
- `DemoActivity`

Services:

- `DemoRequestService`
- `DemoEnvironmentService`
- `DemoCredentialService`
- `DemoActivityService`
- `DemoExpiryService`

Jobs and commands:

- `CreateDemoEnvironmentJob`
- `ExpireDemoSessionJob`
- `demo:expire-sessions`

## Security Rules

- Demo passwords are not stored in plain text.
- Demo credentials expire.
- Demo sessions are school-scoped.
- Demo reset remains disabled unless a safe demo-only pattern exists.
- Demo routes are feature-gated by `demo_system`.

## Sales Integration

Demo requests can feed the marketing foundation through lead activity, scoring, and sales task creation.

## Not Implemented Yet

Full sales automation sequences and billing conversion are not implemented in the demo system itself.
