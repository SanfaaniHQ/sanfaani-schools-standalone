# Future Next.js Repo Checklist

Use this checklist later when creating the separate Next.js website repository. Do not create the repo or these folders inside the Laravel repository during Stage 25.

## Repository

- [ ] Create separate repo, recommended: `sanfaani-school-website-template`.
- [ ] Add README explaining that Laravel remains the source of truth.
- [ ] Add `.env.example` with public-only values.
- [ ] Keep secrets out of the repo.
- [ ] Keep package/build tooling separate from Laravel.

## Suggested Structure

- [ ] `app/`
- [ ] `components/`
- [ ] `lib/`
- [ ] `public/`
- [ ] `content/`
- [ ] `.env.example`
- [ ] `README.md`

## V1 Pages

- [ ] Home.
- [ ] About.
- [ ] Admissions.
- [ ] Academics.
- [ ] Contact.
- [ ] Privacy.
- [ ] Terms.
- [ ] Optional news or announcements placeholder.

## V1 Links

- [ ] Portal Login points to Laravel.
- [ ] Apply Now points to Laravel admissions.
- [ ] Check Application points to Laravel tracking when enabled.
- [ ] Contact School uses public contact details.
- [ ] No private dashboards are exposed.

## Environment Variables

Use public values only:

```env
NEXT_PUBLIC_SCHOOL_NAME=
NEXT_PUBLIC_PORTAL_LOGIN_URL=
NEXT_PUBLIC_ADMISSIONS_URL=
NEXT_PUBLIC_APPLICATION_TRACKING_URL=
NEXT_PUBLIC_CONTACT_EMAIL=
NEXT_PUBLIC_CONTACT_PHONE=
```

## Launch Review

- [ ] DNS/SSL/hosting owner is named.
- [ ] Content owner is named.
- [ ] Design customization scope is approved.
- [ ] Laravel portal URL is final.
- [ ] Admissions URL is final.
- [ ] Tracking URL is final.
- [ ] No secrets or private Laravel data are used.
- [ ] Stage 25 Laravel link contract is referenced.

## Related Docs

- [Separate Repo Strategy](separate-repo-strategy.md)
- [Website-Laravel Link Contract](website-laravel-link-contract.md)
- [Website Deployment Positioning](website-deployment-positioning.md)
