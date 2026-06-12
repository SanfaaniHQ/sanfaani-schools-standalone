# Branding / White Label Consolidation

Stage 19 consolidates Sanfaani Schools Standalone branding around the existing `branding_settings` foundation. It does not add a new theme engine, website builder, DNS workflow, or public Next.js school website.

## What Is Available

- School-scoped display name, logo, favicon, primary color, secondary color, accent color, login wording, dashboard heading, email footer, and report footer settings.
- Safe logo and favicon uploads through the existing branding asset service.
- Effective branding resolution for school-facing portal surfaces when a school context is known.
- Branding visibility on dashboard, sidebar/header, communication center, LMS, live classes, admissions pages, report preview, and finance invoice detail.
- Audit logging for branding updates, logo updates, color updates, and white-label boundary changes.
- Standalone dashboard readiness and planned-module status for branding and white-label consolidation.

## White-label Boundary

School-facing screens can emphasize the school's identity. Powered-by Sanfaani wording can remain where commercially appropriate, especially for standard standalone deployments.

Internal support, installation owner, platform, managed support, diagnostics, license, backup, update, and administrative areas may retain Sanfaani identity unless a licensed white-label deployment explicitly supports broader replacement.

## Upload Security

Branding uploads use approved public branding storage only. School assets are stored under `branding/schools/{school_id}`.

Allowed asset types are PNG, JPG, JPEG, WEBP, and ICO. SVG, executable files, private storage paths, `.env` paths, path traversal, and raw image data in the database are not allowed.

## Cross-school Isolation

School branding is resolved for the active school context only. A school admin can update only the active school's branding settings. Branding assets and settings are not shared across schools.

## Deferred Work

The following remain outside this stage:

- Next.js public school website.
- DNS/domain provisioning.
- SSL automation.
- Full custom theme builder.
- Drag-and-drop page builder.
- Email provider branding automation.
- Advanced PDF redesign beyond existing report hooks.
- Cross-school theme sharing.
