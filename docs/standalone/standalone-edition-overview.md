# Standalone School Edition Overview

The Standalone School Edition is for one private school installation. It is not the public SaaS signup flow, not the SaaS billing/customer acquisition product, and not the marketplace package builder as the main user journey.

The expected owner flow is:

1. Install the app on the school's local computer, LAN server, VPS, or cPanel hosting.
2. Run the installer.
3. Create the first school admin.
4. Activate the standalone license.
5. Use the local school dashboard for admin, teacher, result officer, CBT, student, and result workflows.
6. Configure backups or optional sync later.

The app can continue working without internet when the web server and database are available locally, because the local database is the source of truth. This is local-first server deployment support, not full browser offline/PWA support.

Safe default environment values:

```dotenv
SANFAANI_PRODUCT_EDITION=standalone
SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false
SANFAANI_LICENSE_MODE=annual
SANFAANI_STANDALONE_OFFLINE_MODE=local_first
SANFAANI_STANDALONE_SYNC_ENABLED=false
SANFAANI_STANDALONE_SYNC_ENDPOINT=
SANFAANI_STANDALONE_SYNC_TOKEN=
SANFAANI_STANDALONE_BACKUP_SYNC_ENABLED=false
SANFAANI_STANDALONE_PRIVATE_HOMEPAGE_ENABLED=true
SANFAANI_STANDALONE_HIDE_SAAS_SURFACES=true
SANFAANI_STANDALONE_HIDE_MARKETPLACE_SURFACES=true
SANFAANI_STANDALONE_HIDE_DEMO_SURFACES=true
SANFAANI_STANDALONE_HIDE_PLATFORM_MARKETING_SURFACES=true
```

These surface gates keep the standalone home page, navigation, dashboards, and direct routes focused on private school operations. SaaS billing/subscriptions, marketplace demo pages, public demo requests, customer acquisition dashboards, and platform marketing tools should stay hidden unless a maintainer deliberately disables the relevant gate for a controlled internal workflow.

For non-technical schools, Sanfaani or an implementation partner can provide a done-for-you installation service: prepare hosting or a local server, run the installer, create the admin account, activate the license, and hand over usage guidance.

See [Standalone Dashboard Experience](standalone-dashboard-experience.md) for the owner, School Admin, Teacher, Result Officer, setup-checklist, and planned-module behavior.
