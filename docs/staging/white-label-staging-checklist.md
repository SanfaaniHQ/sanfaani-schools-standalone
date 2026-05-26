# White-Label Staging Checklist

## Environment

- [ ] `SANFAANI_DEPLOYMENT_MODE=managed` or approved white-label deployment mode.
- [ ] `SANFAANI_LICENSE_MODE=white_label`
- [ ] `SANFAANI_WHITE_LABEL_ENABLED=true`
- [ ] `SANFAANI_BRAND_MODE=white_label`
- [ ] `SANFAANI_BRANDING_ENABLED=true`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`

## Expected Features

- [ ] `branding_manager` is enabled.
- [ ] `white_label_branding` entitlement is confirmed.
- [ ] Platform, managed, or school branding settings are visible according to mode.
- [ ] Logo, favicon, colors, email footer, report footer, and public identity are configurable.
- [ ] SaaS billing and public demo visibility follow contract scope.

## Smoke Tests

- [ ] Admin branding page loads.
- [ ] School branding page loads.
- [ ] Safe staging logo upload renders in navigation/login/public surfaces.
- [ ] Safe staging favicon upload renders.
- [ ] Public school page uses staging branding.
- [ ] Report footer and email footer copy are reviewed.
- [ ] Accessibility and contrast are reviewed after staging brand changes.

## Limitations

- [ ] White-label domain provisioning remains planned.
- [ ] Reseller tooling remains planned.
- [ ] Full theme builder remains planned.
- [ ] Marketplace-branded ZIP generation remains planned.
