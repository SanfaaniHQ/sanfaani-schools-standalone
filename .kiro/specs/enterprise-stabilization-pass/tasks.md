# Implementation Plan

## Overview

This implementation plan addresses 10 critical bug categories in the Sanfaani Schools Multi-School SaaS platform. The plan follows the bugfix workflow methodology with exploration tests (to confirm bugs exist), preservation tests (to ensure no regressions), and systematic implementation of fixes across all bug categories.

**Bug Categories:**
1. Scratch Card Result Check Crash
2. Result Entry Permissions Broken
3. Result Workspace Finalization Issues
4. Student 360 Result Operations
5. Communication Log Visibility Leak
6. School Mail System Issues
7. Role Sidebar Issues
8. Language System Instability
9. Responsive Design Issues
10. Performance Issues

**Workflow Phases:**
- Phase 1: Bug Condition Exploration Tests (BEFORE Fix) - Confirm bugs exist
- Phase 2: Preservation Property Tests (BEFORE Fix) - Establish baseline behavior
- Phase 3: Implementation - Fix all 10 bug categories systematically
- Phase 4: Final Checkpoint - Verify all tests pass

## Tasks

## Bug Condition Exploration Tests (BEFORE Fix)

- [ ] 1. Write bug condition exploration tests for all 10 bug categories
  - **Property 1: Bug Condition** - Enterprise Stabilization Pass Bug Conditions
  - **CRITICAL**: These tests MUST FAIL on unfixed code - failures confirm the bugs exist
  - **DO NOT attempt to fix the tests or the code when they fail**
  - **NOTE**: These tests encode the expected behavior - they will validate the fixes when they pass after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bugs exist
  - **Scoped PBT Approach**: For deterministic bugs, scope properties to concrete failing cases to ensure reproducibility
  - Test implementation details from Bug Condition specifications in design
  - The test assertions should match the Expected Behavior Properties from design
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests FAIL (this is correct - it proves the bugs exist)
  - Document counterexamples found to understand root causes
  - Mark task complete when tests are written, run, and failures are documented
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 1.9, 1.10, 1.11, 1.12, 1.13, 1.14, 1.15, 1.16, 1.17, 1.18, 1.19, 1.20, 1.21, 1.22, 1.23, 1.24, 1.25, 1.26, 1.27, 1.28, 1.29, 1.30, 1.31, 1.32, 1.33, 1.34, 1.35, 1.36, 1.37, 1.38, 1.39, 1.40, 1.41, 1.42, 1.43, 1.44, 1.45, 1.46, 1.47, 1.48, 1.49, 1.50, 1.51, 1.52, 1.53, 1.54, 1.55, 1.56, 1.57, 1.58, 1.59, 1.60_

  - [x] 1.1 Test scratch card result check crash
    - Simulate valid scratch card credentials submission
    - Expected: System crashes with `StudentTransactionalEmailRequested::__construct()` error
    - Confirms: Event dispatch pattern is incorrect (line 237 in ResultCheckerController)
    - Document: Exact error message and stack trace

  - [x] 1.2 Test result entry permission bypass
    - Simulate teacher editing approved/published/locked result
    - Expected: Edit operation allowed instead of blocked
    - Confirms: Authorization policies missing
    - Document: Which protected states allow unauthorized edits

  - [-] 1.3 Test result workspace navigation breaks
    - Simulate result entry in Student 360, Result Management, and Teacher Entry workspaces
    - Expected: Navigation breaks during operations
    - Confirms: JavaScript navigation handling incomplete
    - Document: Specific navigation failures observed

  - [~] 1.4 Test result workspace inline actions missing
    - Simulate viewing result grid
    - Expected: Inline actions (Add, Edit, Save Draft, Submit, Approve, Publish) missing
    - Confirms: Blade templates incomplete
    - Document: Which actions are missing

  - [~] 1.5 Test Student 360 inline editing broken
    - Simulate inline edit attempt in Student 360 Result view
    - Expected: Inline editing does not activate
    - Confirms: JavaScript inline editing not implemented
    - Document: Observed behavior when clicking edit

  - [~] 1.6 Test Student 360 save draft broken
    - Simulate save draft and autosave operations
    - Expected: Functionality broken, data not saved
    - Confirms: AJAX endpoints incomplete
    - Document: Error messages or failed requests

  - [~] 1.7 Test communication log visibility leak
    - Simulate teacher/result officer accessing system
    - Expected: Communication logs visible to unauthorized roles
    - Confirms: Authorization enforcement missing
    - Document: Which UI elements show communication logs

  - [~] 1.8 Test school mail system broken
    - Simulate email sending with school SMTP configured
    - Expected: School SMTP not used or fails
    - Confirms: Dynamic SMTP switching not implemented
    - Document: Which email types fail and error messages

  - [~] 1.9 Test sidebar duplication and permission leakage
    - Simulate sidebar rendering for each role
    - Expected: Duplicated menu items and unauthorized modules visible
    - Confirms: Menu filtering logic incomplete
    - Document: Specific duplicates and unauthorized items per role

  - [~] 1.10 Test language persistence broken
    - Simulate language preference change
    - Expected: Preference not saved, reverts on reload
    - Confirms: Persistence logic incomplete
    - Document: Where persistence fails (session vs database)

  - [~] 1.11 Test responsive design broken
    - Simulate mobile/tablet device access
    - Expected: Overlapping text, broken tables, hidden buttons
    - Confirms: Responsive CSS incomplete
    - Document: Specific layout issues per breakpoint

  - [~] 1.12 Test N+1 query performance issues
    - Simulate loading result list with relationships
    - Expected: N+1 queries detected in query log
    - Confirms: Eager loading missing
    - Document: Query count and specific N+1 patterns

