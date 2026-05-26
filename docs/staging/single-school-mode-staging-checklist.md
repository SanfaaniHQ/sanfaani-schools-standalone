# Single-School Mode Staging Checklist

## Environment

- [ ] `SANFAANI_DEPLOYMENT_MODE=single_school`
- [ ] `SANFAANI_LICENSE_MODE=annual`
- [ ] `SANFAANI_INSTALLER_ENABLED=true`
- [ ] `SANFAANI_INSTALLED=false` before installer validation.
- [ ] `SANFAANI_LICENSE_VALIDATION_ENABLED=true`
- [ ] `SANFAANI_DEMO_ENABLED=false`
- [ ] `SANFAANI_MARKETING_AUTOMATION_ENABLED=false`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`

## Expected Features

- [ ] Standalone installer is available before installation lock.
- [ ] License activation and validation routes are visible.
- [ ] Local school settings and school branding are visible.
- [ ] Guided update, backup, performance, and security foundations are visible.
- [ ] SaaS billing, platform demo, and platform marketing route groups are hidden.

## Smoke Tests

- [ ] Installer welcome loads.
- [ ] Requirements and permissions checks load.
- [ ] Database guidance page loads.
- [ ] App key, migrations, admin, school, SMTP, review, and complete stages load.
- [ ] Reinstall lock behavior is verified after completion in staging.
- [ ] License activation screen loads.
- [ ] School dashboard loads after setup.
- [ ] School profile, mail settings, branding, scratch cards, results, and CBT routes load.

## Limitations

- [ ] Buyer deployment remains guided, not one-click automated.
- [ ] Marketplace ZIP generation remains planned.
- [ ] Real update application remains planned.
- [ ] Automated restore remains planned.
