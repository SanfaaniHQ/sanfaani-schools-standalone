# Sanfaani Schools Testing, Debugging, CI/CD, and Safe Deployment Protocol

This runbook is the operational contract for testing and shipping Sanfaani Schools to Namecheap shared hosting without breaking production.

## 1. Production Guardrails

Sanfaani Schools is a live multi-tenant SaaS. The production database name is locked:

```text
sanfaani_schools
```

Never run these commands against production:

```bash
php artisan migrate:fresh
php artisan migrate:refresh
php artisan db:wipe
php artisan db:seed
```

Use destructive database resets only with `APP_ENV=testing` and an isolated test database.

## 2. Environment Lock

| Component | Project Standard |
| --- | --- |
| PHP | 8.3.x, matching Namecheap PHP selector |
| Laravel | 13.x, from `composer.json` |
| MySQL | 8.0+ in production |
| Test DB | SQLite in memory from `phpunit.xml`, or isolated MySQL when explicitly configured |
| Node | 22.x preferred, or Node >= 20.19, because Vite 8 requires it |
| Composer | 2.6+ |
| Queue | Database or sync locally; cPanel cron for shared hosting |
| Assets | Build locally or in CI; do not run npm on Namecheap |

Required environment files:

| File | Rule |
| --- | --- |
| `.env` | Local only; never commit |
| `.env.testing` | Optional when not using `phpunit.xml` SQLite defaults |
| `.env.staging` | Optional staging mirror; never commit secrets |
| `.env.production` | Never commit; keep in cPanel, GitHub Secrets, or password manager |