## Preservation Property Tests (BEFORE Fix)

- [ ] 2. Write preservation property tests for non-buggy behavior
  - **Property 2: Preservation** - Existing Functionality Preservation
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-buggy inputs
  - Write property-based tests capturing observed behavior patterns from Preservation Requirements
  - Property-based testing generates many test cases for stronger guarantees
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10, 3.11, 3.12, 3.13, 3.14, 3.15, 3.16, 3.17, 3.18, 3.19, 3.20, 3.21, 3.22, 3.23, 3.24, 3.25, 3.26, 3.27, 3.28, 3.29, 3.30_

  - [~] 2.1 Test valid scratch card checking preserved
    - Observe: Valid scratch card with correct credentials works on unfixed code
    - Test: Invalid credentials show appropriate errors
    - Test: Complete result display with all subjects/scores/grades
    - Test: Scratch card reuse prevention works

  - [~] 2.2 Test authorized result operations preserved
    - Observe: Authorized users can perform result entry within scope on unfixed code
    - Test: Valid workflow transitions process correctly
    - Test: Result calculations accurate (totals, grades, pass/fail)

  - [~] 2.3 Test Super Admin communication access preserved
    - Observe: Super Admin can access all communication logs on unfixed code
    - Test: Authorized School Admin sees school-scoped logs
    - Test: Communication emails deliver successfully with branding

  - [~] 2.4 Test RBAC for authorized features preserved
    - Observe: Users access features within authorized scope on unfixed code
    - Test: Role-based permissions grant appropriate access
    - Test: School_id isolation enforced for multi-tenancy

  - [~] 2.5 Test platform SMTP fallback preserved
    - Observe: Platform SMTP works when school SMTP not configured on unfixed code
    - Test: Email delivery with school branding functional
    - Test: Email failure logging works

  - [~] 2.6 Test language preferences preserved
    - Observe: English, French, Arabic language preferences work on unfixed code
    - Test: Translations display correctly for each language
    - Test: RTL layout works for Arabic (existing functionality)

  - [~] 2.7 Test desktop layouts preserved
    - Observe: Desktop layouts render correctly on unfixed code
    - Test: All UI components display properly on desktop
    - Test: Navigation and interactions work on desktop

  - [~] 2.8 Test existing performance preserved
    - Observe: Small/medium schools perform well on unfixed code
    - Test: Existing database indexes work efficiently
    - Test: Existing caching strategies function correctly

  - [~] 2.9 Test audit logging preserved
    - Observe: Audit-worthy actions logged on unfixed code
    - Test: Audit logs contain complete metadata
    - Test: Audit log queries return accurate historical records

  - [~] 2.10 Test Student 360 non-result sections preserved
    - Observe: Student 360 profile, enrollment sections work on unfixed code
    - Test: Non-result sections remain functional
    - Test: Report card generation produces accurate output


## Implementation

- [ ] 3. Fix Bug Category 1: Scratch Card Result Check Crash

  - [~] 3.1 Fix event dispatch pattern in ResultCheckerController
    - Modify `app/Http/Controllers/Public/ResultCheckerController.php` line 237
    - Change from: `StudentTransactionalEmailRequested::dispatch(StudentTransactionalEmailRequested::resultAvailable(...))`
    - Change to: `StudentTransactionalEmailRequested::resultAvailable($student->loadMissing('school'), $academicSession, $term, [...])->dispatch()`
    - Add try-catch error handling around email dispatch to prevent crashes from blocking result display
    - Ensure audit log is recorded before email dispatch attempt
    - _Bug_Condition: isBugCondition_ScratchCardCrash(input) where input.scratchCardValid = true AND input.resultPublished = true AND emailDispatchAttempted(input) AND dispatchPattern = "dispatch(constructedEvent)"_
    - _Expected_Behavior: System SHALL dispatch event correctly without crashing, result SHALL load and display, audit log SHALL record access_
    - _Preservation: Valid scratch card checking with correct credentials must continue to work_
    - _Requirements: 2.1, 2.2, 2.3, 3.1, 3.2, 3.3_

  - [~] 3.2 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Scratch Card Email Dispatch
    - **IMPORTANT**: Re-run the SAME test from task 1.1 - do NOT write a new test
    - The test from task 1.1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Run bug condition exploration test from step 1.1
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - _Requirements: 2.1, 2.2, 2.3_

  - [~] 3.3 Verify preservation tests still pass
    - **Property 2: Preservation** - Scratch Card Preservation
    - **IMPORTANT**: Re-run the SAME tests from task 2.1 - do NOT write new tests
    - Run preservation property tests from step 2.1
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Confirm all tests still pass after fix

