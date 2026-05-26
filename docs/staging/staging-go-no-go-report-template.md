# Staging Go/No-Go Report Template

Use this template to record the real staging deployment decision.

## Release Candidate

- Branch:
- Commit:
- Deployment date:
- Reviewer:
- Target staging URL:
- Target mode:

## Environment Summary

- `APP_ENV`:
- `APP_DEBUG`:
- `SANFAANI_DEPLOYMENT_MODE`:
- `SANFAANI_LICENSE_MODE`:
- `SANFAANI_INSTALLER_ENABLED`:
- `SANFAANI_DEMO_ENABLED`:
- `SANFAANI_ONBOARDING_ENABLED`:
- `SANFAANI_MARKETING_AUTOMATION_ENABLED`:
- `SANFAANI_UPDATES_ENABLED`:
- `SANFAANI_BACKUPS_ENABLED`:
- `SANFAANI_BRAND_MODE`:
- `SANFAANI_WHITE_LABEL_ENABLED`:

## Validation Results

| Check | Result | Notes |
| --- | --- | --- |
| Full test suite |  |  |
| Route list |  |  |
| Staging readiness |  |  |
| Deployment readiness |  |  |
| Performance audit |  |  |
| Security audit with production-style env |  |  |
| Release readiness |  |  |
| Marketplace validation |  |  |
| Git diff check |  |  |
| Smoke tests |  |  |

## Mode Checklist

- Checklist used:
- Required env values confirmed:
- Expected enabled features confirmed:
- Expected hidden features confirmed:
- Admin routes confirmed:
- School routes confirmed:
- Onboarding/demo/licensing behavior confirmed:
- Backup/update behavior confirmed:
- Branding behavior confirmed:
- Known limitations accepted:

## Go/No-Go Decision

- Decision:
- Approver:
- Date:
- Accepted risks:
- Blockers:
- Required follow-up before production:

## Scope Confirmation

- [ ] No release ZIP was generated.
- [ ] No destructive deployment automation was run.
- [ ] `public/build.zip` was not modified or used as a package.
- [ ] Protected migration was not modified.
- [ ] Full billing automation remains planned.
- [ ] Real update application remains planned.
- [ ] Automated restore remains planned.
