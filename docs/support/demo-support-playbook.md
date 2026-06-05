# Demo Support Playbook

Use this playbook when supporting public marketplace demo visitors.

## What To Say

- The live demo uses fake sample data only.
- Public credentials are safe to share.
- Demo data resets regularly.
- Destructive actions are blocked or limited in safe mode.
- Standalone buyers receive the package, license guidance, and installation support.
- Non-technical buyers should choose done-for-you installation.
- SaaS buyers use the hosted service and do not receive code.

## What Not To Say

- Do not share real super admin credentials.
- Do not claim billing automation or real payment charging is complete.
- Do not claim parent or student portals are complete unless implementation and QA prove it.
- Do not ask buyers to test with real student, parent, staff, payment, license, email, or backup data.

## Operational Checks

Run these commands after deployment and before sharing the public demo link:

```bash
php artisan demo:seed-marketplace
php artisan route:list
php artisan test --filter=MarketplaceLiveDemoTest
php artisan test --filter=DemoSandboxSafetyTest
```

Use `php artisan demo:reset-marketplace --dry-run` to review the reset scope. Use `php artisan demo:reset-marketplace` from cron to refresh the known demo school, users, credentials, and fake sample data without touching non-demo records.