- [ ] 4. Fix Bug Category 2: Result Entry Permissions Broken

  - [~] 4.1 Create ResultPolicy with authorization methods
    - Create `app/Policies/ResultPolicy.php`
    - Implement `update()`: Check result status (not approved/published/locked) and user role
    - Implement `approve()`: Check user is Result Officer or School Admin with permission
    - Implement `publish()`: Check user is School Admin with permission
    - Implement `viewAny()`: Check user has access to school and assigned classes (for teachers)
    - Implement teacher scope validation checking `ClassSubjectAssignment`
    - Implement Result Officer permission flag checking
    - _Bug_Condition: isBugCondition_PermissionsBroken(input) where result status in protected states OR user accessing outside scope OR Result Officer with disabled flags_
    - _Expected_Behavior: System SHALL prevent unauthorized operations and display appropriate error messages_
    - _Preservation: Authorized result entry operations within user scope must continue to function_
    - _Requirements: 2.4, 2.5, 2.6, 2.7, 2.8, 2.9, 3.4, 3.5, 3.6_

  - [~] 4.2 Create EnsureResultEditable middleware
    - Create `app/Http/Middleware/EnsureResultEditable.php`
    - Check workflow state before allowing edits
    - Block edits for approved/published/locked results
    - Return appropriate error messages

  - [~] 4.3 Add policy checks to ResultController
    - Modify `app/Http/Controllers/School/ResultController.php`
    - Add `$this->authorize('update', $result)` in edit methods
    - Add `$this->authorize('approve', $result)` in approve methods
    - Add `$this->authorize('publish', $result)` in publish methods

  - [~] 4.4 Apply middleware to result edit routes
    - Modify `routes/web.php`
    - Apply `EnsureResultEditable` middleware to result edit routes
    - Ensure route protection consistent across all result operations

  - [~] 4.5 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Result Entry Permission Enforcement
    - **IMPORTANT**: Re-run the SAME test from task 1.2 - do NOT write a new test
    - Run bug condition exploration test from step 1.2
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - _Requirements: 2.4, 2.5, 2.6, 2.7, 2.8, 2.9_

  - [~] 4.6 Verify preservation tests still pass
    - **Property 2: Preservation** - Authorized Result Operations
    - **IMPORTANT**: Re-run the SAME tests from task 2.2 - do NOT write new tests
    - Run preservation property tests from step 2.2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)

- [ ] 5. Fix Bug Category 3: Result Workspace Finalization Issues

  - [~] 5.1 Complete result grid Blade template
    - Modify `resources/views/school/results/partials/result-grid.blade.php`
    - Add all inline action buttons: Add, Edit, Save Draft, Submit, Return, Approve, Publish, Unpublish, View Audit Log
    - Add proper authorization checks using `@can` directives
    - Add complete columns: CA, Exam, Total, Grade, Pass/Fail, Remarks, Status, Source, Timestamps, Audit Trail
    - _Bug_Condition: isBugCondition_WorkspaceIncomplete(input) where navigation broken OR inline actions missing OR columns incomplete_
    - _Expected_Behavior: System SHALL provide working navigation, complete inline actions, complete columns, enforced workflow transitions, auto-calculation, complete audit logging_
    - _Preservation: Authorized result operations within user scope must continue to function_
    - _Requirements: 2.10, 2.11, 2.12, 2.13, 2.14, 2.15, 2.16, 2.17, 3.4, 3.5, 3.6_

  - [~] 5.2 Implement JavaScript for inline editing and navigation
    - Create `resources/js/result-workspace.js`
    - Implement inline editing functionality
    - Implement navigation handling to prevent breaks during operations
    - Implement AJAX operations for result actions

  - [~] 5.3 Create ResultWorkflowService for state machine
    - Create `app/Services/ResultWorkflowService.php`
    - Implement `canTransition($result, $toStatus)`: Validate state transitions
    - Implement `transition($result, $toStatus, $user)`: Execute transition with audit logging
    - Define valid transitions: Draft → Submitted → Returned → Reviewed → Approved → Published → Unpublished → Archived → Locked

  - [~] 5.4 Create ResultValidationService for auto-calculation
    - Create `app/Services/ResultValidationService.php`
    - Implement `calculateTotal($ca, $exam)`: Calculate total score
    - Implement `applyGrading($total, $gradingScale)`: Apply grading scale
    - Implement `detectPassFail($grade)`: Determine pass/fail status
    - Implement `preventDuplicates($student, $subject, $term)`: Check for existing results

  - [~] 5.5 Integrate audit logging in result operations
    - Modify `app/Http/Controllers/School/ResultController.php`
    - Call `AuditLogService::log()` for all result operations
    - Ensure complete metadata captured in audit logs

  - [~] 5.6 Verify bug condition exploration tests now pass
    - **Property 1: Expected Behavior** - Result Workspace Completeness
    - **IMPORTANT**: Re-run the SAME tests from tasks 1.3, 1.4 - do NOT write new tests
    - Run bug condition exploration tests from steps 1.3, 1.4
    - **EXPECTED OUTCOME**: Tests PASS (confirms bugs are fixed)
    - _Requirements: 2.10, 2.11, 2.12, 2.13, 2.14, 2.15, 2.16, 2.17_

  - [~] 5.7 Verify preservation tests still pass
    - **Property 2: Preservation** - Result Operations
    - **IMPORTANT**: Re-run the SAME tests from task 2.2 - do NOT write new tests
    - Run preservation property tests from step 2.2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)

