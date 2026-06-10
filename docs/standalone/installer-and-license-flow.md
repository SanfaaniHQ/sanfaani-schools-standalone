# Installer And License Flow

Standalone installations should begin with the installer. The installer prepares a single-school installation and creates the owner/admin path without relying on public SaaS signup.

Recommended flow:

1. Copy `.env.example` and fill local database, mail, app URL, and license values.
2. Confirm `SANFAANI_PRODUCT_EDITION=standalone`.
3. Confirm `SANFAANI_DEPLOYMENT_MODE=single_school`.
4. Confirm `SANFAANI_INSTALLER_ENABLED=true`.
5. Confirm `SANFAANI_INSTALLED=false` before first install.
6. Run the installer.
7. Create the first school admin.
8. Create or confirm the local school profile.
9. Activate the license.
10. Use the school dashboard.

License mode defaults to `annual` for standalone. Lifetime or managed contract modes can be configured when the commercial agreement supports them.

SaaS billing, public customer signup, demo request funnels, and marketplace live demo links are not part of the standalone main flow. They should remain hidden, demoted, or clearly documented as non-primary when this repository is configured for one private school.

Full browser offline/PWA support is not complete yet. The offline foundation is that the app and database run locally.
