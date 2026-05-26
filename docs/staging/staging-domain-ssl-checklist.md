# Staging Domain And SSL Checklist

Use this checklist to validate the staging domain and TLS setup.

## DNS

- [ ] Staging hostname is approved.
- [ ] DNS points to the staging host.
- [ ] No production hostname is reused without approval.
- [ ] `APP_URL` matches the staging HTTPS URL.
- [ ] Callback URLs use the staging URL.

## SSL

- [ ] TLS certificate is issued for the staging hostname.
- [ ] HTTPS loads without browser certificate warnings.
- [ ] HTTP redirects to HTTPS where the host supports it.
- [ ] Mixed content warnings are absent.
- [ ] Login, dashboard, assets, public school pages, result checker, and CBT routes load over HTTPS.

## Application Checks

- [ ] `php artisan route:list` succeeds.
- [ ] Password reset links use the staging URL.
- [ ] Signed marketing and verification URLs use the staging URL.
- [ ] Payment callback placeholders do not point to production.
- [ ] White-label domain provisioning remains planned unless separately approved.

## Hold Conditions

- TLS is invalid.
- `APP_URL` points to production.
- Browser console shows mixed content from production assets.
