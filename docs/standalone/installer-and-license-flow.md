# Installer And License Flow

> **Current behavior:** The installer has no license step, and standalone installations require no key. License enforcement and activation UI are temporarily dormant. See [Temporary License Disablement](../licensing/temporary-license-disablement.md).

Standalone installations should begin with the installer. The installer prepares a single-school installation and creates the owner/admin path without relying on public SaaS signup.

Recommended flow:

1. Copy `.env.example` and fill local database, mail, and app URL values.
2. Confirm `SANFAANI_PRODUCT_EDITION=standalone`.
3. Confirm `SANFAANI_DEPLOYMENT_MODE=single_school`.
4. Confirm `SANFAANI_INSTALLER_ENABLED=true`.
5. Confirm `SANFAANI_INSTALLED=false` before first install.
6. Run the installer.
7. Create the first school admin.
8. Create or confirm the local school profile.
9. Use the school dashboard.

License mode defaults to `annual` for standalone. Lifetime or managed contract modes can be configured when the commercial agreement supports them.

The installer and license foundation now include Stage 21 final hardening. Operators can review support-safe diagnostics for PHP, extensions, writable folders, database connectivity, migrations, app key presence, queue/cache/session settings, mail readiness, scheduler readiness, backup metadata, update package review, local license status, and entitlement visibility. These diagnostics show configured/missing/status values only; they must not reveal app keys, database credentials, mail passwords, license keys, raw `.env` values, sync tokens, private backup paths, or absolute server paths.

License activation remains local. Submitted keys are format-validated, hashed, masked in the UI, and audited with safe metadata. The existing server-client foundation does not contact a real external license server yet.

SaaS billing, public customer signup, demo request funnels, and marketplace live demo links are not part of the standalone main flow. They should remain hidden, demoted, or clearly documented as non-primary when this repository is configured for one private school.

Full browser offline/PWA support is not complete yet. The offline foundation is that the app and database run locally.