- [ ] 6. Fix Bug Category 4: Student 360 Result Operations

  - [~] 6.1 Implement inline editing JavaScript
    - Create `resources/js/student-360-results.js`
    - Implement click handlers for inline edit activation
    - Implement input field rendering and validation
    - Implement save/cancel handlers
    - _Bug_Condition: isBugCondition_Student360Broken(input) where inline editing broken OR save draft broken OR subject switching broken_
    - _Expected_Behavior: System SHALL provide fully functional inline editing, save draft, autosave, quick subject switching, validation indicators, responsive grids_
    - _Preservation: Student 360 non-result sections must remain functional_
    - _Requirements: 2.18, 2.19, 2.20, 2.21, 2.22, 2.23, 2.24, 3.27_

  - [~] 6.2 Create AJAX endpoints for Student 360 result operations
    - Create `app/Http/Controllers/School/Student360ResultController.php`
    - Implement `saveDraft()`: Save result as draft
    - Implement `autosave()`: Auto-save on input change (debounced)
    - Implement `switchSubject()`: Load results for different subject

  - [~] 6.3 Implement quick subject switching
    - Add subject dropdown to Student 360 Result view
    - Implement AJAX loading for subject switching
    - Ensure smooth UX without page reloads

  - [~] 6.4 Add validation indicators
    - Create validation indicator components
    - Show required field indicators
    - Show score range validation
    - Show duplicate detection warnings

  - [~] 6.5 Implement responsive academic grids
    - Create `resources/css/responsive-academic-grids.css`
    - Add mobile breakpoints for tables
    - Add horizontal scroll containers
    - Add card-based layout for mobile
    - Add touch-friendly input controls

  - [~] 6.6 Verify bug condition exploration tests now pass
    - **Property 1: Expected Behavior** - Student 360 Result Operations
    - **IMPORTANT**: Re-run the SAME tests from tasks 1.5, 1.6 - do NOT write new tests
    - Run bug condition exploration tests from steps 1.5, 1.6
    - **EXPECTED OUTCOME**: Tests PASS (confirms bugs are fixed)
    - _Requirements: 2.18, 2.19, 2.20, 2.21, 2.22, 2.23, 2.24_

  - [~] 6.7 Verify preservation tests still pass
    - **Property 2: Preservation** - Student 360 Non-Result Sections
    - **IMPORTANT**: Re-run the SAME tests from task 2.10 - do NOT write new tests
    - Run preservation property tests from step 2.10
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)

- [ ] 7. Fix Bug Category 5: Communication Log Visibility Leak

  - [~] 7.1 Create CommunicationLogPolicy
    - Create `app/Policies/CommunicationLogPolicy.php`
    - Implement `viewAny()`: Return true only for Super Admin and School Admin with explicit permission
    - Implement `view()`: Check user can access specific communication log
    - Implement `create()`: Check user can create communication logs
    - _Bug_Condition: isBugCondition_CommunicationLeak(input) where unauthorized roles can view communication logs_
    - _Expected_Behavior: System SHALL hide communication logs from unauthorized roles and enforce backend authorization_
    - _Preservation: Super Admin and authorized School Admin access must remain unchanged_
    - _Requirements: 2.25, 2.26, 2.27, 2.28, 3.7, 3.8, 3.9_

  - [~] 7.2 Create EnsureCommunicationAccess middleware
    - Create `app/Http/Middleware/EnsureCommunicationAccess.php`
    - Check user role and permissions before allowing access
    - Return appropriate error for unauthorized access

  - [~] 7.3 Apply middleware to communication routes
    - Modify `routes/web.php`
    - Apply `EnsureCommunicationAccess` to all communication log routes
    - Ensure consistent route protection

  - [~] 7.4 Update sidebar Blade template with permission checks
    - Modify `resources/views/layouts/partials/sidebar.blade.php`
    - Add `@can('viewAny', App\Models\CommunicationLog::class)` checks
    - Hide communication menu items for unauthorized roles

  - [~] 7.5 Update dashboard Blade template with permission checks
    - Modify `resources/views/school/dashboard.blade.php`
    - Add `@can('viewAny', App\Models\CommunicationLog::class)` checks
    - Hide communication widgets/cards for unauthorized roles

  - [~] 7.6 Add Gate checks in controllers
    - Modify communication controllers
    - Add `$this->authorize('viewAny', CommunicationLog::class)` in controller methods

  - [~] 7.7 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Communication Log Visibility Enforcement
    - **IMPORTANT**: Re-run the SAME test from task 1.7 - do NOT write a new test
    - Run bug condition exploration test from step 1.7
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - _Requirements: 2.25, 2.26, 2.27, 2.28_

  - [~] 7.8 Verify preservation tests still pass
    - **Property 2: Preservation** - Super Admin Communication Access
    - **IMPORTANT**: Re-run the SAME tests from task 2.3 - do NOT write new tests
    - Run preservation property tests from step 2.3
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)


