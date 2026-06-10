# Admissions Security And Privacy

The Laravel portal owns admission records and keeps applicant data private.

## Implemented controls

- Public submission and tracking routes are rate limited.
- The browser form includes a honeypot field and a local timing challenge. When `SANFAANI_ADMISSION_REQUIRE_CAPTCHA=true`, submissions that arrive too quickly or without the timestamp are rejected as the bundled CAPTCHA fallback.
- Applicant lists and administration actions require authenticated school-admin context.
- Every nested application, document, and payment action is checked against the active school.
- Tracking tokens are random and stored only as SHA-256 hashes.
- Public tracking requires the application number plus the tracking token by default. Guardian-phone fallback is disabled unless `SANFAANI_ADMISSION_GUARDIAN_TRACKING_FALLBACK_ENABLED=true`, and when enabled it can also require the applicant date of birth.
- API keys are shown once and stored only as SHA-256 hashes.
- API access is disabled by default. When enabled, requests require `X-Sanfaani-Admission-Key`, keys are hashed at rest, and allowed domains should be configured per key/channel.
- The public API config endpoint returns school, cycle, document, and payment capability metadata only. It does not expose applicants, API keys, document storage disks, staff data, or internal notes.
- Embeds can be disabled with `SANFAANI_ADMISSION_EMBED_ENABLED=false`. When embed domains are configured globally or on a channel, the request `Origin` or `Referer` must match the allowlist and the response sends a restricted `frame-ancestors` policy.
- Documents are limited by file extension/MIME, size, count, and allowlisted document type. They are stored on private disks only; the public disk is rejected.
- Status changes are recorded in immutable application history rows.
- Sensitive staff actions, including status changes, document reviews, document downloads, manual payments, settings changes, channels, API keys, and conversion attempts, are audit logged.
- The form requires explicit privacy consent.
- Public tracking omits guardian data, documents, internal notes, and other applicants.

## Operating requirements

Use HTTPS for any internet-facing installation. Protect backups because they contain applicant data. Give admission access only to authorized staff. Review document retention and privacy notices under applicable law. Rotate or revoke integration keys when website maintainers change.

Captcha is configurable but no third-party captcha provider is bundled in this stage; the current protection is honeypot plus local timing validation. Malware scanning is represented in configuration as a placeholder and is not an active scanner yet, so staff should review uploaded documents before trusting them. Online payment providers, production SMS, and WhatsApp delivery are also not implemented. Email notifications use the existing Laravel mail configuration and can safely fall back to a log mailer.

## Deferred security items

- Full malware scanning or antivirus integration for admission documents.
- Signed temporary links for document previews.
- Third-party CAPTCHA provider integration.
- Server-to-server API policy variants for websites that cannot send a stable origin without weakening browser-origin checks.
