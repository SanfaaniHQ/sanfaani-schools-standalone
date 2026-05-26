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
