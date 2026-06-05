# Done-For-You Installation Offer

Done-for-you installation is the recommended path for non-technical standalone and marketplace buyers. SaaS buyers do not get code because they use hosted access at `sanfaanischools.online`. Standalone buyers get a private single-school package, and marketplace buyers can buy the package plus installation support.

## Who Should Buy It

- Buyers who use cPanel or shared hosting and are not comfortable setting the document root to `/public`.
- Buyers who cannot run Composer, npm, migrations, or queue/cron setup.
- Schools that want Sanfaani to install the standalone package and hand over a working single-school system.

## What Sanfaani Handles

- Uploading the selected standalone package.
- Confirming the document root points to `/public`.
- Creating the database and configuring safe environment values.
- Enabling the standalone installer and completing the first setup flow.
- Handing over admin access and post-installation notes.

This offer does not claim automated payment or billing setup is complete. Payment gateway configuration remains a deployment task handled according to the buyer's selected provider and support agreement.

Recommended standalone env:

```env
SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_LICENSE_MODE=annual
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false
```
