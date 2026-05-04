# Super Admin Guide

## Create a School

Use the Super Admin school management page to create a school. If `school_code` is blank, the system can generate one automatically. Use a clear slug because the slug supports branded public routes.

## Manage Schools

Use archive/deactivate instead of casual hard deletion. Archived schools should keep their records for support, billing history, and result integrity.

Use Support Access only when helping a school. It starts from School Management, is logged, shows a support banner, and can be ended from the banner or admin route. Super Admin actions remain attributed to the Super Admin account.

New schools must not inherit another school's students, results, scratch cards, users, classes, subjects, sessions, or terms. Only safe defaults such as report-card defaults, grading defaults, or trial plan setup should be created automatically when explicitly implemented.

## Admin Login

Super Admin users should use `/admin/login`. The standard `/login` page remains for School Admin, Result Officer, and future teacher users. Non-Super Admin accounts are rejected from the admin login area.

## Scratch Card Approval

1. Review the school request.
2. Confirm manual payment when applicable.
3. Generate cards only after approval.
4. Download cards for controlled distribution.
5. Revoke cards or batches when access should be stopped.

Scratch cards should be revoked, not deleted.

## Payments

Manual payment is the active production launch flow. Paystack Auto Payment and Flutterwave Auto Payment are prepared through environment configuration but remain disabled until production keys and webhook handling are enabled.

## Plans and Feature Access

Use subscription plans, feature overrides, and result access policies to shape production agreements. Avoid hardcoding commercial rules into views.

## Lead Requests

Demo and contact requests are stored in Super Admin > Lead Requests. Update statuses as `new`, `contacted`, `converted`, or `closed`, and keep internal notes there.

## System Updates and Maintenance

System Updates stores uploaded update packages privately and does not apply them automatically. Always back up database and files before any update.

After deployment or update, use System Maintenance in this order: Clear All Cache, then Optimize Application. If uploaded images do not display, run Storage Link and confirm `APP_URL`, `FILESYSTEM_DISK`, file permissions, and config cache.

## Audit and Security

Audit logs should be preserved. Payment transactions and scratch card usage records should not be deleted casually.

Public result checking should remain school-private: no public school dropdowns, no school list, and no similar-school hints. Scratch cards identify the school privately, and usage should only increase after a published result opens successfully.
