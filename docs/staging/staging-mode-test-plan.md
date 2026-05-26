# Staging Mode Test Plan

This plan validates expected behavior for each staging mode without adding runtime features.

## Shared Test Steps

For every mode:

1. Set the documented env values in a staging-only environment.
2. Clear only the staging environment state through normal staging operations outside this repo audit.
3. Confirm `php artisan staging:check-readiness` exits successfully.
4. Confirm route visibility matches `docs/staging/staging-environment-matrix.md`.
5. Confirm hidden features do not appear in navigation or route groups where the mode should hide them.
6. Confirm onboarding, demo, licensing, update, backup, and branding behavior matches the mode matrix.
7. Record results in `docs/staging/staging-smoke-test-results-template.md`.

## SaaS Mode Tests

- Confirm platform dashboard and school management routes load for authorized Super Admin users.
- Confirm subscription visibility is present but full billing/payment workflow remains planned.
- Confirm standalone installer and local license activation are hidden.
- Confirm demo, onboarding, marketing, diagnostics, update, backup, and branding foundations are visible where feature gates allow.

## single_school Mode Tests

- Confirm installer access before installation lock.
- Confirm license activation routes are visible.
- Confirm platform SaaS school onboarding, marketing, and billing route groups are hidden.
- Confirm school dashboard, school settings, branding, backup, update, performance, and security diagnostics are available where configured.

## Managed Mode Tests

- Confirm managed support, managed backup, managed update, managed branding, and managed security/performance visibility.
- Confirm SaaS billing and public demo route groups are hidden unless explicitly enabled by contract scope.
- Confirm guided onboarding and support handover flow are documented.

## Demo Mode Tests

- Confirm public demo request route, demo session management, credentials, activity, and expiry behavior.
- Confirm demo reset remains disabled by default.
- Confirm backup/update operations are not presented as demo automation.
- Confirm sales handoff remains foundation-level and does not claim paid conversion automation.

## Trial Mode Tests

- Confirm trial-aware onboarding visibility.
- Confirm lead scoring and sales task flow can support manual follow-up.
- Confirm trial-to-paid billing conversion remains planned.
- Confirm managed-only and standalone-only controls stay hidden.

## white_label Mode Tests

- Confirm white-label entitlement before enabling white-label branding.
- Confirm branding forms handle logo, favicon, colors, email footer, report footer, and public identity.
- Confirm white-label domain provisioning and reseller tooling remain planned.
- Confirm update and backup behavior remains foundation-level.

## Marketplace Buyer Package Mode Tests

- Confirm `.env.marketplace.example` is buyer-safe.
- Confirm `marketplace:validate-package` passes and creates no ZIP.
- Confirm buyer package excludes `.env`, logs, backups, private storage, `vendor`, `node_modules`, and `public/build.zip`.
- Confirm installer, license activation, guided onboarding, backup, update, and branding docs are linked.
- Confirm marketplace ZIP generation and marketplace API integration remain planned.

## Required Audit References

- Deployment readiness: `php artisan deployment:check-readiness`
- Performance audit: `php artisan performance:audit`
- Security audit: `php artisan security:audit`
- Release readiness: `php artisan release:check-readiness`
- Marketplace validation: `php artisan marketplace:validate-package`