Production flags:

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=database
```

## 3. Namecheap Shared Hosting Constraints

| Constraint | Required Mitigation |
| --- | --- |
| No persistent process manager | Use cPanel Cron for short queue runs |
| npm may be unavailable | Upload CI-built `public/build` |
| Composer may be restricted | Upload CI/local-built `vendor` when needed |
| Limited SSH | Keep artisan commands non-interactive |
| No Redis likely | Use database queue/cache or file cache |
| Storage permissions drift | Reapply `chmod -R 775 storage bootstrap/cache` after deploy |
| Upload limits | Verify `upload_max_filesize` >= 10M |

Cron queue command for shared hosting:

```bash
php artisan queue:work --queue=mail,exports,default --sleep=3 --tries=3 --timeout=60 --stop-when-empty
```

## 4. Test Suite Architecture

| Layer | Tool | Scope | Gate |
| --- | --- | --- | --- |
| Unit | PHPUnit | Models, services, grading, policy helpers | Required in CI |
| Feature | PHPUnit | Routes, controllers, middleware, form requests | Required in CI |
| Browser | Laravel Dusk, future | Login, dashboards, result workflow | Required before major release |
| Accessibility | axe-core with browser tests, future | WCAG 2.2 AA primary surfaces | Required before major release |
| Performance | Query logging, Lighthouse CI, future | Result grids, dashboard, login | Required before major release |
| Security | `composer audit` | Dependency vulnerabilities | Required in CI |

Current CI gate:

```bash
composer install --prefer-dist --no-interaction
npm ci
npm run build
php artisan view:cache
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
composer audit --locked --abandoned=report
```

## 5. Mandatory Regression Matrix

### Authentication and Access Control

- Valid login redirects to role-correct dashboard.
- Invalid login returns a generic error.
- Inactive users cannot authenticate.
- Unauthenticated dashboard requests redirect to login.
- School users cannot access records from another `school_id`.
- Teachers cannot access admin routes.
- Super admins cannot leak school-only menus outside support mode.

### Role-Based Navigation

- Super Admin sees only platform modules.
- School Admin sees only school modules.
- Result Officer sees only result workspace, upload, review, publishing, and permitted academic tools.
- Teacher sees only assigned classes, subjects, students, communication, and result entry.
- Sidebar and command palette both honor feature flags.
- HTTP endpoints still return 403 even when links are hidden.

### Language and Localization

- Arabic sets `dir="rtl"` in all master layouts.
- English, Arabic, French, Yoruba, and Hausa files load without missing key fallback on primary surfaces.
- Language switcher works on login before authentication.
- Preference persists in session and user profile when authenticated.
- Validation errors use the selected locale.
- Date formatting follows selected locale where implemented.

### Student 360

- Profile loads only enrollment-aware subjects.
- Quick actions respect permissions.
- Result tab keeps `session`, `term`, and `result_type` query state.
- Guardian contact visibility is permission-aware.
- Missing result alerts appear when required scores are absent.
- Print/download links do not expose another school's student.

### Result Workspace

- Subjects resolve from class assignments, electives, enrollment history, teacher mappings, and existing scoped result records.
- Global subject lists never leak into the grid.
- CA and Exam validate against subject max scores.
- Total, grade, pass/fail, and remarks recalculate from authoritative grading logic.
- Teachers cannot approve, publish, edit locked, approved, or published results.
- Result officers can review and return only within granted scope.
- Bulk operations are transaction-safe and audit-logged.
- Audit history records old and new values for score changes.
- Audit history loads lazily, not in the initial grid payload.
- Published results are visible immediately through scratch-card checking.
- A 2,000 row result grid target is under 2 seconds with fewer than 10 main queries.

### Communication and Notifications

- Support ticket creation notifies school users.
- Escalation notifies super admins.
- School SMTP overrides global fallback when configured.
- Queue jobs do not block the HTTP response.
- Student 360 communication history displays delivered and failed states.
- Failed emails can be retried by authorized users only.

### Scratch Cards and Public Checker

- Scratch batches generate unique PINs within the school context.
- `StudentTransactionalEmailRequested` receives an `App\Models\School` model.
- Public checker validates PIN and admission/registration number.
- Draft results are hidden from public checking.
- Published results are visible.
- Failed attempts are rate limited.
- Manual transfer proof creates an admin review notification.

### Subscriptions and Payments

- Plan selection changes feature access without data loss.
- Expired plans downgrade capabilities gracefully.
- Payment webhooks are idempotent.
- Scratch-card purchase records remain school-scoped.

### Global Search

- Debounce waits 300ms.
- Non-super-admin results are scoped by `school_id`.
- Pagination returns 20 results per page.
- Empty states are contextual and translated.

### Public School Website

- Each school page uses its own slug, logo, colors, hero, gallery, and contact details.
- Public result checker embeds in school page context.
- Gallery uploads are resized or optimized.
- School branding never affects other tenants.

### Backup and Security

- Backup generation is queued.
- Backup links expire.
- Non-admin users receive 403.
- Backups exclude `.env`, `.git`, logs, and server-sensitive files.

### Responsive and Accessibility

- Mobile sidebar collapses into a drawer.
- Tables use horizontal scroll where full fidelity matters.
- Result grid keeps sticky headers and first column on larger screens.
- Focus indicators are visible.
- Normal text contrast is at least 4.5:1.
- Images have alt text.
- Status badges have accessible labels when status meaning is not already text-visible.

## 6. Demo Data Protocol

Create `DemoDataSeeder` only for testing and staging. It must never be called by the default production seeder.

Target structure:

```text
DemoDataSeeder
├── SuperAdminSeeder
├── SchoolSeeder
├── SessionTermSeeder
├── UserRoleSeeder
├── ClassSubjectSeeder
├── StudentSeeder
├── EnrollmentSeeder
├── ResultSeeder
├── ScratchCardSeeder
├── SubscriptionSeeder
├── SupportTicketSeeder
└── CommunicationLogSeeder
```

Demo dataset requirements:

- 3 schools: Islamic, secular, and Madrasah.
- 500 students distributed across schools and classes.
- Nigerian names across Hausa, Yoruba, Igbo, and mixed contexts.
- JSS1 to SS3 classes, Islamic studies electives, and secular subjects.
- Sessions `2024/2025` and `2025/2026`, with three terms each.
- Results: 40 percent draft, 30 percent submitted, 20 percent approved, 10 percent published.
- Some incomplete scores for missing-result alerts.
- 1000 scratch cards, with 200 used.
- One Arabic/RTL configured school.
- One expired subscription for feature downgrade testing.

Seeder guard:

```php
if (app()->environment('production')) {
    throw new RuntimeException('DemoDataSeeder must not run in production.');
}
```

## 7. Debugging Runbook

Follow this order when debugging. Do not guess.

| Step | Command | Purpose |
| --- | --- | --- |
| 1 | `php artisan config:clear` | Remove stale config |
| 2 | `php artisan cache:clear` | Remove stale cache |
| 3 | `composer dump-autoload` | Fix class map issues |
| 4 | `php artisan migrate:fresh --seed --env=testing` | Reset only a test database |
| 5 | `php artisan test --filter=FailingTestName` | Isolate the failure |
| 6 | Inspect `storage/logs/laravel.log` | Read the stack trace |
| 7 | `php artisan route:list --path=results` | Confirm route bindings |
| 8 | Use query logging locally | Detect N+1 and missing indexes |

Common symptoms:

| Symptom | Likely Cause | Fix |
| --- | --- | --- |
| Sidebar shows all menus | Role context or feature gate not applied | Add role-aware view data and server middleware |
| RTL does not flip | Layout missing `dir` binding | Use `dir="{{ $isRtl ? 'rtl' : 'ltr' }}"` |
| Result grid loads all subjects | Query falls back to global subjects | Scope to enrollment and assignments |
| Text overlaps | Missing `min-w-0`, wrapping, or scroll container | Add responsive constraints and `overflow-x-auto` |
| Dark mode unreadable | Hardcoded colors | Replace with design tokens or dark variants |
| Scratch card type error | Event factory uses null school relation | Resolve school by relation, batch fallback, or `school_id` |
| Slow result grid | Missing eager loads or indexes | Add eager loading, pagination, and composite indexes |
| Translation fallback | Missing key in one language | Add key to all five language files |

Bug fix standard:

1. Write a failing test that reproduces the bug.
2. Apply the smallest safe fix.
3. Run the focused test.
4. Run the relevant feature suite.
5. Commit with `fix(scope): resolve issue`.

## 8. Git Workflow

| Branch | Purpose | Protection |
| --- | --- | --- |
| `main` | Production | PR, review, CI pass, deployment approval |
| `develop` | Integration | PR and CI pass |
| `feature/*` | Feature work | Merge to develop |
| `fix/*` | Hotfixes | Branch from main; merge back to develop |
| `release/*` | Staging and release candidate | Full regression before main |

Commit format:

```text
type(scope): subject

body

footer
```

Types: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`, `perf`.

Recommended fast local checks before commit:

```bash
composer test
npm run build
php artisan view:cache
```

## 9. CI Workflow

The repository includes GitHub Actions workflows for:

- `ci.yml`: tests, Blade compilation, asset build, and dependency audit.
- `deploy-namecheap.yml`: manual, protected FTP deployment template.

Required GitHub Secrets for deployment:

```text
FTP_SERVER
FTP_USERNAME
FTP_PASSWORD
FTP_SERVER_DIR
```

Configure the GitHub `production` environment to require manual approval before `deploy-namecheap.yml` can upload files.

## 10. Pre-Deploy Checklist

Run locally or in CI before touching production:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan test
php artisan view:cache
php artisan route:cache
php artisan config:cache
```

Then verify the production target from the server:

```bash
php artisan sanfaani:deployment-verify
php artisan migrate --pretend > migration_preview.sql
```

Stop if `migration_preview.sql` contains `DROP`, `TRUNCATE`, destructive `DELETE`, or `ALTER TABLE ... DROP`.

Download a live database backup before migration.

## 11. Namecheap Deployment

### Method A: cPanel Git Deployment

Use this if Namecheap Git Version Control is available.

```bash
cd /home/username/schools.sanfaani.net
php artisan down
git pull origin main
php artisan sanfaani:deployment-verify
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
php artisan queue:restart
chmod -R 775 storage bootstrap/cache
php artisan up
```

Upload `vendor/` and `public/build/` from CI/local if Composer or Node is unavailable on the host.

### Method B: Manual FTP/SFTP

Local build:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan test
```

Upload:

```text
app/
bootstrap/
config/
database/
public/
resources/
routes/
vendor/
artisan
composer.json
composer.lock
```

Do not upload:

```text
.env
.git/
node_modules/
tests/
storage/logs/
storage/framework/cache/
```

### Method C: GitHub Actions FTP

Use `.github/workflows/deploy-namecheap.yml` only after:

- GitHub Secrets are configured.
- `production` environment requires reviewer approval.
- A fresh database backup exists.
- CI has passed on the exact commit being deployed.

## 12. Post-Deploy Smoke Tests

Run immediately after deploy:

| Surface | Expected |
| --- | --- |
| `https://schools.sanfaani.net` | HTTP 200 |
| `/login` | Loads with logo, no 500 |
| Super Admin login | Platform sidebar only |
| School Admin login | School modules only |
| Teacher login | Assigned modules only |
| Result Officer login | Result modules only |
| Student 360 | Enrollment-aware subjects only |
| Result workspace | Loads selected session and term |
| Public result checker | Draft hidden, published visible |
| Arabic switch | `dir="rtl"` and no layout collision |
| Logs | No new fatal errors |

Check logs:

```bash
tail -n 100 storage/logs/laravel.log
```

## 13. Existing Standalone Installations: Licensing Temporarily Disabled

No license key or signing key is required. The source default for `SANFAANI_LICENSE_VALIDATION_ENABLED` is `false`, and no database migration is needed. Existing license tables and records remain dormant and must not be deleted.

After deploying to `/home/swifarpx/portal.sanfaani.net`, rebuild Laravel caches:

```bash
cd /home/swifarpx/portal.sanfaani.net
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

See `docs/licensing/temporary-license-disablement.md` for restoration guidance.

## 14. Rollback Protocol

If a critical failure appears within 30 minutes:

1. Enable maintenance mode or restore the previous `public/index.php`.
2. Restore the pre-deploy database backup through phpMyAdmin.
3. Revert code to the previous known-good commit.
4. Run:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. Disable maintenance mode.
6. Repeat smoke tests.
7. Open a GitHub incident issue with logs, timeline, and rollback notes.

## 15. Maintenance Schedule

Weekly:

- Review `storage/logs` for recurring errors.
- Check cPanel disk usage.
- Verify queue cron execution.
- Test latest backup integrity.
- Review failed login attempts.

Monthly:

- Run `composer audit --locked --abandoned=report`.
- Update dependencies in staging first.
- Test registration, payment, scratch-card, and result-checking end to end.
- Refresh staging demo data.

## 16. Acceptance Criteria

- All permission gates have tests.
- 100 percent of primary routes have 200, 302, 403, or validation assertions.
- Role sidebars are filtered and regression-tested.
- Scratch-card event type error is fixed and regression-tested.
- Arabic RTL works on primary surfaces.
- Result Workspace reaches the 2,000 row target in under 2 seconds.
- Lighthouse score is at least 80 on throttled mobile.
- No `.env`, `.git`, logs, or backups are publicly exposed.
- Production deploy completes in under 10 minutes.
- Simulated rollback completes in under 5 minutes.
