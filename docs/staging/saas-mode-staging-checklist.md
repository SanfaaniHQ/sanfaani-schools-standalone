# SaaS Mode Staging Checklist

## Environment

- [ ] `SANFAANI_DEPLOYMENT_MODE=saas`
- [ ] `SANFAANI_LICENSE_MODE=subscription`
- [ ] `SANFAANI_INSTALLER_ENABLED=false`
- [ ] `SANFAANI_INSTALLED=true`
- [ ] `SANFAANI_DEMO_ENABLED=true`
- [ ] `SANFAANI_MARKETING_AUTOMATION_ENABLED=true`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`

## Expected Features

- [ ] Platform school management is visible to authorized Super Admin users.
- [ ] Subscription and feature override visibility is available.
- [ ] Demo, onboarding, marketing, update, backup, performance, security, and branding foundations are visible where feature gates allow.
- [ ] Standalone installer and local license activation are hidden.

## Smoke Tests

- [ ] Super Admin login works.
- [ ] Platform dashboard loads.
- [ ] School list and create/edit screens load.
- [ ] Feature override screen loads.
- [ ] Demo request and demo admin screens load.
- [ ] Onboarding progress screen loads.
- [ ] Marketing dashboard and sales tasks load.
- [ ] Update and backup dashboards load as foundation workflows.
- [ ] Branding page loads and safe staging assets render.

## Limitations

- [ ] Full billing/payment workflow remains planned.
- [ ] Trial-to-paid automation remains planned.
- [ ] Real update application remains planned.
- [ ] Automated restore remains planned.