- [ ] 8. Fix Bug Category 6: School Mail System Issues

  - [~] 8.1 Create SchoolMailService for dynamic SMTP switching
    - Create `app/Services/SchoolMailService.php`
    - Implement `resolveMailDriver($school)`: Determine which SMTP to use (school or platform)
    - Implement `configureSchoolSMTP($school)`: Configure Laravel mailer with school SMTP settings
    - Implement `configureFallbackSMTP()`: Configure platform SMTP as fallback
    - Implement `switchSMTP($school)`: Dynamically switch SMTP configuration
    - Implement `testConnection($config)`: Test SMTP connection before use
    - _Bug_Condition: isBugCondition_MailSystemBroken(input) where school SMTP not working OR fallback not configured OR queue issues_
    - _Expected_Behavior: System SHALL properly use school SMTP for all email types, activate fallback when needed, ensure queue compatibility_
    - _Preservation: Platform SMTP fallback when school SMTP not configured must continue to work_
    - _Requirements: 2.29, 2.30, 2.31, 2.32, 2.33, 3.13, 3.14, 3.15_

  - [~] 8.2 Create SchoolMailable base class
    - Create `app/Mail/SchoolMailable.php`
    - Extend Laravel Mailable
    - Implement `buildWithSchoolConfig($school)`: Build email with school-specific SMTP
    - Implement queue-safe serialization of school mail config
    - Implement automatic failover to platform SMTP on failure

  - [~] 8.3 Implement failover behavior in mail listeners
    - Modify `app/Listeners/SendStudentTransactionalEmail.php`
    - Modify `app/Listeners/SendStaffTransactionalEmail.php`
    - Wrap mail sending in try-catch
    - Try school SMTP first, on failure log error and retry with platform SMTP
    - Update communication log with delivery status

  - [~] 8.4 Encrypt SMTP credentials
    - Create migration `database/migrations/xxxx_add_encrypted_smtp_credentials.php`
    - Encrypt existing SMTP passwords in `mail_settings` table using `Crypt::encrypt()`
    - Decrypt in `SchoolMailService` when configuring SMTP

  - [~] 8.5 Implement queue-safe mail configuration
    - Ensure school mail config is serializable for queue workers
    - Store school_id instead of School model in queued jobs
    - Resolve school and configure SMTP in queue worker
    - Handle school deletion gracefully in queue

  - [~] 8.6 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - School Mail System Completion
    - **IMPORTANT**: Re-run the SAME test from task 1.8 - do NOT write a new test
    - Run bug condition exploration test from step 1.8
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - _Requirements: 2.29, 2.30, 2.31, 2.32, 2.33_

  - [~] 8.7 Verify preservation tests still pass
    - **Property 2: Preservation** - Platform SMTP Fallback
    - **IMPORTANT**: Re-run the SAME tests from task 2.5 - do NOT write new tests
    - Run preservation property tests from step 2.5
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)

- [ ] 9. Fix Bug Category 7: Role Sidebar Issues

  - [~] 9.1 Create sidebar configuration file
    - Create `config/sidebar.php`
    - Define role-to-module mapping:
      - super_admin: ['platform_settings', 'schools', 'subscriptions', 'audit_logs']
      - school_admin: ['dashboard', 'students', 'staff', 'classes', 'results', 'communications', 'settings']
      - result_officer: ['dashboard', 'results', 'report_cards']
      - teacher: ['dashboard', 'my_classes', 'my_results']
    - _Bug_Condition: isBugCondition_SidebarBroken(input) where duplicated menu items OR unauthorized modules visible OR permission leakage_
    - _Expected_Behavior: System SHALL eliminate duplicates, show only authorized modules per role, prevent permission leakage, eliminate orphan links_
    - _Preservation: Role-based access control for authorized features must continue to grant access_
    - _Requirements: 2.34, 2.35, 2.36, 2.37, 2.38, 2.39, 2.40, 2.41, 2.42, 3.10, 3.11, 3.12_

  - [~] 9.2 Create Sidebar component
    - Create `app/View/Components/Sidebar.php`
    - Implement `filterMenuByRole($user)`: Filter menu items based on user role
    - Implement `checkPermissions($menuItem, $user)`: Check if user has permission for menu item
    - Implement `removeDuplicates($menu)`: Remove duplicate menu items
    - Implement `removeOrphanLinks($menu)`: Remove links to non-existent routes

  - [~] 9.3 Update sidebar Blade template to use component
    - Modify `resources/views/layouts/partials/sidebar.blade.php`
    - Use Sidebar component to render filtered menu
    - Ensure role-specific module visibility

  - [~] 9.4 Create EnsureAuthorizedRoute middleware
    - Create `app/Http/Middleware/EnsureAuthorizedRoute.php`
    - Check user has permission to access route even if they know the URL
    - Return appropriate error for unauthorized access

  - [~] 9.5 Apply middleware to protected routes
    - Modify `routes/web.php`
    - Add `EnsureAuthorizedRoute` to all protected routes
    - Ensure hidden routes are properly protected

  - [~] 9.6 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Role Sidebar Cleanup
    - **IMPORTANT**: Re-run the SAME test from task 1.9 - do NOT write a new test
    - Run bug condition exploration test from step 1.9
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - _Requirements: 2.34, 2.35, 2.36, 2.37, 2.38, 2.39, 2.40, 2.41, 2.42_

  - [~] 9.7 Verify preservation tests still pass
    - **Property 2: Preservation** - RBAC for Authorized Features
    - **IMPORTANT**: Re-run the SAME tests from task 2.4 - do NOT write new tests
    - Run preservation property tests from step 2.4
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)

