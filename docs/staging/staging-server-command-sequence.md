# Staging Server Command Sequence

Run this sequence only on the intended staging host or controlled staging build runner. Replace placeholders with staging values outside Git.

## 1. Confirm Target

```bash
pwd
git status
git branch --show-current
git log --oneline -5
```

Expected operator checks:

- Repository is `SanfaaniHQ/sanfaani-schools`.
- Branch is the approved staging branch or release candidate.
- The checkout is not detached.
- `public/build.zip` is not used as an artifact.
- `database/migrations/2026_05_01_173857_create_result_publications_table.php` is not changed by this deployment.

## 2. Update Source

```bash
git fetch origin
git checkout feature/v7-cbt-localization-hardening
git pull --ff-only origin feature/v7-cbt-localization-hardening
```

If the deployment uses a tagged release candidate, replace the branch checkout with the approved tag or commit.

## 3. Install PHP Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

Do not commit `vendor`. If the host cannot run Composer, deploy dependencies through an approved build handoff.

## 4. Build Frontend Assets

The npm build step for this repository is `npm run build`.

```bash
npm ci
npm run build
```

Deploy reviewed `public/build` assets only. Do not package or deploy `public/build.zip`.

## 5. Configure Staging Environment

Copy reviewed values from the relevant example:

- `docs/staging/staging-env-saas.example.md`
- `docs/staging/staging-env-single-school.example.md`
- `docs/staging/staging-env-managed.example.md`

Then run only on the staging host if a key has not already been generated:

```bash
php artisan key:generate
```

Never commit the generated `.env`.

## 6. Prepare Database

Preview migrations first:

```bash
php artisan migrate --pretend
```

After backup approval and go/no-go for the staging target:

```bash
php artisan migrate --force
```

Do not edit existing migrations during staging execution.

## 7. Storage And Cache

```bash
php artisan storage:link
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan view:cache
```

Use `php artisan route:cache` only after route-cache compatibility is confirmed for the target.

## 8. Required Validation Commands

Run in this order and record the actual result:

```bash
php artisan test
php artisan route:list
php artisan staging:check-readiness
php artisan deployment:check-readiness
php artisan performance:audit
php artisan release:check-readiness
php artisan marketplace:validate-package
git diff --check
```

Run security audit with production-style environment values.

Bash:

```bash
APP_ENV=production APP_DEBUG=false php artisan security:audit
```

PowerShell:

```powershell
$env:APP_ENV="production"; $env:APP_DEBUG="false"; php artisan security:audit
Remove-Item Env:\APP_ENV
Remove-Item Env:\APP_DEBUG
```

`php artisan marketplace:validate-package` validates package readiness only. It must not create a ZIP.

## 9. Post-Deploy Verification

Complete:

- `docs/staging/staging-post-deploy-verification.md`
- `docs/staging/staging-first-login-checklist.md`
- `docs/staging/staging-signoff-report-template.md`

Do not record staging as accepted until the signoff owner reviews the actual command output and mode checks.
