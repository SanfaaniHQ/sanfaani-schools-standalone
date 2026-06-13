# Admissions Linking Guide

The future separate Next.js website should send admissions visitors to Laravel. Laravel owns admission records, validation, tracking, documents, review workflow, decisions, and applicant-to-student conversion.

## Recommended V1: Link-Only

Use link-only integration first:

```text
Apply Now -> https://portal.schooldomain.com/admissions/apply
Check Application -> https://portal.schooldomain.com/admissions/track
```

Why this is recommended:

- Lowest risk.
- Fastest to sell and deploy.
- No website API credentials.
- No duplicated validation.
- Laravel keeps the full admissions security boundary.

## Existing Laravel Routes

- `/admissions`
- `/admissions/apply`
- `/admissions/track`
- `/admissions/embed`
- `/api/public/admissions/config`
- `/api/public/admissions`

The API routes are disabled by default unless `SANFAANI_ADMISSION_API_ENABLED=true`.

## Mode 1: Link-Only V1

The website links to Laravel admissions pages. Laravel renders the form, applies throttling, validates fields, stores documents privately, creates the tracking token, and shows the acknowledgement.

Use this first for the future website template.

## Mode 2: Embedded Admissions Later

The website may embed Laravel's admissions form later:

```html
<iframe
  src="https://portal.schooldomain.com/admissions/embed?channel=main-website"
  width="100%"
  height="900"
  title="School admission application">
</iframe>
```

Requirements:

- `SANFAANI_ADMISSION_EMBED_ENABLED=true`.
- Approved admission channel, such as `main-website`.
- Allowed domain configured globally or on the channel where needed.
- `Origin` or `Referer` must match the allowlist when configured.
- Rate limits, honeypot/timing controls, privacy consent, and document rules remain Laravel-owned.

## Mode 3: API-Based Public Admissions Later

The future website may submit to Laravel's public admissions API only after deliberate security review:

- `GET /api/public/admissions/config`
- `POST /api/public/admissions`

Requirements:

- `SANFAANI_ADMISSION_API_ENABLED=true`.
- Active API key created in Laravel.
- Key stored only on the Next.js server, never in browser JavaScript.
- Allowed domain configured for the key/channel when browser-originated requests are used.
- Laravel validation, throttling, file/document policy, and privacy rules remain authoritative.

## Data The Website Must Not Own

- Applicant lists.
- Internal review notes.
- Admission documents.
- Payment records.
- Interview records.
- Status history.
- Applicant-to-student conversion.
- Student records after admission.

## Related Docs

- [Website-Laravel Link Contract](website-laravel-link-contract.md)
- [Security And Privacy Boundaries](security-and-privacy-boundaries.md)
- [Admissions For Existing Websites](../standalone/admissions-for-schools-with-existing-website.md)
- [Admissions Security And Privacy](../standalone/admissions-security-and-privacy.md)
