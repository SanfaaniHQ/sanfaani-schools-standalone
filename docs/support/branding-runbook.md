# Branding Runbook

## Purpose

Use this runbook when supporting school branding, white-label settings, logo/favicon uploads, colors, login wording, dashboard heading, report footer, and email footer.

## Access

School Admins can manage branding for the active school where the feature is enabled. Super Admin or local owner users may review branding diagnostics and license entitlement boundaries. Other roles may see branding but do not manage it by default.

## Normal Workflow

1. Confirm the active school and branding permission.
2. Update display name, wording, colors, footer text, logo, and favicon from School > Branding.
3. Upload only approved PNG, JPG, JPEG, WEBP, or ICO assets.
4. Confirm branding appears on school-facing portal surfaces, supported reports, communication center, LMS, live classes, admissions, and invoice detail where a school context is known.
5. Confirm white-label mode only when the deployment has the required feature and entitlement.

## Common Issues

- Wrong active school context.
- Unsupported asset type or oversized file.
- Logo stored or referenced from an unsafe path.
- White-label expectation exceeds license entitlement.
- School expects a public website, DNS provisioning, SSL automation, or theme builder.
- Cached browser view shows old assets.

## First Checks

- Confirm school context and School Admin permission.
- Check asset type, size, and upload error message.
- Check whether the school has white-label entitlement.
- Confirm the affected surface actually uses resolved school branding.
- Check public storage/link setup when image files do not render.

## Safe Commands And UI Checks

```bash
php artisan route:list
php artisan standalone:status
php artisan deployment:check-readiness
```

Use School > Branding, report preview, invoice detail, login page, dashboard, and communication/LMS/live-class pages as primary checks.

## What Support Should Not Do

- Do not upload SVG, executable files, raw image data, private storage paths, or server paths.
- Do not paste secrets, provider credentials, private finance notes, or admissions documents into branding text.
- Do not promise Next.js public website integration, DNS/domain provisioning, SSL automation, full theme builder, drag-and-drop page builder, or cross-school theme sharing.
- Do not apply another school's assets to the active school.

## Escalation Points

Escalate when cross-school assets appear, unsafe files are accepted, white-label entitlement checks are wrong, branding leaks private paths, or school-facing pages cannot render approved assets after storage is verified.

## Data And Privacy Warnings

Branding text appears in visible school surfaces. Keep private contact data, credentials, internal notes, and student information out of branding fields.

## Backup And Security Reminders

Back up branding settings and uploaded assets before migration, restore, or update work. Confirm storage link and file permissions after restore.

## Related Docs

- [Branding White-label Consolidation](../standalone/branding-white-label-consolidation.md)
- [School Branding Operations](../school-operations/branding.md)
- [Branding Setup Guide](../white-label/branding-setup-guide.md)
- [Branding Asset Guidelines](../white-label/branding-asset-guidelines.md)
