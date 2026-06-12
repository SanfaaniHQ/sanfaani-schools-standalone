# School Branding Operations

School Admins manage branding from **School > Branding**.

## Managed Fields

- Display name.
- Login heading and subheading.
- Dashboard heading.
- Primary, secondary, and accent colors.
- Email footer and report footer text.
- Logo and favicon uploads.
- White-label mode when the deployment has the required feature and license entitlement.

## Where Branding Appears

The resolved school brand appears in the school-facing portal shell, dashboard, communication center, LMS, live classes, admissions pages, report preview, finance invoice detail, and emails that already use the branding layout.

Teacher, accountant, result officer, student, and parent users may see the school brand where they already have safe portal access. They do not manage branding by default.

## Safety Rules

- Use only school-owned logos and approved colors.
- Upload only PNG, JPG, JPEG, WEBP, or ICO assets.
- Do not upload SVG or executable files.
- Do not paste private storage paths or server paths.
- Do not use branding text to publish secrets, tokens, provider credentials, private finance notes, or admissions documents.
- Keep cross-school assets separate.

## Audit Trail

Branding changes are recorded through audit logs with safe metadata: school id, setting id, changed field names, and boolean flags for logo/color/white-label changes. Raw file data and private file paths are not audited.

## Not Included

Branding management does not create a public website, custom domain, DNS record, SSL certificate, theme builder, drag-and-drop page editor, provider branding automation, or cross-school theme marketplace.
