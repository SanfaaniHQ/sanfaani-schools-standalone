# Admissions For Schools With An Existing Website

The existing school website remains the public front door while the Laravel portal remains the source of truth.

## Link integration

Add an Apply Now link to:

```text
https://portal.schooldomain.com/admissions/apply
```

This is the simplest and safest phase 1 integration.

## Iframe integration

Create an active `embed` or `existing_website` admission channel in the portal and use:

```html
<iframe
  src="https://portal.schooldomain.com/admissions/embed?channel=main-website"
  width="100%"
  height="900"
  title="School admission application">
</iframe>
```

The form is responsive and records the approved channel name. Applicant lists, internal notes, documents, and administration routes are never exposed in the iframe.

## API integration

API integration is disabled by default. When deliberately enabled, the website sends `X-Sanfaani-Admission-Key`, uses an active hashed key, and must match the configured domain allowlist when one is present. The API returns only an application number, tracking token, and next step.

Start with a link or iframe. Use the API only when the school has a maintained website backend and can protect credentials.
