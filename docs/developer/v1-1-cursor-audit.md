# V1.1 Cursor Audit

Date: 2026-05-05  
Branch: `codex/v1-1-architecture-improvements`

## 1. Completed V1.1 features

- School isolation foundations are broadly in place via `school_id`-scoped queries across school/admin/public result and setup controllers.
- Teacher assignment and result workflow is implemented:
  - Teacher class/subject assignments exist.
  - Teacher result draft/save/submit exists.
  - School review return/approve/publish/void exists.
  - Public checker reads only published results.
- Public checker behavior is correct for scope:
  - Public/global checker does not expose a school dropdown.
  - Dedicated checker route (`/s/{school:slug}/result-checker`) is school-scoped.
- Payment and mail settings are implemented with encrypted-at-rest casts and masked UI placeholders.
- Onboarding foundation exists via `onboarding_progress` table, service usage, and dashboard checklist cards.
- Language preference foundation exists via `language_preferences` table and class/subject edit hooks.
- School website/public page foundation exists (school + admin public page forms backed by `school_website_settings`).
- Support foundation is now fully wired:
  - Admin routes, controller, and views for list/detail/reply/status/assign.
  - School routes, controller, and views for list/create/store/show/reply/close.
  - Dashboard UI cards added for quick access in admin and school dashboards.

## 2. Incomplete V1.1 features

- No dedicated onboarding wizard step-by-step pages yet; current state is checklist/progress foundation only.
- No standalone global language preference management module yet (only school default and class/subject hooks).
- Website setting behavior is foundation-level (mode/flags/domain fields) and does not yet include full site builder features.
- Automated support notifications/escalation (mail/SMS) are not implemented in support threads yet.

## 3. Risky or untested features

- New support thread flow has no automated feature tests yet (admin + school conversation lifecycle should be covered).
- Teacher result publish flow writes into `student_results` from submission metadata; this needs more role-based and edge-case tests.
- Support thread assignment currently validates assignee existence and super-admin role at runtime; no policy layer yet.
- Build plugin timing warning (`laravel` plugin time) is informational, but should be watched as frontend grows.

## 4. Duplicate or unnecessary files if any

- No duplicate migrations/routes/controllers/views were found during this pass.
- Existing generated `storage/framework/views/*` cache files are local runtime artifacts and should remain uncommitted.

## 5. Routes missing compared to master prompt

- Previously missing support routes are now implemented:
  - `/admin/support-threads`
  - `/admin/support-threads/{thread}`
  - `/admin/support-threads/{thread}/reply`
  - `/admin/support-threads/{thread}/status`
  - `/admin/support-threads/{thread}/assign`
  - `/school/support`
  - `/school/support/create`
  - `/school/support` (POST)
  - `/school/support/{thread}`
  - `/school/support/{thread}/reply`
  - `/school/support/{thread}/close`
- No additional route gaps were identified against the explicitly provided priority list.

## 6. Views missing compared to master prompt

- Support views were missing and are now implemented:
  - `resources/views/admin/support-threads/index.blade.php`
  - `resources/views/admin/support-threads/show.blade.php`
  - `resources/views/school/support/index.blade.php`
  - `resources/views/school/support/create.blade.php`
  - `resources/views/school/support/show.blade.php`
- Onboarding, language, and website settings currently remain foundation-level UI, not full wizard modules.

## 7. Controllers missing compared to master prompt

- Support controllers were missing and are now implemented:
  - `app/Http/Controllers/Admin/SupportThreadController.php`
  - `app/Http/Controllers/School/SupportThreadController.php`

## 8. Migrations that need review for Namecheap-safe indexes

- Reviewed recent V1.1 migrations for explicit/short index names:
  - `2026_05_05_000005_create_payment_gateway_settings_table.php`
  - `2026_05_05_000007_create_onboarding_progress_table.php`
  - `2026_05_05_000008_create_language_preferences_table.php`
  - `2026_05_05_000009_create_teacher_class_assignments_table.php`
  - `2026_05_05_000010_create_teacher_subject_assignments_table.php`
  - `2026_05_05_000011_create_teacher_result_submissions_table.php`
  - `2026_05_05_000013_create_school_website_settings_table.php`
  - `2026_05_05_000014_create_support_threads_table.php`
  - `2026_05_05_000015_create_support_messages_table.php`
- No immediate long-index-name failures were observed on local migration run.

## 9. Manual tests still required

- School user creates support thread, replies, closes; verify only same school can access its thread.
- Super admin opens support threads, assigns owner, replies, changes status; verify school can see expected updates.
- Teacher role:
  - Can only create/submit for assigned class/subject combinations.
  - Cannot edit after submitted/approved/published unless returned.
- Result review role:
  - Return/approve/publish transitions enforce expected status order.
  - Public checker shows results only after publish.
- Public checker:
  - Global route has no school picker and still works with valid scratch card + student.
  - Dedicated school route rejects cross-school card/student pairs.
- Mobile pass on newly added support pages and dashboard cards.

## 10. Next exact implementation steps

1. Add feature tests for support lifecycle (school create/reply/close + admin reply/status/assign).
2. Add role/policy authorization tests for support thread access boundaries by `school_id`.
3. Add feature tests for teacher result workflow transitions and published-only public visibility.
4. Add an optional onboarding wizard page that writes to `onboarding_progress` using existing service.
5. Add a small language preference management page (foundation UI) if needed, reusing `language_preferences`.
6. Keep website settings as foundation unless product scope expands to full inbuilt website editor.
