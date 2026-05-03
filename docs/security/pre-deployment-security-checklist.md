# Pre-Deployment Security Checklist

Use this checklist for authorized local or pre-production testing only. Do not run destructive tests and do not attack external systems.

## 1. Authentication

- Login requires valid email or staff code and password.
- Rate limiting works on failed login attempts.
- Password reset does not disclose whether an account exists.

## 2. Authorization / Access Control

- Unauthenticated users cannot access `/school/dashboard`.
- School Admin users cannot access `/admin/dashboard`.
- Result Officer users cannot publish or unpublish results.
- School Admin users cannot access another school's students.
- School Admin users cannot download another school's scratch cards.

## 3. Session Management

- Logout invalidates the authenticated session.
- Session cookies are scoped correctly for production.
- Production uses HTTPS.

## 4. Input Validation

- Required fields validate.
- Long strings are rejected where limits exist.
- Numeric fields reject invalid numbers.

## 5. File Upload Security

- Platform logo rejects invalid file types.
- School logo rejects invalid file types.
- Uploads are limited to 2MB.
- Uploads are stored under `storage/app/public`, not committed.
- SVG is not inlined and is only accepted for favicon.

## 6. CSRF Protection

- All POST, PATCH, PUT, and DELETE forms include CSRF tokens.

## 7. XSS Prevention

- Blade output uses escaped `{{ }}` by default.
- Uploaded filenames are not rendered as trusted HTML.
- User content is not injected into JavaScript without encoding.

## 8. SQL Injection Prevention

- Queries use Eloquent or query builder bindings.
- Manual SQL is limited to migrations and reviewed.

## 9. Sensitive Data Exposure

- `.env` is not committed or publicly accessible.
- Payment secret keys are not exposed in Blade or JavaScript.
- SMTP credentials are not exposed in Blade or JavaScript.
- Logs and backups are not public.

## 10. Error Handling

- Production has `APP_DEBUG=false`.
- Error pages do not expose stack traces.

## 11. Rate Limiting

- Public result checker has throttling.
- Contact and demo forms have throttling.

## 12. Public Result Checker Security

- Unpublished results are not exposed.
- Wrong scratch card PIN fails.
- Revoked card fails.
- Expired card fails.
- Used card limit works.
- Result tokens expire.

## 13. Scratch Card Security

- PIN hashes are used for validation.
- CSV downloads are restricted to authorized users.
- Batch and card revocation works.
- Generation cannot be repeated for an existing batch.

## 14. Payment Config Security

- Paystack and Flutterwave keys are read from `.env`.
- Secret keys are never rendered in frontend.
- Manual payment confirmation is Super Admin only.

## 15. Backup Security

- Backups are stored outside public web root.
- `.env` backups are encrypted or access controlled.
- Restore is tested privately.

## 16. Deployment Config Security

- `storage` and `bootstrap/cache` are writable.
- Whole project is not world-writable.
- `config:cache`, `route:cache`, and `view:cache` work.

## Safe Local Checks

```bash
npm audit
composer audit
php artisan route:list
php artisan optimize:clear
```

Review output manually. Do not install aggressive scanners without approval.