- [ ] 10. Fix Bug Category 8: Language System Instability

  - [~] 10.1 Fix SetLocale middleware to persist preferences
    - Modify `app/Http/Middleware/SetLocale.php`
    - Implement database persistence: `LanguagePreference::updateOrCreate(['user_id' => $user->id], ['language' => $locale])`
    - Implement session persistence: `session(['locale' => $locale])`
    - Ensure preference saved on language change
    - _Bug_Condition: isBugCondition_LanguageBroken(input) where translation persistence broken OR backend translations incomplete OR RTL support incomplete_
    - _Expected_Behavior: System SHALL persist translation preferences, load complete backend translations, display correct translations, provide complete RTL support_
    - _Preservation: Language preferences for English, French, Arabic must continue to work_
    - _Requirements: 2.43, 2.44, 2.45, 2.46, 2.47, 3.16, 3.17, 3.18_

  - [~] 10.2 Complete backend translation files
    - Create/update `resources/lang/en/backend.php`
    - Create/update `resources/lang/fr/backend.php`
    - Create/update `resources/lang/ar/backend.php`
    - Ensure all translation keys used in Blade templates exist
    - Add missing translations for validation messages
    - Add missing translations for navbar elements
    - Add missing translations for dashboard elements

  - [~] 10.3 Fix translation keys in Blade templates
    - Modify `resources/views/layouts/partials/navbar.blade.php`
    - Replace hardcoded text with `__()` calls
    - Ensure all UI text uses translation keys
    - Test translations in all three languages

  - [~] 10.4 Complete RTL support
    - Modify `resources/css/rtl.css`
    - Fix spacing issues: Add proper padding/margin for RTL
    - Fix alignment issues: Use `text-align: right` for RTL
    - Fix float issues: Use `float: right` for RTL elements
    - Fix flexbox direction: Use `flex-direction: row-reverse` for RTL
    - Test all major UI components in RTL mode

  - [~] 10.5 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Language System Stabilization
    - **IMPORTANT**: Re-run the SAME test from task 1.10 - do NOT write a new test
    - Run bug condition exploration test from step 1.10
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - _Requirements: 2.43, 2.44, 2.45, 2.46, 2.47_

  - [~] 10.6 Verify preservation tests still pass
    - **Property 2: Preservation** - Language Preferences
    - **IMPORTANT**: Re-run the SAME tests from task 2.6 - do NOT write new tests
    - Run preservation property tests from step 2.6
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)

- [ ] 11. Fix Bug Category 9: Responsive Design Issues

  - [~] 11.1 Fix overlapping text and hidden buttons
    - Create/modify `resources/css/responsive.css`
    - Add responsive breakpoints for mobile/tablet
    - Fix button sizing and visibility on mobile
    - Fix text truncation and wrapping
    - _Bug_Condition: isBugCondition_ResponsiveBroken(input) where overlapping text OR broken tables OR hidden buttons OR z-index issues_
    - _Expected_Behavior: System SHALL eliminate overlapping text, fix table overflow, show buttons, resolve z-index issues, fix sidebar collapse, fix dropdown overflow_
    - _Preservation: Desktop layout rendering must remain correct_
    - _Requirements: 2.48, 2.49, 2.50, 2.51, 2.52, 3.19, 3.20_

  - [~] 11.2 Fix table overflow
    - Modify `resources/css/tables.css`
    - Wrap tables in scrollable containers: `<div class="table-responsive"><table>...</table></div>`
    - Add CSS: `.table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }`
    - Test horizontal scrolling on mobile

  - [~] 11.3 Fix z-index issues
    - Create z-index scale in CSS:
      - `.sidebar { z-index: 1000; }`
      - `.navbar { z-index: 1010; }`
      - `.dropdown { z-index: 1020; }`
      - `.modal { z-index: 1030; }`
    - Test layering on mobile devices

  - [~] 11.4 Fix sidebar collapse
    - Modify `resources/js/sidebar-collapse.js`
    - Add mobile toggle button
    - Implement collapse/expand animation
    - Handle touch events
    - Persist collapse state in localStorage

  - [~] 11.5 Fix dropdown overflow
    - Create `resources/js/dropdown-positioning.js`
    - Detect viewport boundaries
    - Reposition dropdown if overflowing
    - Add scrolling for long dropdowns
    - Handle mobile touch events

  - [~] 11.6 Fix dark mode contrast
    - Modify `resources/css/dark-mode.css`
    - Update color palette for sufficient contrast
    - Test readability in dark mode
    - Ensure WCAG AA compliance for contrast ratios

  - [~] 11.7 Fix sticky headers
    - Add sticky header CSS: `.table thead th { position: sticky; top: 0; background: #fff; z-index: 10; }`
    - Test sticky behavior on mobile scrolling

  - [~] 11.8 Optimize for low-end Android
    - Reduce CSS complexity
    - Minimize JavaScript execution
    - Use CSS transforms instead of position changes
    - Lazy load images and heavy components
    - Test on low-end Android devices

  - [~] 11.9 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Responsive Design Stabilization
    - **IMPORTANT**: Re-run the SAME test from task 1.11 - do NOT write a new test
    - Run bug condition exploration test from step 1.11
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - _Requirements: 2.48, 2.49, 2.50, 2.51, 2.52_

  - [~] 11.10 Verify preservation tests still pass
    - **Property 2: Preservation** - Desktop Layouts
    - **IMPORTANT**: Re-run the SAME tests from task 2.7 - do NOT write new tests
    - Run preservation property tests from step 2.7
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)

