# Marketplace Live Demo

`demo.sanfaani.net` is intended to run as a public buyer preview for marketplace visitors. It is separate from the guided demo request flow at `/demo`, which still collects buyer details and prepares a private demo environment.

## Configuration

Public live demo mode is disabled unless explicitly enabled:

```env
SANFAANI_MARKETPLACE_DEMO_ENABLED=true
SANFAANI_MARKETPLACE_DEMO_AUTO_LOGIN=false
SANFAANI_MARKETPLACE_DEMO_RESET_HOURS=24
SANFAANI_MARKETPLACE_DEMO_SAFE_MODE=true
```

Keep auto-login disabled unless the demo school has been seeded and safe mode is confirmed in production. The public page at `/demo/live` shows safe credentials and buyer guidance only when marketplace demo mode is enabled.

## Buyer Flow

Visitors can preview Sanfaani Schools with fake sample data, then choose one of three next steps:

- Buy the cPanel-ready standalone package.
- Request done-for-you installation if they are not technical.
- Contact Sanfaani for sales or support questions.

SaaS buyers use the hosted service and do not receive code. Standalone buyers receive the packaged application, license guidance, and installation support according to the purchased offer.

Parent and student portals must not be advertised as complete unless the implemented code proves those workflows are production-ready.
