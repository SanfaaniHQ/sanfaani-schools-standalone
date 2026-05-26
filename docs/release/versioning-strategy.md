# Versioning Strategy

Use semantic versioning:

- Major: breaking deployment or data contract changes.
- Minor: backward-compatible features or commercial foundations.
- Patch: bug fixes, test hardening, docs updates, safe hotfixes.

Channels:

- `stable` for production releases.
- `beta` for pre-release validation.
- `security` for security-only releases.
- `hotfix` for urgent patch releases.
- `managed` for client-specific managed release coordination.
- `white_label` for licensed white-label releases.
- `marketplace` for buyer package validation.

Do not tag or publish a version until release readiness checks pass.