- [ ] 12. Fix Bug Category 10: Performance Issues

  - [~] 12.1 Eliminate N+1 queries with eager loading
    - Modify `app/Http/Controllers/School/ResultController.php`
    - Add eager loading to all relationship queries: `Result::with(['student', 'subject', 'schoolClass'])->get()`
    - Modify `app/Http/Controllers/School/StudentController.php`
    - Add eager loading: `Student::with(['schoolClass', 'results'])->get()`
    - _Bug_Condition: isBugCondition_PerformanceSlow(input) where N+1 queries occur OR eager loading missing OR indexes missing_
    - _Expected_Behavior: System SHALL eliminate N+1 queries, implement eager loading, optimize indexed queries, implement pagination, lazy loading, query scopes, caching_
    - _Preservation: Existing database indexes and query performance for small/medium schools must be preserved_
    - _Requirements: 2.53, 2.54, 2.55, 2.56, 2.57, 2.58, 2.59, 2.60, 3.21, 3.22, 3.23_

  - [~] 12.2 Add database indexes
    - Create migration `database/migrations/xxxx_add_performance_indexes.php`
    - Add indexes on `results` table: status, workflow_status, [school_id, term_id, academic_session_id], [student_id, subject_id, term_id]
    - Add indexes on `students` table: status, [school_id, school_class_id]
    - Add indexes on `communication_logs` table: status, [school_id, created_at]
    - Run migration and test query performance

  - [~] 12.3 Implement pagination
    - Modify controllers to use pagination: `Student::where('school_id', $schoolId)->paginate(50)`
    - Update Blade templates to display pagination links
    - Test pagination on large result sets

  - [~] 12.4 Implement lazy loading for dashboard widgets
    - Create JavaScript for lazy loading widgets
    - Use IntersectionObserver to load widgets on scroll
    - Load widgets on-demand instead of upfront

  - [~] 12.5 Create query scopes
    - Modify `app/Models/Result.php`
    - Add scopes: `scopeForTerm($query, $termId)`, `scopePublished($query)`, `scopeForSchool($query, $schoolId)`
    - Modify `app/Models/Student.php`
    - Add scopes: `scopeActive($query)`, `scopeForSchool($query, $schoolId)`

  - [~] 12.6 Implement safe caching
    - Create `app/Services/CacheService.php`
    - Implement `getSchoolSettings($schoolId)`: Cache school settings for 1 hour
    - Implement `getGradingScale($schoolId)`: Cache grading scale for 1 hour
    - Implement `invalidateSchoolCache($schoolId)`: Clear cache on updates
    - Use caching in controllers for frequently accessed data

  - [~] 12.7 Add performance monitoring
    - Modify `app/Providers/AppServiceProvider.php`
    - Add DB query listener to log slow queries (>1 second)
    - Monitor query performance in production

  - [~] 12.8 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Performance Optimization
    - **IMPORTANT**: Re-run the SAME test from task 1.12 - do NOT write a new test
    - Run bug condition exploration test from step 1.12
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - _Requirements: 2.53, 2.54, 2.55, 2.56, 2.57, 2.58, 2.59, 2.60_

  - [~] 12.9 Verify preservation tests still pass
    - **Property 2: Preservation** - Existing Performance
    - **IMPORTANT**: Re-run the SAME tests from task 2.8 - do NOT write new tests
    - Run preservation property tests from step 2.8
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)

## Final Checkpoint

- [~] 13. Checkpoint - Ensure all tests pass
  - Run all bug condition exploration tests (tasks 1.1-1.12)
  - Verify all tests now PASS (confirming all bugs are fixed)
  - Run all preservation property tests (tasks 2.1-2.10)
  - Verify all tests still PASS (confirming no regressions)
  - Run full test suite to ensure integration
  - Document any issues or questions for user review
  - Ask the user if questions arise


## Task Dependency Graph

```json
{
  "waves": [
    {
      "name": "Phase 1: Testing Setup",
      "tasks": ["1", "2"]
    },
    {
      "name": "Phase 2: Foundation Fixes",
      "tasks": ["3", "4", "7"]
    },
    {
      "name": "Phase 3: Dependent Fixes",
      "tasks": ["5", "6", "9"]
    },
    {
      "name": "Phase 4: Independent Fixes",
      "tasks": ["8", "10", "11"]
    },
    {
      "name": "Phase 5: Performance & Verification",
      "tasks": ["12", "13"]
    }
  ]
}
```

