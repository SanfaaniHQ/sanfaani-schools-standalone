# Pre-Deployment Security Checklist

Use this checklist for authorized local or pre-production testing only. Do not run destructive tests and do not attack external systems.

## 1. Authentication

- Login requires valid email or staff code and password.
- `/admin/login` is separate and accepts Super Admin users only.
- Non-Super Admin users cannot authenticate through `/admin/login`.
- Rate limiting works on failed login attempts.
- Password reset does not disclose whether an account exists.

## 2. Authorization / Access Control

- Unauthenticated users cannot access `/school/dashboard`.
- School Admin users cannot access `/admin/dashboard`.
- Result Officer users cannot publish or unpublish results.
- School Admin users cannot access another school's students.
- School Admin users cannot download another school's scratch cards.
- Super Admin support access requires explicit school selection and shows a support banner.
- Support access start/stop is logged.

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
- Public result checker does not show a school dropdown or public school list.
- The scratch card serial/PIN privately identifies the school before student lookup.
- Step 1 asks only for admission number, scratch card serial number, and PIN.
- Academic session and term options appear only after the access context is verified.
- Session and term options are scoped to the privately identified school.
- Scratch card usage does not increment during Step 1 and increments only after a published result opens.
- Wrong scratch card PIN fails.
- Revoked card fails.
- Expired card fails.
- Used card limit works.
- Result tokens expire.
- Published result access requires `status = published`, `published_at` not null, and `unpublished_at` null.
- Notification links use the generic checker or school slug route, not internal `school_id` query strings.

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
- Super Admin System Maintenance actions are POST-only, CSRF protected, and run fixed Artisan commands only.
- System Maintenance buttons require confirmation before cache, optimize, or storage-link actions run.

## 17. School Data Isolation

- Creating a new school does not clone another school's students, results, scratch cards, classes, subjects, sessions, terms, users, or uploaded files.
- School-facing controllers scope reads and writes to the current school through `school_id`.
- Safe defaults may be created only for settings such as admission numbers, report-card configuration, grading scale, access policy, subscription, or trial status when explicitly implemented.
- Super Admin support access must be selected deliberately, show a banner, and write audit logs.

## 18. Upload and Storage Security

- `FILESYSTEM_DISK=public` is set for production uploads that need browser display.
- Uploaded platform logos, favicons, school logos, and signatures are stored as relative public-disk paths.
- Views render uploaded images through `Storage::url()` or model/service helpers.
- Run `php artisan storage:link` after deployment and repair the link if uploaded images do not display.
- Missing images fall back to initials or text and should not break public pages.

## Safe Local Checks

```bash
npm audit
composer audit
php artisan route:list
php artisan optimize:clear
```

Review output manually. Do not install aggressive scanners without approval.
