# Super Admin Guide

## Create a School

Use the Super Admin school management page to create a school. If `school_code` is blank, the system can generate one automatically. Use a clear slug because the slug supports branded public routes.

## Manage Schools

Use archive/deactivate instead of casual hard deletion. Archived schools should keep their records for support, billing history, and result integrity.

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

## Audit and Security

Audit logs should be preserved. Payment transactions and scratch card usage records should not be deleted casually.
