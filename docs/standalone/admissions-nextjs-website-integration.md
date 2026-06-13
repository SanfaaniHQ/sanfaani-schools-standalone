# Next.js Website Admission Integration

A future Next.js school website is an optional separate-repo frontend. Laravel remains the admission engine and source of truth.

Stage 25 chooses a link-only V1 for the future website template. The website should link to Laravel's public admissions pages first:

```text
Apply Now -> /admissions/apply
Check Application -> /admissions/track
```

The secured API flow below is a later integration mode, not the V1 requirement.

## Later API Flow

1. The school administrator creates a `nextjs` admission channel.
2. The portal creates an API key and stores only its SHA-256 hash.
3. The key is kept on the Next.js server, never in browser JavaScript.
4. The server requests `GET /api/public/admissions/config`.
5. The server submits validated form data to `POST /api/public/admissions`.
6. Laravel creates the application, guardian, documents, tracking token, and audit entry.

Requests use:

```http
X-Sanfaani-Admission-Key: sad_example_secret
Origin: https://www.schooldomain.com
```

API access is disabled unless `SANFAANI_ADMISSION_API_ENABLED=true`. An allowed domain should be configured for browser-originated integrations. Server-side integrations that cannot provide the configured origin need an integration policy change rather than weakening the key.

The API does not expose applicant lists, internal notes, documents, or staff actions. Online payment is not required in phase 1.

See `docs/website/admissions-linking-guide.md` and `docs/website/security-and-privacy-boundaries.md` for the Stage 25 separate-repo website strategy.
