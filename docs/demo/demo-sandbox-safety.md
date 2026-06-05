# Demo Sandbox Safety

Marketplace live demo safe mode blocks high-risk actions for users linked to active demo credentials.

Blocked or limited areas include:

- Password, email, and destructive profile changes.
- Backup creation, pruning, restore planning actions that could alter state, and system maintenance actions.
- Update upload, preflight mutation, and ready/apply-style update actions.
- License activation or validation changes.
- Payment confirmation and payment gateway changes.
- Platform and school mail setting changes or test sends.
- Real bulk communication sends and retries.
- Archive, revoke, restore, delete, publish, unpublish, and similar destructive school/admin actions where route coverage is practical.

The guard is route-name driven through `demo.marketplace.blocked_routes`, with a conservative fallback for high-risk write URLs. Normal non-demo users are not blocked by the sandbox guard.

Remaining manual restrictions:

- Do not grant public demo users real super admin access.
- Do not use customer data in the marketplace demo school.
- Review new destructive routes and add them to the blocked route list before enabling them on `demo.sanfaani.net`.
- Keep real email, payment, license, backup, and update credentials out of the demo environment.