**Dependency Details:**

```
1 (Bug Exploration Tests) → 2 (Preservation Tests) → 3-12 (Implementation) → 13 (Final Checkpoint)

Implementation Dependencies:
- Task 3 (Scratch Card Fix) - Independent
- Task 4 (Permissions Fix) - Independent
- Task 5 (Result Workspace Fix) - Depends on Task 4 (needs permissions)
- Task 6 (Student 360 Fix) - Depends on Task 5 (needs result workspace)
- Task 7 (Communication Visibility Fix) - Independent
- Task 8 (Mail System Fix) - Independent
- Task 9 (Sidebar Fix) - Depends on Task 4, 7 (needs policies)
- Task 10 (Language Fix) - Independent
- Task 11 (Responsive Design Fix) - Independent
- Task 12 (Performance Fix) - Should be done last (affects all queries)

Recommended Implementation Order:
1. Tasks 1-2 (Exploration & Preservation Tests) - MUST be done first
2. Task 3 (Scratch Card) - Quick win, isolated fix
3. Task 4 (Permissions) - Foundation for other fixes
4. Task 7 (Communication Visibility) - Uses same policy pattern as Task 4
5. Task 9 (Sidebar) - Depends on policies from Tasks 4 & 7
6. Task 5 (Result Workspace) - Needs permissions from Task 4
7. Task 6 (Student 360) - Builds on Task 5
8. Task 8 (Mail System) - Independent, can be done anytime
9. Task 10 (Language) - Independent, can be done anytime
10. Task 11 (Responsive Design) - Independent, can be done anytime
11. Task 12 (Performance) - Should be last to optimize all queries
12. Task 13 (Final Checkpoint) - MUST be done last
```

## Notes

### Testing Approach

This bugfix uses the **bug condition methodology** with property-based testing:

1. **Exploration Tests (Task 1)**: Write tests that FAIL on unfixed code to confirm bugs exist
   - Tests encode the expected behavior
   - Failures are EXPECTED and confirm the bugs
   - Document counterexamples to understand root causes

2. **Preservation Tests (Task 2)**: Write tests that PASS on unfixed code to establish baseline
   - Use observation-first methodology
   - Property-based testing recommended for stronger guarantees
   - Tests ensure no regressions after fixes

3. **Implementation (Tasks 3-12)**: Fix each bug category systematically
   - Each fix includes verification that exploration tests now pass
   - Each fix includes verification that preservation tests still pass

4. **Final Checkpoint (Task 13)**: Verify all tests pass together

### Property-Based Testing

Property-based testing is **strongly recommended** for:
- Preservation tests (Task 2) - Generates many test cases automatically
- Bug condition tests (Task 1) - Can scope to concrete failing cases for deterministic bugs

Benefits:
- Catches edge cases that manual unit tests might miss
- Provides stronger guarantees about behavior across input domain
- Automatically generates diverse test cases

### Critical Reminders

- **DO NOT** fix bugs before writing exploration tests (Task 1)
- **DO NOT** write new tests in verification steps - rerun existing tests
- **DO NOT** skip preservation tests - they prevent regressions
- **DO** document counterexamples found during exploration
- **DO** follow the observation-first methodology for preservation tests
- **DO** verify both exploration and preservation tests after each fix

### File Locations

Key files to be created/modified:
- **Policies**: `app/Policies/ResultPolicy.php`, `app/Policies/CommunicationLogPolicy.php`
- **Middleware**: `app/Http/Middleware/EnsureResultEditable.php`, `app/Http/Middleware/EnsureCommunicationAccess.php`, `app/Http/Middleware/EnsureAuthorizedRoute.php`
- **Services**: `app/Services/ResultWorkflowService.php`, `app/Services/ResultValidationService.php`, `app/Services/SchoolMailService.php`, `app/Services/CacheService.php`
- **Controllers**: `app/Http/Controllers/School/Student360ResultController.php`
- **JavaScript**: `resources/js/result-workspace.js`, `resources/js/student-360-results.js`, `resources/js/sidebar-collapse.js`, `resources/js/dropdown-positioning.js`
- **CSS**: `resources/css/responsive-academic-grids.css`, `resources/css/responsive.css`, `resources/css/rtl.css`, `resources/css/dark-mode.css`
- **Config**: `config/sidebar.php`
- **Translations**: `resources/lang/en/backend.php`, `resources/lang/fr/backend.php`, `resources/lang/ar/backend.php`
- **Migrations**: Database indexes and encrypted SMTP credentials

### Scope

This is a comprehensive stabilization pass addressing 60 bug scenarios across 10 categories. The implementation will touch many parts of the codebase including:
- Backend: Controllers, Policies, Middleware, Services, Models
- Frontend: Blade templates, JavaScript, CSS
- Database: Migrations for indexes and encryption
- Configuration: Sidebar, translations, mail settings

Estimated effort: Large (multiple weeks for full implementation and testing)

### Success Criteria

All tasks complete when:
1. All exploration tests pass (bugs fixed)
2. All preservation tests pass (no regressions)
3. Full test suite passes
4. User review confirms fixes work as expected
