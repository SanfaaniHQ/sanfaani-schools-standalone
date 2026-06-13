# Portal Login Linking Guide

The future separate Next.js website should link to Laravel for all private portal access. It should not authenticate school users itself.

## Recommended Links

| Website label | Laravel target | Notes |
| --- | --- | --- |
| Portal Login | `/login` | Default school-level login. |
| Staff Login | `/login` | Staff roles use Laravel authentication and role context. |
| Parent Login | `/login` | Show only when parent portal access is enabled for the deployment. |
| Student Login | `/login` | Show only when student portal access is enabled for the deployment. |
| Admin Login | `/admin/login` | Super Admin/local owner login; avoid casual public placement. |

## V1 Guidance

The public website should use a single "Portal Login" button unless the school's commercial package and Laravel deployment clearly support separate parent/student/staff messaging.

The website should not promise private parent or student dashboards unless those workflows are implemented, enabled, and included in the buyer scope.

## What The Website Must Not Do

- Store passwords.
- Create Laravel user sessions.
- Reimplement Laravel authentication.
- Store student/staff/parent accounts as source of truth.
- Display private dashboard links before Laravel authentication.
- Expose role-specific dashboard URLs as public data.

## Configuration

The future Next.js repo should read:

```env
NEXT_PUBLIC_PORTAL_LOGIN_URL=
```

This should point to the approved Laravel login URL for the deployment.

## Related Docs

- [Website-Laravel Link Contract](website-laravel-link-contract.md)
- [Security And Privacy Boundaries](security-and-privacy-boundaries.md)
- [Roles And Permissions](../users/roles-and-permissions.md)
