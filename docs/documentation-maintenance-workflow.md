# Documentation Maintenance Workflow

## When Docs Must Be Updated

Update docs in the same pull request when a change affects:

- Deployment modes, license modes, feature flags, or deployment behavior.
- Installer, licensing, demo, onboarding, marketing, support, tenant isolation, or security behavior.
- User-facing screens, routes, dashboards, forms, or workflows.
- Environment variables or config keys.
- Release, migration, hosting, setup, or support procedures.

## Pull Request Checklist

- Confirm the changed behavior is documented.
- Confirm planned systems are labeled as planned.
- Update URL map entries if a new docs page is added.
- Update role-based or deployment-mode maps if audience scope changes.
- Add release note and changelog entries for customer-visible changes.
- Confirm no secrets, keys, database dumps, logs, or backups are included.

## Release Note Checklist

- What changed.
- Who is affected.
- Deployment or migration steps.
- New environment/config keys.
- Known limitations.
- Support notes.

## Changelog Checklist

- Added.
- Changed.
- Fixed.
- Security.
- Deprecated.
- Planned, only when clearly marked as planned.

## Versioning Strategy

- Keep `main` docs aligned with the active production SaaS release.
- Use versioned docs for marketplace packages and long-lived licensed releases.
- Managed client runbooks may live in private operational docs, but public docs must stay client-neutral.

## Ownership

- Product owns user and buyer docs.
- Engineering owns architecture, developer, security, deployment, update, and backup docs.
- Support owns troubleshooting and support playbooks.
- Sales/onboarding owns demo, onboarding, and buyer journey docs.

## Review Process

- A subject owner reviews the content.
- Engineering reviews technical accuracy.
- Support reviews troubleshooting clarity.
- Product reviews customer-facing tone.

## Docs QA Checklist

- Links resolve locally.
- Commands are accurate.
- Environment variable names match `.env.example`.
- Screens and routes exist if claimed.
- Future systems are not described as shipped.
- Tenant/security warnings are present where needed.
