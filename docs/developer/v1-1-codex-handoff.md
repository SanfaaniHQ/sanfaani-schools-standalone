# Sanfaani Schools V1.1 Codex Handoff

Last updated: 2026-05-05

## Continuation Note (Cursor Session)

- Support thread workflow is now implemented with admin and school routes/controllers/views.
- Teacher workspace now includes a dedicated "My Assigned Classes and Subjects" page.
- Support status/assignment/close endpoints now use PATCH semantics.
- See `docs/developer/v1-1-completion-audit.md` for the latest full completion matrix.

## 1. Current Branch

- Branch: `codex/v1-1-architecture-improvements`
- Base commit: `71f226c Fix shared hosting migration indexes`

## 2. Current Git Status Summary

- Working tree contains many uncommitted V1.1 changes from the interrupted Codex session.
- No commit has been made for the V1.1 work yet.
- Do not reset or discard changes.
- Untracked files include new controllers, models, services, migrations, middleware, and Blade views.

## 3. Files Already Changed Before This Resume

Modified tracked files include:

- `.env.example`
- `routes/web.php`
- `bootstrap/app.php`
- `config/sanfaani.php`
- `app/Providers/AppServiceProvider.php`
- Controllers under `app/Http/Controllers/Admin`, `Auth`, `School`, and `DashboardController.php`
- Models: `User`, `School`, `Subject`, `SchoolClass`, `AcademicSession`, `Term`, `Student`, `AuditLog`
- Services: `AuditLogService`, `CurrentSchoolService`, `PlatformSettingService`
- Blade views under `resources/views/admin`, `resources/views/layouts`, and `resources/views/school`

New untracked files include:

- `app/Http/Controllers/Admin/MailSettingController.php`
- `app/Http/Controllers/Admin/PaymentGatewaySettingController.php`
- `app/Http/Controllers/ChooseWorkspaceController.php`
- `app/Http/Controllers/Public/ResultCheckerPaymentController.php`
- `app/Http/Controllers/School/ClassUploadController.php`
- `app/Http/Controllers/School/StudentElectiveSubjectController.php`
- `app/Http/Controllers/School/SubjectAssignmentController.php`
- `app/Http/Controllers/School/SubjectUploadController.php`
- `app/Http/Middleware/IdleTimeoutMiddleware.php`
- New V1.1 models for assignments, settings, onboarding, language, and workspace roles
- New V1.1 services for CSV import, mail settings, payment settings, onboarding, and workspaces
- Migrations `2026_05_05_000001` through `2026_05_05_000008`
- New Blade views for mail settings, payment settings, workspace selection, uploads, next actions, and subject assignments

## 4. V1.1 Parts That Appear Partially or Mostly Complete

- Subject-to-class assignment migration/model/controller/views/routes are partially implemented.
- Student elective subject migration/model/controller and Student 360 section are partially implemented.
- Class/subject CSV upload controllers/views/routes are partially implemented.
- Safe archive/delete/search for classes and subjects is partially implemented.
- Idle timeout middleware/config/platform setting field is partially implemented.
- Support access role context, continue, stop, and banner are partially implemented.
- Multi-school/multi-role user workspace service and choose-workspace page are partially implemented.
- Forgot password controller now catches mail failures and returns a safe response.
- Subscription assignment UI has been rewritten as a guided form.
- Audit log tags/search fields are partially implemented.
- Product update ZIP manifest/checksum validation is partially implemented.
- Payment gateway settings and mail settings foundations are partially implemented.
- Onboarding progress table/service/dashboard cards are partially implemented.
- Language preference table and class/subject edit fields are partially implemented.

## 5. V1.1 Parts Incomplete

- Continue inspecting syntax and route model bindings for all new files.
- Need teacher assignment/result workflow foundation.
- Need school public page / dedicated result checker foundation.
- Need support thread/message foundation.
- Need website settings foundation.
- Need safe export/foundation features where practical.
- Need docs updates across README, CHANGELOG, admin/user/developer/security/testing/payment/notification/marketplace docs.
- Need run migrations/build/tests after fixing boot/runtime errors.
- Need commit and push only after checks are acceptable.

## 6. Continue From First

1. Fix boot-time database access so `php artisan route:list` and `php artisan migrate:status` can run when MySQL is down.
2. Complete the mandatory inspection by reading changed and new files.
3. Run PHP syntax checks and route listing.
4. Continue with incomplete safe V1.1 foundations without duplicating existing partial work.

## 7. Risks Found

- Local MySQL is currently not reachable: `SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it`.
- The current partial boot code makes missing DB connectivity break every Artisan command.
- Large number of untracked files means a future session must not assume files are committed.
- Need verify migrations are Namecheap-safe before final commit.
- Need ensure no `.env`, `node_modules`, logs, uploads, dumps, backups, or `build.zip` are staged.

## 8. Commands/Tests Run

- `git status`
- `git branch -vv`
- `git log --oneline -5`
- `git diff --stat`
- `git diff --name-only`
- `git diff -- . ':!resources/views/**/*.blade.php'`
- `php artisan migrate:status` failed due MySQL connection refusal during `AppServiceProvider` boot.
- `php artisan route:list` failed due MySQL connection refusal during `AppServiceProvider` boot.
- `php -l app\Services\MailSettingService.php`
- `php -l app\Providers\AppServiceProvider.php`
- `php -l app\Http\Middleware\IdleTimeoutMiddleware.php`
- `php -l app\Http\Controllers\School\SubjectAssignmentController.php`
- `php artisan route:list` now succeeds after boot guard fixes.

## 9. Current Implementation Progress

- Inspection phase started.
- Handoff file created.
- Boot-time mail settings lookup now uses `MailSettingService::tableIsReady()` and no longer fails Artisan commands when the database is unavailable.
- `IdleTimeoutMiddleware` now guards the platform settings table lookup against missing database connectivity.
- `config/sanfaani.php` restored platform/company/support defaults while keeping `SANFAANI_IDLE_TIMEOUT_MINUTES`.
- Student elective subjects now use soft deletes.
- Subject assignment create/update now blocks duplicate active assignments for the same subject, class/general scope, session, and term.

## 10. Next Continuation Instruction

If another Codex session resumes:

1. Read this file first.
2. Do not reset or discard changes.
3. Continue from teacher assignment/result workflow foundation unless the current working tree shows newer completed work.
4. Run `php artisan route:list` and `php artisan migrate:status` after any boot/runtime fix.
5. Continue the incomplete phases in order, updating this file after each major phase.
6. Commit and push only after syntax, migration, route, build, and test checks are acceptable.
