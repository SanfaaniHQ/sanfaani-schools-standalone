# Website Deployment Positioning

The future Next.js website is a separate public deployment. DNS, SSL, hosting, content writing, and design customization are deployment/business tasks, not Laravel code changes.

## Deployment Option A

```text
www.schoolname.com -> Next.js public website
portal.schoolname.com -> Laravel portal
```

Best when the school wants a clear public website and a separate portal subdomain.

## Deployment Option B

```text
schoolname.sanfaani.net -> Next.js public website
portal.schoolname.sanfaani.net -> Laravel portal
```

Best for Sanfaani-managed domains or managed-client deployments.

## Deployment Option C

```text
schoolname.com -> Next.js public website
schoolname.com/portal or a portal link -> Laravel portal
```

Use only if hosting supports the routing cleanly. A visible portal link to a Laravel subdomain is often simpler and safer than trying to merge two apps under one path.

## Commercial Scope

The website add-on can be sold separately from the Laravel portal. The offer should state who owns:

- Next.js website hosting.
- Laravel portal hosting.
- DNS.
- SSL.
- Content writing.
- Photos and media.
- Design customization.
- Ongoing edits.
- Website maintenance.
- Portal support.

## Operational Notes

- The public website can be cached and optimized for marketing pages.
- Laravel should remain the destination for portal login and admissions.
- Do not expose private Laravel admin routes in public website navigation.
- Do not claim the website is built until the separate repo exists and is validated.

## Related Docs

- [Next.js School Website Add-On](nextjs-school-website-add-on.md)
- [Separate Repo Strategy](separate-repo-strategy.md)
- [Support And Maintenance Positioning](../commercial/support-and-maintenance-positioning.md)
