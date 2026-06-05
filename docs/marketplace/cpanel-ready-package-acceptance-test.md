# cPanel-Ready Package Acceptance Test

This acceptance test proves that the `cpanel_ready` package behaves like a standalone single-school product for marketplace and shared-hosting buyers. It does not claim that hosting setup is fully automatic.

## Package Generation

On the build machine, prepare the package:

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan marketplace:build-package --profile=cpanel_ready --dry-run
php artisan marketplace:build-package --profile=cpanel_ready
```

Record the ZIP path and sibling manifest path from the command output.

## Archive Inspection

Run:

```bash
php artisan marketplace:inspect-package storage/app/marketplace-packages/package-name.zip
```

Acceptance criteria:

- The command exits successfully.
- `.env` is excluded.
- `public/build.zip` is excluded.
- `.git` is excluded.
- `node_modules` is excluded.
- `vendor/` is present when Composer dependencies were available during build.
- `public/build/manifest.json` is present when frontend assets were available during build.
- Standalone QA, installer acceptance, and support docs are present.
- The sibling package manifest exists beside builder-generated release ZIPs.

## Hosting Acceptance

In the target cPanel or shared-hosting account:

- Upload and extract the approved ZIP outside public web access where possible.
- Set the domain or subdomain document root to the application `/public` directory.
- Confirm direct access to parent folders, `.env`, `storage`, and `vendor` is not publicly browsable.
- Confirm PHP version and required extensions match the installer requirement screen.
- Confirm `storage` and `bootstrap/cache` are writable.

## Database Acceptance

Create the database and user in cPanel or the hosting panel before opening `/install`.

Required values:

- `DB_CONNECTION=mysql`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

Use credentials created for this standalone school only. Do not reuse SaaS, staging, demo, or another school's credentials.

## License And Installer Mode

The standalone package should use:

```env
SANFAANI_DEPLOYMENT_MODE=single_school
SANFAANI_LICENSE_MODE=annual
SANFAANI_INSTALLER_ENABLED=true
SANFAANI_INSTALLED=false
```

After installation is complete, the installation lock and final environment state should prevent casual reuse of `/install`.

## `/install` Flow

Open `https://your-domain.example/install` and verify:

1. Welcome page confirms standalone setup intent.
2. Requirements page reports PHP and extension status.
3. Permissions page reports writable folders.
4. Database page can read the configured database connection.
5. Environment and app-key pages show readiness.
6. Migrations page is reviewed before any schema action.
7. Admin, school, SMTP, review, and complete steps can be followed.

If any hosting, database, or permissions step is unclear, stop and use Sanfaani done-for-you setup or the hosting provider's support path.

## Non-Technical Buyer Boundaries

Non-technical buyers should not run Composer, npm, Git, database imports, migrations, or server-path changes unless Sanfaani or the hosting provider gives explicit guidance. They should not assume cPanel creates databases, rewrites document roots, or completes installation automatically.
