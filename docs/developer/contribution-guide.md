# Contribution Guide

## Engineering Rules

- Reuse existing School, Subscription, FeatureOverride, Role, Permission, Branding, Result, CBT, Scratch Card, Marketing, and Support systems.
- Do not hard-code client names, school names, domains, marketplace packages, or buyer-specific behavior.
- Use deployment mode, license mode, tenant context, feature access, and authorization checks.
- Add tests for cross-school access and commercial gating changes.

## Documentation Rules

Update docs when behavior, config, routes, deployment steps, or role access changes.

Planned systems must be labeled as planned.

## Validation

Run:

```bash
php artisan test
php artisan route:list
git diff --check
```
