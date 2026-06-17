# Stage G Readiness

Date: 2026-06-17

## Status

- Stage F roles, permissions, feature control, and authorization behavior are finalized for this branch.
- Stage G production polish is completed for error pages, role/feature screens, school-facing copy cleanup, targeted mobile layout fixes, and handover readiness.
- White-label behavior is preserved: school/platform branding continues to come from the existing branding services and settings.

## Validation Checklist

- Run `composer validate`.
- Run PHP syntax checks for the Stage F services/controllers and any touched translation files.
- Clear local caches with `php artisan config:clear`, `php artisan route:clear`, and `php artisan view:clear`.
- Run the focused Stage F filters listed in the implementation handoff.
- Run `php artisan test --filter=StageGProductionPolishTest`.
- Run the full suite with `php artisan test`.
- Run `npm audit --audit-level=critical`.
- Run `npm run build`.
- Run `git diff --check`.
- Confirm protected files are untouched with `git diff -- .env .env.local public/build.zip database/migrations/2026_05_01_173857_create_result_publications_table.php`.
- Review `git status --short` before packaging or committing.

## Deployment Checklist

- Confirm production `.env` has `APP_ENV=production` and `APP_DEBUG=false`.
- Confirm the production database is backed up before deploying migrations.
- Upload the application files, excluding local-only files and archives.
- Install/update dependencies with Composer using production flags.
- Run database migrations once the new code is in place.
- Clear and rebuild production caches after environment values are confirmed.
- Build frontend assets locally or on the server according to the cPanel deployment process.
- Verify login, dashboard routing, role switch, feature control, role permissions, parent/student dashboards, and 403/404/419/500/503 error pages.

## Handover Checklist

- Share the branch name and commit hash used for deployment.
- Share the validation command results from the deployment package.
- Confirm no protected files were modified.
- Confirm the school branding, logo, colors, and support contact render correctly.
- Confirm school admins know where to manage role permissions and feature controls.
- Confirm rollback steps and latest backup location are available to the operator.

## Known Non-Blockers

- None recorded.
