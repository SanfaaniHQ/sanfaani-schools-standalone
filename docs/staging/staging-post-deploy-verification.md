# Staging Post-Deploy Verification

Complete this after the command sequence finishes. Record actual results; do not mark staging accepted until signoff is reviewed.

## Command Evidence

| Check | Actual result | Notes |
| --- | --- | --- |
| `php artisan test` |  |  |
| `php artisan route:list` |  |  |
| `php artisan staging:check-readiness` |  |  |
| `php artisan deployment:check-readiness` |  |  |
| `php artisan performance:audit` |  |  |
| `php artisan security:audit` with production-style env |  |  |
| `php artisan release:check-readiness` |  |  |
| `php artisan marketplace:validate-package` |  |  |
| `git diff --check` |  |  |

## Browser Verification

- [ ] Public landing page loads over HTTPS.
- [ ] Login page loads over HTTPS.
- [ ] Super Admin dashboard loads after authentication.
- [ ] School dashboard loads for a staging school user.
- [ ] Core result, CBT, communication, support, branding, and public school routes load for authorized users.
- [ ] Unauthorized users cannot access protected screens.
- [ ] Validation and error pages do not expose secrets or server paths.

## Mode Verification

- [ ] Selected `SANFAANI_DEPLOYMENT_MODE` is recorded.
- [ ] Selected `SANFAANI_LICENSE_MODE` is recorded.
- [ ] Enabled features match the selected mode checklist.
- [ ] Hidden features are not visible in navigation or direct route checks.
- [ ] Known limitations are documented in the signoff report.

## Safety Verification

- [ ] `.env` is not reachable through the browser.
- [ ] Logs, backups, dumps, `vendor`, `node_modules`, and private storage are not web-accessible.
- [ ] `public/build.zip` is not used for deployment packaging.
- [ ] Staging email sends only to approved staging recipients.
- [ ] Production payment keys are not configured.
- [ ] No docs or screenshots include real secrets.
