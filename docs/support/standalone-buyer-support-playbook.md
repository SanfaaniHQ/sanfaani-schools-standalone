# Standalone Buyer Support Playbook

This playbook helps support teams guide technical marketplace buyers, non-technical standalone school buyers, and customers who pay Sanfaani for done-for-you installation.

## First Triage

Confirm the buyer type:

- Technical marketplace buyer: can run commands, inspect ZIPs, configure hosting, and follow Laravel deployment notes.
- Non-technical standalone buyer: owns a single-school license but needs a hosting provider or Sanfaani to perform setup.
- Done-for-you buyer: paid Sanfaani to upload, configure, verify, and hand over the standalone school installation.

Confirm the package type and build evidence:

- `cpanel_ready` package path.
- Sibling builder manifest path.
- Output from `php artisan marketplace:inspect-package`.
- Confirmation that `.env`, `public/build.zip`, `.git`, and `node_modules` are excluded.
- Confirmation that `vendor/` and `public/build/manifest.json` are present when available.

## Safe Support Questions

Ask for:

- Domain or subdomain.
- Hosting provider and cPanel access method.
- PHP version and extension screen.
- Document root setting, which must point to `/public`.
- Database name, database username, host, and port.
- Whether `storage` and `bootstrap/cache` are writable.
- `/install` step where the buyer is blocked.

Do not ask buyers to paste real passwords, app keys, private `.env` files, SQL dumps, or full server backups into casual support channels.

## Required Standalone Mode

For standalone annual buyers, confirm:

```env
SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_LICENSE_MODE=annual
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false
```

After successful installation, handover notes should record how the installation lock and final installed state were confirmed.

## Done-For-You Setup Scope

Sanfaani done-for-you setup may include:

- Uploading the approved `cpanel_ready` package.
- Pointing the domain document root to `/public` when the host allows it.
- Creating or verifying the database and database user.
- Creating `.env` from a safe template on the hosting account.
- Verifying `/install`, owner admin setup, school setup, SMTP intent, review, and completion.
- Recording handover notes and support next steps.

This service does not mean every host can be configured automatically. cPanel settings, DNS, PHP extensions, database creation, file permissions, cron, and SMTP depend on the buyer's hosting provider.

## What Non-Technical Buyers Should Do

- Choose done-for-you setup when they are not comfortable with hosting controls.
- Provide approved hosting access through the agreed support channel.
- Keep database and hosting credentials private outside approved handover.
- Wait for support before changing `/public`, `.env`, or database settings.
- Use `/install` only after support confirms the host is ready.

## What Non-Technical Buyers Should Not Do

- Do not upload `.env` from another environment.
- Do not expose the project root as the public document root.
- Do not run Composer, npm, Git, migrations, or database imports without guidance.
- Do not create or upload `public/build.zip`.
- Do not edit database credentials by guesswork.
- Do not delete the installation lock to rerun setup on a live school.

## Escalation

Escalate to engineering when package inspection fails, `/install` behaves inconsistently, the install lock state is unclear, migrations report unexpected errors, license mode does not match `single_school` and `annual`, or a hosting provider requires a non-standard public-folder mapping.
