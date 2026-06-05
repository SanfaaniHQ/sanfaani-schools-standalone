# Demo Credentials And Roles

Marketplace demo credentials are public, safe credentials for fake sample data only.

| Role | Email | Password | Public card |
| --- | --- | --- | --- |
| School Admin | `schooladmin@demo.sanfaani.net` | `password` | Yes |
| Teacher | `teacher@demo.sanfaani.net` | `password` | Yes |
| Result Officer | `resultofficer@demo.sanfaani.net` | `password` | Yes |
| Accountant | `accountant@demo.sanfaani.net` | `password` | No |

The accountant credential is seeded as a foundation only. It should not be promoted as a working buyer preview until accountant routes and dashboards are complete.

Do not publish real super admin credentials. If a platform-level preview is ever added, it must be a restricted demo-only account with no access to secrets, license keys, backup restore, update upload, or destructive platform tools.

Seed or refresh the foundation with:

```bash
php artisan demo:seed-marketplace
php artisan demo:reset-marketplace
```

Both commands are idempotent and scoped to the configured marketplace demo school and known public demo users.
