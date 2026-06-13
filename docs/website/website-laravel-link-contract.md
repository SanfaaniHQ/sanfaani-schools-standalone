# Website-Laravel Link Contract

This contract defines the public links a future separate Next.js school website may use to send visitors into the Laravel portal.

## Source Of Truth

Laravel remains the source of truth for authentication, admissions, school operations, student/staff records, fees, attendance, LMS, CBT, communications, reports, branding, installer, license, updates, backups, and system health.

The website should treat Laravel URLs as external product links, not as data to duplicate.

## Recommended Public Links

| Website action | Laravel target | Notes |
| --- | --- | --- |
| Portal Login | `/login` | Standard school-level login route. |
| Staff Login | `/login` | Use the same portal login unless a deployment config provides a separate target. |
| Parent Login | `/login` | Use only when parent portal access is enabled and communicated honestly. |
| Student Login | `/login` | Use only when student portal access is enabled and communicated honestly. |
| Admin Login | `/admin/login` | For Super Admin/local owner users only; do not expose casually on public marketing pages. |
| Admissions Overview | `/admissions` | Public Laravel admissions overview. |
| Apply Now | `/admissions/apply` | Recommended V1 admissions CTA. |
| Check Application | `/admissions/track` | Use when tracking is enabled. |
| Embed Admissions | `/admissions/embed?channel=main-website` | Later mode only; requires domain/channel review. |
| School Public Page | `/schools/{slug}` or `/s/{slug}` | Existing Laravel public school page. |
| School Admissions Page | `/schools/{slug}/admissions` | Requires public page and admissions visibility. |
| School Contact Page | `/schools/{slug}/contact` | Requires public page contact visibility. |
| School Result Checker | `/schools/{slug}/results` or `/s/{slug}/result-checker` | Use only when result-checker visibility is intentionally enabled. |

## URL Configuration

The future Next.js repo should not hardcode local development URLs. It should read public URL values from its own non-secret environment variables, such as:

```env
NEXT_PUBLIC_PORTAL_LOGIN_URL=
NEXT_PUBLIC_ADMISSIONS_URL=
NEXT_PUBLIC_APPLICATION_TRACKING_URL=
```

## Link-Only V1 Rules

- Use normal links or buttons to Laravel routes.
- Let Laravel render and validate login/admissions pages.
- Do not submit private forms directly from the website in V1.
- Do not store admission records in the website.
- Do not expose internal admin, backup, update, license, system health, or report URLs.

## Later Integration Modes

- Embedded admissions can use the Laravel embed route after domain allowlist review.
- API-based public admissions can use the Laravel public admissions API only when deliberately enabled and secured.
- The website must never receive private dashboards or admin APIs.

## Related Docs

- [Portal Login Linking Guide](portal-login-linking-guide.md)
- [Admissions Linking Guide](admissions-linking-guide.md)
- [Security And Privacy Boundaries](security-and-privacy-boundaries.md)
