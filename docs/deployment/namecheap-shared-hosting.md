# Namecheap Shared Hosting

This guide is for single-school or small managed deployments on Namecheap shared hosting.

## Current Support Level

The app has an installer foundation and shared-hosting-aware requirements checks. It does not yet include a full marketplace package installer or automated update manager.

## Deployment Notes

- Upload application files outside public web root where possible.
- Point the domain document root to `public/`.
- Configure `.env` from `.env.example`.
- Use `APP_ENV=production` and `APP_DEBUG=false`.
- Confirm `storage` and `bootstrap/cache` are writable.
- Configure queue behavior according to hosting limits.
- Run migrations through available hosting tooling or safe CLI access.

## Installer Path

If using single-school mode before installation:

- `SANFAANI_DEPLOYMENT_MODE=single_school`
- `SANFAANI_INSTALLER_ENABLED=true`
- `SANFAANI_INSTALLED=false`

After installation, `storage/app/installed.lock` blocks reinstall.

## Planned

Automated update and backup managers are planned, not currently complete.
