# Staging Known Issues

These issues are known at the staging release candidate boundary. They must be disclosed during handover and should not be described as completed production automation.

## Planned Commercial Work

- Full billing/payment workflow remains planned.
- Trial-to-paid billing conversion remains planned.
- Remote license server sync remains planned.
- Marketplace license sync remains planned.

## Planned Operations Work

- Real update application remains planned.
- Real update download, extraction, code patching, and migration orchestration remain planned.
- Automated restore remains planned.
- Full backup archive creation and external storage orchestration remain planned.
- Deployment automation remains planned.

## Planned Marketplace And White-Label Work

- Marketplace ZIP generation remains planned.
- Marketplace API integration remains planned.
- One-click buyer deployment remains planned.
- White-label domain provisioning remains planned.
- Reseller tooling remains planned.
- Full theme builder remains planned.

## Planned Portal Work

- Full parent portal workflows remain planned where incomplete.
- Full student portal workflows remain planned where incomplete.

## Local Audit Warnings

- Local `.env` may use `APP_ENV=local`; staging should use production-like values.
- Local `.env` may use `APP_DEBUG=true`; staging must use `APP_DEBUG=false`.
- Optional Redis and ZIP PHP extensions may be absent locally.
- `public/build.zip` may exist locally but must not be used as a runtime artifact or marketplace package.
- Staging reviewers should run `php artisan security:audit` with production-style overrides when local env values are not production-like.

## Hosting Compatibility Notes

- cPanel/Namecheap shared hosting may enforce a 1000-byte MySQL/MariaDB key limit and reject wide `utf8mb4` composite indexes.
- Long automatic foreign key names can exceed MySQL's 64-character identifier limit on shared hosting.
- Shared hosting may not provide global Composer; use `php composer.phar` if a local Composer PHAR is installed.
- Shared hosting may use HTTPS GitHub clones when SSH public-key access is not configured.
- Shared hosting may require storage-link alternatives when symlinks are blocked.
- Shared hosting usually needs `sync` or `database` queue mode instead of long-running workers.
- VPS and cloud environments usually avoid the 1000-byte shared-hosting key limit when MySQL/MariaDB is modern and configured properly, but migrations should remain compatible.
- VPS needs Nginx/Apache document root pointing to `public`, PHP extensions, Composer, Node/npm, queue worker, scheduler, permissions, SSL, and Supervisor/systemd.
- Cloud hosting may use managed databases, object storage, dashboard-managed env vars, build commands, platform workers, and platform cron jobs.
- Redis may be unavailable on shared hosting but available on VPS/cloud.
- The ZIP extension is optional for baseline deployment but affects package and backup workflows.
- Queue workers may be unavailable on shared hosting but should run on VPS/cloud where supported.
- File permissions must never make `.env`, logs, backups, private storage, or SQL dumps public.
