# Separate Repo Strategy

The Next.js school website add-on should be built later in a separate repository, not inside `sanfaani-schools-standalone`.

## Recommended Future Repository

Recommended name:

```text
sanfaani-school-website-template
```

Alternative name:

```text
sanfaani-next-school-website
```

## Why Separate

- Keeps the Laravel school portal as the source of truth.
- Avoids mixing Laravel Vite/build setup with a public website app.
- Lets the website be reused across schools.
- Keeps website hosting, domain, content, and design work as a clear commercial add-on.
- Avoids exposing private Laravel code, routes, files, or credentials in a public website project.

## Future Repo Purpose

The future repo should provide a reusable public website template for schools using Sanfaani Schools Standalone. It should display public content and link into Laravel for portal login and admissions.

## Future Repo Structure

For later planning only, the future repo may use:

```text
app/
components/
lib/
public/
content/
.env.example
README.md
```

Do not create these folders in this Laravel repository.

## Future Environment Variables

The future repo should use public, non-secret values only:

```env
NEXT_PUBLIC_SCHOOL_NAME=
NEXT_PUBLIC_PORTAL_LOGIN_URL=
NEXT_PUBLIC_ADMISSIONS_URL=
NEXT_PUBLIC_APPLICATION_TRACKING_URL=
NEXT_PUBLIC_CONTACT_EMAIL=
NEXT_PUBLIC_CONTACT_PHONE=
```

Do not put API keys, Laravel app keys, database credentials, license keys, SMTP passwords, payment secrets, backup paths, or server tokens in public Next.js variables.

## Stage 25 Boundary

Stage 25 documents the strategy and Laravel link contract only. It does not create the Next.js app, install packages, modify Laravel build tooling, or change Laravel behavior.

## Related Docs

- [Future Next.js Repo Checklist](future-nextjs-repo-checklist.md)
- [Website Deployment Positioning](website-deployment-positioning.md)
- [Next.js School Website Add-On](nextjs-school-website-add-on.md)
