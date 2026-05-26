# White-Label Buyer Checklist

White-label packaging is allowed only where license terms permit it.

## Pre-Sale

- Confirm white-label license mode.
- Confirm branding scope: name, logo, colors, public pages, support identity, and documentation wording.
- Confirm who owns hosting, domain, SMTP, and backups.
- Confirm update and support responsibilities.

## Package Preparation

- Use the validated marketplace package as the base.
- Do not include Sanfaani production secrets or local files.
- Replace branding only through approved configuration or buyer-provided assets.
- Keep license validation and entitlement checks enabled unless contract terms specify otherwise.

## Handover

- Provide buyer installation checklist.
- Provide branded admin/support notes where contracted.
- Provide update and backup boundaries.
- Clearly mark planned features as planned.

## Branding Foundation

- Confirm `SANFAANI_BRANDING_ENABLED=true`.
- Confirm `SANFAANI_WHITE_LABEL_ENABLED` matches the purchased license.
- Upload only PNG, JPG, WEBP, or ICO assets.
- Keep logo/favicon files under safe public branding storage.
- Confirm email footer and report footer text are buyer-approved.
- Do not expose private storage paths, `.env` values, license keys, or hosting credentials in branding copy.
