# Demo And Trial Staging Checklist

## Demo Environment

- [ ] `SANFAANI_DEPLOYMENT_MODE=saas`
- [ ] `SANFAANI_LICENSE_MODE=demo`
- [ ] `SANFAANI_DEMO_ENABLED=true`
- [ ] `SANFAANI_DEMO_RESET_ENABLED=false`
- [ ] `SANFAANI_ONBOARDING_DEMO_ENABLED=true`
- [ ] `SANFAANI_MARKETING_AUTOMATION_ENABLED=true`

## Trial Environment

- [ ] `SANFAANI_DEPLOYMENT_MODE=saas`
- [ ] `SANFAANI_LICENSE_MODE=trial`
- [ ] `SANFAANI_ONBOARDING_TRIAL_ENABLED=true`
- [ ] `SANFAANI_MARKETING_AUTOMATION_ENABLED=true`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`

## Expected Features

- [ ] Public demo request route is available.
- [ ] Demo session admin screens are available.
- [ ] Role-based demo credentials can be generated safely.
- [ ] Demo activity and expiry are recorded.
- [ ] Trial-aware onboarding is visible.
- [ ] Marketing lead scoring and sales tasks support manual follow-up.

## Smoke Tests

- [ ] Submit a staging demo request using a staging-only email.
- [ ] Confirm demo request appears in admin.
- [ ] Confirm demo session creation path works in staging.
- [ ] Confirm credentials do not expose plain-text passwords at rest.
- [ ] Confirm `demo:expire-sessions` behavior is understood before scheduling.
- [ ] Confirm onboarding progress updates for demo/trial users.
- [ ] Confirm unsubscribe route does not reveal whether a contact exists.

## Limitations

- [ ] Demo reset remains disabled unless a safe demo-only reset pattern exists.
- [ ] Full demo-to-paid conversion automation remains planned.
- [ ] Trial-to-paid billing automation remains planned.
- [ ] Provider-specific WhatsApp sending remains planned.
