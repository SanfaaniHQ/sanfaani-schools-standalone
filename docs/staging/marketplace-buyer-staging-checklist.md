# Marketplace Buyer Staging Checklist

## Environment

- [ ] `SANFAANI_DEPLOYMENT_MODE=single_school`
- [ ] `SANFAANI_LICENSE_MODE=annual`
- [ ] `SANFAANI_INSTALLER_ENABLED=true`
- [ ] `SANFAANI_INSTALLED=false`
- [ ] `SANFAANI_MARKETING_AUTOMATION_ENABLED=false`
- [ ] `SANFAANI_DEMO_ENABLED=false`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`

## Expected Features

- [ ] Buyer-safe `.env.marketplace.example` is referenced.
- [ ] Installer and license activation are visible.
- [ ] Guided onboarding is visible.
- [ ] Backup and update foundations are visible.
- [ ] Local branding is visible.
- [ ] SaaS billing, managed-only controls, and marketing automation are hidden.

## Smoke Tests

- [ ] `php artisan marketplace:validate-package` passes and creates no ZIP.
- [ ] Include/exclude rules omit `.env`, logs, backups, private storage, `vendor`, `node_modules`, and `public/build.zip`.
- [ ] Installer flow loads.
- [ ] License activation route loads.
- [ ] School dashboard and setup routes load after installer validation.
- [ ] Buyer docs point to installer, licensing, updates, backups, and support.

## Limitations

- [ ] Marketplace ZIP generation remains planned.
- [ ] Marketplace API integration remains planned.
- [ ] One-click buyer deployment remains planned.
- [ ] Automated restore remains planned.
- [ ] Real update application remains planned.
