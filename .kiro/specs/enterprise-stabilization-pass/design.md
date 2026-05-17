# Enterprise Stabilization Pass - Bugfix Design

## Overview

This design addresses 10 critical bug categories affecting the Sanfaani Schools Multi-School SaaS platform (Laravel 13). The bugs span core business workflows including result management, permission enforcement, communication systems, mail delivery, role-based UI rendering, language system stability, responsive design, and performance optimization.

The fix strategy follows a systematic approach:
1. **Scratch Card Crash**: Fix event dispatch pattern
2. **Permission Enforcement**: Implement comprehensive authorization policies and middleware
3. **Result Workspace**: Complete missing UI components and workflow state machine
4. **Student 360**: Implement inline editing, autosave, and responsive grids
5. **Communication Visibility**: Enforce role-based access control across all layers
6. **Mail System**: Complete dynamic SMTP switching with failover
7. **Role Sidebar**: Implement role-based menu filtering with permission checks
8. **Language System**: Fix persistence and complete RTL support
9. **Responsive Design**: Fix CSS issues across breakpoints
10. **Performance**: Eliminate N+1 queries and implement caching

## Glossary

- **Bug_Condition (C)**: The condition that triggers each bug category
- **Property (P)**: The desired correct behavior for buggy inputs
- **Preservation**: Existing functionality that must remain unchanged
- **ResultCheckerController**: Controller in `app/Http/Controllers/Public/ResultCheckerController.php` handling scratch card result checking
- **StudentTransactionalEmailRequested**: Event in `app/Events/StudentTransactionalEmailRequested.php` for student transactional emails
- **Result Workflow States**: Draft → Submitted → Returned → Reviewed → Approved → Published → Unpublished → Archived → Locked
- **School_id Isolation**: Multi-tenancy pattern ensuring data segregation between schools
- **RBAC**: Role-Based Access Control (Super Admin, School Admin, Result Officer, Teachers)
- **N+1 Query**: Performance anti-pattern where relationships trigger multiple queries
- **Eager Loading**: Laravel optimization technique using `with()` to load relationships efficiently
- **RTL**: Right-to-Left text direction for Arabic language support


## Bug Details

### 1. Scratch Card Result Check Crash

**Bug Condition:**

The bug manifests when a student successfully completes scratch card verification and the system attempts to dispatch a transactional email notification. The `ResultCheckerController::check()` method incorrectly calls `StudentTransactionalEmailRequested::dispatch()` with an already-constructed event object instead of individual constructor parameters.

**Formal Specification:**
```
FUNCTION isBugCondition_ScratchCardCrash(input)
  INPUT: input of type ResultCheckRequest
  OUTPUT: boolean
  
  RETURN input.scratchCardValid = true
         AND input.resultPublished = true
         AND emailDispatchAttempted(input)
         AND dispatchPattern = "dispatch(constructedEvent)"
END FUNCTION
```

**Examples:**
- Student enters valid scratch card credentials → system crashes with `StudentTransactionalEmailRequested::__construct(): Argument #1 ($school) must be of type App\Models\School`
- Result check form submission with valid data → email dispatch fails, result does not load
- Audit log may not record successful access attempt due to crash before completion

**Root Cause:**
Line 237 in `ResultCheckerController.php`:
```php
StudentTransactionalEmailRequested::dispatch(
    StudentTransactionalEmailRequested::resultAvailable($student->loadMissing('school'), ...)
);
```

The `dispatch()` method expects individual constructor parameters but receives a constructed event object. Should be:
```php
StudentTransactionalEmailRequested::resultAvailable($student->loadMissing('school'), ...)->dispatch();
```

### 2. Result Entry Permissions Broken

**Bug Condition:**

The bug manifests when users attempt to edit results in protected workflow states (approved, published, locked) or access resources outside their authorization scope. The system lacks comprehensive authorization policies and middleware enforcement.

**Formal Specification:**
```
FUNCTION isBugCondition_PermissionsBroken(input)
  INPUT: input of type ResultEditRequest
  OUTPUT: boolean
  
  RETURN (input.resultStatus IN ['approved', 'published', 'locked']
          AND input.userRole = 'teacher'
          AND editOperationAllowed(input))
         OR (input.resourceScope NOT IN userAssignedScope(input.user)
             AND accessGranted(input))
         OR (input.userRole = 'result_officer'
             AND input.permissionFlags.disabled = true
             AND operationAllowed(input))
END FUNCTION
```

**Examples:**
- Teacher edits approved result → system allows instead of blocking
- Teacher accesses unassigned class → system grants access instead of denying
- Result Officer with disabled permissions performs restricted operation → system allows
- School Admin performs operation outside lifecycle scope → system may allow

**Root Cause:**
1. Missing authorization policies for Result model
2. No middleware enforcement on result edit routes
3. Incomplete permission flag checking for Result Officers
4. Missing scope validation for teacher assignments


### 3. Result Workspace Finalization Issues

**Bug Condition:**

The bug manifests when users perform result operations across three workspace contexts (Student 360, Result Management, Assigned Teacher Entry). Navigation breaks, inline actions are missing, columns are incomplete, and workflow state transitions are not enforced.

**Formal Specification:**
```
FUNCTION isBugCondition_WorkspaceIncomplete(input)
  INPUT: input of type ResultWorkspaceOperation
  OUTPUT: boolean
  
  RETURN (input.workspace IN ['student_360', 'result_management', 'teacher_entry']
          AND navigationBroken(input))
         OR (input.gridView = true
             AND inlineActionsMissing(input))
         OR (input.gridView = true
             AND columnsIncomplete(input))
         OR (input.stateTransition REQUESTED
             AND NOT validationEnforced(input))
         OR (input.operation = 'score_entry'
             AND NOT autoCalculationTriggered(input))
         OR (input.operation PERFORMED
             AND NOT auditLogged(input))
END FUNCTION
```

**Examples:**
- User enters result in Student 360 → navigation breaks during operation
- User views result grid → inline actions (Add, Edit, Save Draft, Submit, Approve) missing
- User views result grid → columns for Total, Grade, Pass/Fail, Audit Trail incomplete
- User attempts Draft → Submitted transition → validation not enforced
- User enters CA and Exam scores → total not auto-calculated

**Root Cause:**
1. Incomplete Blade templates for result grids
2. Missing JavaScript for inline editing and navigation
3. No workflow state machine implementation
4. Missing validation engine for auto-calculation
5. Incomplete audit logging integration

### 4. Student 360 Result Operations

**Bug Condition:**

The bug manifests when users interact with the Student 360 Result view. Inline editing, save draft, autosave, quick subject switching, validation indicators, and responsive grids are broken or missing.

**Formal Specification:**
```
FUNCTION isBugCondition_Student360Broken(input)
  INPUT: input of type Student360ResultOperation
  OUTPUT: boolean
  
  RETURN (input.operation = 'inline_edit'
          AND NOT inlineEditingWorks(input))
         OR (input.operation IN ['save_draft', 'autosave']
             AND NOT saveFunctionalityWorks(input))
         OR (input.operation = 'subject_switch'
             AND NOT quickSwitchingWorks(input))
         OR (input.view = 'validation'
             AND validationIndicatorsMissing(input))
         OR (input.deviceType IN ['mobile', 'tablet']
             AND responsiveGridsBroken(input))
END FUNCTION
```

**Examples:**
- User clicks inline edit in Student 360 → editing does not activate
- User clicks save draft → functionality broken, data not saved
- User switches subjects → quick switching broken, page reloads or fails
- User views validation → indicators missing, no visual feedback
- User accesses on mobile → responsive academic grids broken, unusable layout

**Root Cause:**
1. Missing JavaScript for inline editing functionality
2. Incomplete AJAX endpoints for draft saving and autosave
3. No subject switching implementation
4. Missing validation indicator components
5. Incomplete responsive CSS for academic grids


### 5. Communication Log Visibility Leak

**Bug Condition:**

The bug manifests when unauthorized roles (Teachers, Result Officers) access the system and can view communication logs that should be restricted to Super Admin and authorized School Admins only.

**Formal Specification:**
```
FUNCTION isBugCondition_CommunicationLeak(input)
  INPUT: input of type UserAccess
  OUTPUT: boolean
  
  RETURN (input.userRole IN ['teacher', 'result_officer']
          AND communicationLogsVisible(input))
         OR (input.userRole IN ['teacher', 'result_officer']
             AND directRouteAccessGranted(input, 'communication_logs'))
         OR (input.userRole IN ['teacher', 'result_officer']
             AND menuItemsVisible(input, 'communication'))
         OR (input.userRole IN ['teacher', 'result_officer']
             AND dashboardWidgetsVisible(input, 'communication'))
END FUNCTION
```

**Examples:**
- Teacher logs in → communication logs visible in sidebar/dashboard
- Result Officer accesses system → communication log menu items visible
- Unauthorized role accesses `/school/communications` directly → access granted
- Teacher views dashboard → communication widgets/cards visible

**Root Cause:**
1. Missing authorization policies for CommunicationLog model
2. No middleware enforcement on communication routes
3. Blade templates not checking role permissions for menu items
4. Dashboard widgets not filtered by role
5. Missing gate checks in controllers

### 6. School Mail System Issues

**Bug Condition:**

The bug manifests when schools configure SMTP settings or when emails are sent. School-specific SMTP does not work properly, fallback is not configured, queue compatibility has issues, and not all email types work correctly.

**Formal Specification:**
```
FUNCTION isBugCondition_MailSystemBroken(input)
  INPUT: input of type EmailSendRequest
  OUTPUT: boolean
  
  RETURN (input.schoolSMTPConfigured = true
          AND NOT schoolSMTPWorks(input))
         OR (input.schoolSMTPFailed = true
             AND NOT fallbackActivated(input))
         OR (input.emailQueued = true
             AND queueCompatibilityIssues(input))
         OR (input.emailType IN ['transactional', 'branded', 'notification', 
                                  'support', 'scratch_card', 'result_publication']
             AND NOT emailTypeWorks(input))
         OR (input.operation IN ['resolve_driver', 'switch_smtp', 'load_config',
                                  'queue_safe', 'failover', 'encrypt_credentials']
             AND operationIncomplete(input))
END FUNCTION
```

**Examples:**
- School configures SMTP → transactional emails fail to send
- School SMTP fails → fallback SMTP not activated, emails not sent
- Email queued → queue worker fails due to serialization issues
- Scratch card email sent → email not delivered or uses wrong SMTP
- System attempts dynamic SMTP switch → operation fails or incomplete

**Root Cause:**
1. Incomplete MailConfigInterface implementation
2. Missing dynamic SMTP switching logic in mail service
3. Queue serialization issues with per-school mail configuration
4. Incomplete failover behavior
5. Missing encrypted credential storage for SMTP passwords


### 7. Role Sidebar Issues

**Bug Condition:**

The bug manifests when users view the sidebar navigation. Duplicated menu items are rendered, unauthorized modules are visible, permission leakage occurs, orphan links exist, and hidden routes are accessible.

**Formal Specification:**
```
FUNCTION isBugCondition_SidebarBroken(input)
  INPUT: input of type SidebarRender
  OUTPUT: boolean
  
  RETURN (duplicatedMenuItems(input))
         OR (input.userRole = role
             AND unauthorizedModulesVisible(input, role))
         OR (permissionLeakageOccurs(input))
         OR (orphanLinksPresent(input))
         OR (hiddenRoutesAccessible(input))
         OR (input.userRole = 'super_admin'
             AND nonPlatformModulesVisible(input))
         OR (input.userRole = 'school_admin'
             AND nonSchoolModulesVisible(input))
         OR (input.userRole = 'result_officer'
             AND nonResultModulesVisible(input))
         OR (input.userRole = 'teacher'
             AND nonAssignedToolsVisible(input))
END FUNCTION
```

**Examples:**
- User views sidebar → "Results" menu item appears twice
- Teacher views sidebar → "Communication Logs" module visible (should be hidden)
- Result Officer views sidebar → "Student Management" visible (should be hidden)
- Super Admin views sidebar → School operational modules visible (should be hidden)
- User clicks hidden menu item → route accessible despite being hidden

**Root Cause:**
1. Sidebar Blade component not filtering by role
2. Menu configuration includes duplicates
3. Missing permission checks in menu rendering logic
4. Route middleware not enforcing authorization
5. Incomplete role-to-module mapping

### 8. Language System Instability

**Bug Condition:**

The bug manifests when users change language preferences or when translations are loaded. Translation persistence is broken, backend translations are incomplete, navbar/validation/dashboard translations have issues, session persistence fails, and RTL support is incomplete.

**Formal Specification:**
```
FUNCTION isBugCondition_LanguageBroken(input)
  INPUT: input of type LanguageOperation
  OUTPUT: boolean
  
  RETURN (input.operation = 'change_preference'
          AND NOT translationPersisted(input))
         OR (input.context = 'backend'
             AND translationLoadingIncomplete(input))
         OR (input.component IN ['navbar', 'validation', 'dashboard']
             AND translationIssues(input))
         OR (input.operation = 'persist'
             AND NOT sessionPersistenceWorks(input))
         OR (input.language = 'ar'
             AND rtlSupportIncomplete(input))
END FUNCTION
```

**Examples:**
- User changes language to French → preference not saved, reverts to English on reload
- Backend loads translations → some areas still show English keys
- User views navbar in Arabic → translation missing or incorrect
- User selects Arabic → RTL layout has spacing and alignment issues
- Session stores language preference → preference lost on next request

**Root Cause:**
1. SetLocale middleware not persisting to database
2. Incomplete translation files for backend areas
3. Missing translation keys in Blade templates
4. Session persistence logic incomplete
5. Incomplete RTL CSS with spacing/alignment issues


### 9. Responsive Design Issues

**Bug Condition:**

The bug manifests when users access the application on mobile or tablet devices. Overlapping text, broken table overflow, hidden buttons, z-index issues, sidebar collapse bugs, dropdown overflow, dark mode contrast problems, sticky header failures, and unusable layouts on low-end Android devices occur.

**Formal Specification:**
```
FUNCTION isBugCondition_ResponsiveBroken(input)
  INPUT: input of type DeviceAccess
  OUTPUT: boolean
  
  RETURN (input.deviceType IN ['mobile', 'tablet']
          AND (overlappingText(input)
               OR brokenTableOverflow(input)
               OR hiddenButtons(input)))
         OR (input.deviceType = 'mobile'
             AND (zIndexIssues(input)
                  OR sidebarCollapseBugs(input)
                  OR mobileTopbarIssues(input)))
         OR (dropdownOverflow(input)
             OR darkModeContrastProblems(input))
         OR (stickyHeadersNotWorking(input)
             OR tablesNotScrolling(input)
             OR cardsNotAligning(input))
         OR (input.device = 'low_end_android'
             AND layoutsUnusable(input))
END FUNCTION
```

**Examples:**
- User views table on mobile → text overlaps, table does not scroll horizontally
- User clicks button on tablet → button hidden behind other elements
- User opens dropdown on mobile → dropdown overflows viewport, not scrollable
- User views dark mode → contrast too low, text unreadable
- User scrolls table → sticky headers do not work, headers scroll away
- User accesses on low-end Android → layout completely broken, unusable

**Root Cause:**
1. Missing responsive CSS breakpoints
2. Tables not wrapped in scrollable containers
3. Z-index conflicts in CSS
4. Sidebar collapse JavaScript incomplete
5. Dropdown positioning not viewport-aware
6. Dark mode color palette insufficient contrast
7. Sticky header CSS incomplete
8. Heavy CSS/JS causing performance issues on low-end devices

### 10. Performance Issues

**Bug Condition:**

The bug manifests when database queries are executed, especially for large schools with thousands of students. N+1 queries occur, eager loading is missing, indexed queries are not optimized, pagination is not optimized, lazy loading is not implemented, query scopes are missing, safe caching is not implemented, and the system becomes slow or unusable.

**Formal Specification:**
```
FUNCTION isBugCondition_PerformanceSlow(input)
  INPUT: input of type DatabaseOperation
  OUTPUT: boolean
  
  RETURN (nPlusOneQueriesOccur(input))
         OR (relationshipsLoaded(input)
             AND NOT eagerLoadingUsed(input))
         OR (queriesExecuted(input)
             AND NOT indexedQueryOptimized(input))
         OR (largeResultSet(input)
             AND NOT paginationOptimized(input))
         OR (resourcesLoaded(input)
             AND NOT lazyLoadingImplemented(input))
         OR (commonFilterPattern(input)
             AND queryScopesMissing(input))
         OR (dataAccessedRepeatedly(input)
             AND NOT safeCachingImplemented(input))
         OR (input.schoolSize = 'large'
             AND input.studentCount > 1000
             AND systemSlowOrUnusable(input))
END FUNCTION
```

**Examples:**
- System loads result list → N+1 queries for student, class, subject relationships
- System loads 1000 students → missing eager loading causes 3000+ queries
- System queries results by status → no index on status column, full table scan
- System displays 5000 results → no pagination, page load takes 30+ seconds
- System loads dashboard → all data loaded upfront, no lazy loading
- System filters results by term → no query scope, repeated WHERE clauses
- System accesses school settings repeatedly → no caching, queries on every request
- Large school with 3000 students → system becomes unusable, timeouts occur

**Root Cause:**
1. Missing `with()` calls for eager loading relationships
2. Missing database indexes on frequently queried columns
3. No pagination on large result sets
4. No lazy loading for dashboard widgets
5. Missing query scopes for common filters
6. No caching layer for frequently accessed data
7. Inefficient queries in loops


## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Valid scratch card result checking with correct credentials must continue to work
- Authorized result entry operations within user scope must continue to function
- Super Admin and authorized School Admin access to communication logs must remain unchanged
- Platform-level SMTP fallback when school SMTP not configured must continue to work
- Email delivery with school branding must remain functional
- Role-based access control for authorized features must continue to grant access
- Multi-tenancy school_id isolation must remain enforced
- Language preferences for English, French, and Arabic must continue to work
- Desktop layout rendering must remain correct
- Existing database indexes and query performance for small/medium schools must be preserved
- Audit logging for all audit-worthy actions must continue to function
- Student 360 non-result sections (profile, enrollment) must remain functional
- Report card generation must continue to produce accurate output
- Result publication visibility controls must remain enforced

**Scope:**
All inputs that do NOT involve the specific bug conditions should be completely unaffected by these fixes. This includes:
- Valid user operations within their authorized scope
- Existing workflows that are functioning correctly
- Desktop user experience for non-buggy features
- Small to medium school performance characteristics
- Existing caching and optimization strategies
- Non-result management features
- Platform administration features
- Existing email delivery for non-affected email types


## Hypothesized Root Cause

Based on the bug analysis, the root causes are:

### 1. Scratch Card Result Check Crash
**Root Cause**: Incorrect event dispatch pattern in `ResultCheckerController::check()` method (line 237). The code calls `dispatch()` on an already-constructed event object instead of calling `dispatch()` on the event instance or passing constructor parameters to the static `dispatch()` method.

### 2. Result Entry Permissions Broken
**Root Cause**: Missing authorization layer across multiple levels:
- No Laravel Policy for Result model
- Missing middleware on result edit routes
- Incomplete permission flag validation for Result Officers
- No scope validation for teacher class/subject assignments
- Missing workflow state checks before allowing edits

### 3. Result Workspace Finalization Issues
**Root Cause**: Incomplete feature implementation:
- Blade templates missing inline action buttons and complete column definitions
- JavaScript for inline editing and navigation not implemented
- No workflow state machine to enforce transitions
- Missing validation engine for auto-calculation of totals/grades
- Incomplete audit logging integration in result operations

### 4. Student 360 Result Operations
**Root Cause**: Missing frontend functionality:
- JavaScript for inline editing not implemented
- AJAX endpoints for draft saving and autosave not created
- Subject switching functionality not implemented
- Validation indicator components not built
- Responsive CSS for academic grids incomplete

### 5. Communication Log Visibility Leak
**Root Cause**: Missing authorization enforcement:
- No Laravel Policy for CommunicationLog model
- Routes not protected with authorization middleware
- Blade templates not checking permissions before rendering menu items
- Dashboard widgets not filtered by role
- Missing Gate checks in controllers

### 6. School Mail System Issues
**Root Cause**: Incomplete mail configuration system:
- MailConfigInterface implementation incomplete
- Dynamic SMTP switching logic not implemented
- Queue serialization issues with school-specific mail config
- Failover behavior not implemented
- SMTP credentials not encrypted in database

### 7. Role Sidebar Issues
**Root Cause**: Missing role-based filtering:
- Sidebar Blade component not filtering menu items by role
- Menu configuration contains duplicates
- Permission checks not implemented in menu rendering
- Route middleware not enforcing authorization consistently
- Role-to-module mapping incomplete

### 8. Language System Instability
**Root Cause**: Incomplete localization implementation:
- SetLocale middleware not persisting preference to database
- Translation files incomplete for backend areas
- Missing translation keys in Blade templates
- Session persistence logic incomplete
- RTL CSS incomplete with spacing/alignment issues

### 9. Responsive Design Issues
**Root Cause**: Incomplete responsive CSS implementation:
- Missing responsive breakpoints for mobile/tablet
- Tables not wrapped in scrollable containers
- Z-index conflicts in CSS
- Sidebar collapse JavaScript incomplete
- Dropdown positioning not viewport-aware
- Dark mode color palette insufficient contrast
- Sticky header CSS incomplete
- Heavy CSS/JS causing performance issues on low-end devices

### 10. Performance Issues
**Root Cause**: Missing query optimization:
- Relationships loaded without `with()` eager loading
- Missing database indexes on frequently queried columns
- No pagination on large result sets
- No lazy loading for dashboard widgets
- Missing query scopes for common filters
- No caching layer for frequently accessed data
- Inefficient queries in loops causing N+1 problems


## Correctness Properties

Property 1: Bug Condition - Scratch Card Email Dispatch

_For any_ scratch card result check where the student successfully verifies credentials and the result is published, the fixed system SHALL dispatch the StudentTransactionalEmailRequested event correctly without crashing, the result SHALL load and display to the user, and the audit log SHALL record the successful access attempt.

**Validates: Requirements 2.1, 2.2, 2.3**

Property 2: Bug Condition - Result Entry Permission Enforcement

_For any_ result edit attempt where the result status is approved, published, or locked, OR where the user attempts to access resources outside their assigned scope, OR where a Result Officer has disabled permission flags, the fixed system SHALL prevent the operation and display an appropriate error message.

**Validates: Requirements 2.4, 2.5, 2.6, 2.7, 2.8, 2.9**

Property 3: Bug Condition - Result Workspace Completeness

_For any_ result operation in Student 360, Result Management, or Assigned Teacher Entry workspaces, the fixed system SHALL provide working navigation, complete inline actions (Add, Edit, Save Draft, Submit, Return, Approve, Publish, Unpublish, View Audit Log), complete columns (CA, Exam, Total, Grade, Pass/Fail, Remarks, Status, Source, Timestamps, Audit Trail), enforced workflow state transitions, automatic calculation of totals/grades/pass-fail status, and complete audit logging.

**Validates: Requirements 2.10, 2.11, 2.12, 2.13, 2.14, 2.15, 2.16, 2.17**

Property 4: Bug Condition - Student 360 Result Operations

_For any_ Student 360 result operation including inline editing, save draft, autosave, quick subject switching, validation indicators, publish visibility, audit visibility, or responsive grid viewing on mobile/tablet, the fixed system SHALL provide fully functional operations with proper visual feedback and responsive layouts.

**Validates: Requirements 2.18, 2.19, 2.20, 2.21, 2.22, 2.23, 2.24**

Property 5: Bug Condition - Communication Log Visibility Enforcement

_For any_ access attempt by Teachers or Result Officers to communication logs (via UI, direct routes, menu items, widgets, dashboard cards, quick links, API access, or navigation badges), the fixed system SHALL hide communication logs from unauthorized roles and enforce backend authorization policies denying access with appropriate errors.

**Validates: Requirements 2.25, 2.26, 2.27, 2.28**

Property 6: Bug Condition - School Mail System Completion

_For any_ email sending operation where school SMTP is configured or where school SMTP fails, the fixed system SHALL properly use school SMTP settings for all email types (transactional, branded, notification, support, scratch card, result publication), activate fallback SMTP when school SMTP fails or is not configured, ensure queue compatibility, and complete all mail system operations (resolve drivers, switch SMTP dynamically, load per-school configuration, handle queue-safe mail, implement failover behavior, store encrypted credentials).

**Validates: Requirements 2.29, 2.30, 2.31, 2.32, 2.33**

Property 7: Bug Condition - Role Sidebar Cleanup

_For any_ sidebar rendering operation, the fixed system SHALL eliminate duplicated menu items, show only authorized modules for each role (Platform modules for Super Admin, School operational modules for School Admin, Result workspace modules for Result Officer, Assigned operational tools for Teachers), prevent permission leakage, eliminate orphan links, and properly protect hidden routes from direct access.

**Validates: Requirements 2.34, 2.35, 2.36, 2.37, 2.38, 2.39, 2.40, 2.41, 2.42**

Property 8: Bug Condition - Language System Stabilization

_For any_ language preference change or translation loading operation, the fixed system SHALL persist translation preferences correctly, load complete backend translations for all areas, display correct translations in navbar/validation messages/dashboard elements, persist session and user preferences correctly, and provide complete RTL support for Arabic with proper spacing and alignment.

**Validates: Requirements 2.43, 2.44, 2.45, 2.46, 2.47**

Property 9: Bug Condition - Responsive Design Stabilization

_For any_ access on mobile or tablet devices, the fixed system SHALL eliminate overlapping text, broken table overflow, and hidden buttons, resolve z-index issues, sidebar collapse bugs, and mobile topbar issues, fix dropdown overflow and dark mode contrast problems, implement working sticky headers, correct table scrolling, proper card alignment, and provide usable layouts on low-end Android devices.

**Validates: Requirements 2.48, 2.49, 2.50, 2.51, 2.52**

Property 10: Bug Condition - Performance Optimization

_For any_ database operation, the fixed system SHALL eliminate N+1 queries through proper eager loading, implement eager loading for all relationships, optimize indexed query usage, implement pagination optimization for large result sets, implement lazy loading for resources, provide query scopes for common filtering patterns, implement safe caching for repeatedly accessed data, and maintain acceptable performance for large schools with thousands of students.

**Validates: Requirements 2.53, 2.54, 2.55, 2.56, 2.57, 2.58, 2.59, 2.60**

Property 11: Preservation - Existing Functionality

_For any_ operation that does NOT involve the specific bug conditions (valid operations within authorized scope, existing correct workflows, desktop non-buggy features, small/medium school operations, non-result features, platform administration, non-affected email types), the fixed system SHALL produce exactly the same behavior as the original system, preserving all existing functionality including valid scratch card checking, authorized result operations, Super Admin/authorized School Admin communication access, platform SMTP fallback, email branding, RBAC for authorized features, school_id isolation, language preferences, desktop layouts, existing performance, audit logging, Student 360 non-result sections, report card generation, and result publication visibility.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10, 3.11, 3.12, 3.13, 3.14, 3.15, 3.16, 3.17, 3.18, 3.19, 3.20, 3.21, 3.22, 3.23, 3.24, 3.25, 3.26, 3.27, 3.28, 3.29, 3.30**


## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct:

#### 1. Scratch Card Result Check Crash

**File**: `app/Http/Controllers/Public/ResultCheckerController.php`

**Function**: `check()`

**Specific Changes**:
1. **Fix Event Dispatch Pattern**: Change line 237 from:
   ```php
   StudentTransactionalEmailRequested::dispatch(
       StudentTransactionalEmailRequested::resultAvailable($student->loadMissing('school'), ...)
   );
   ```
   To:
   ```php
   StudentTransactionalEmailRequested::resultAvailable(
       $student->loadMissing('school'), 
       $academicSession, 
       $term, 
       [
           'result_type' => $data['result_type'],
           'scratch_card_id' => $scratchCardAccess['scratchCard']?->id,
       ]
   )->dispatch();
   ```

2. **Add Error Handling**: Wrap email dispatch in try-catch to prevent crashes from blocking result display

3. **Verify Audit Logging**: Ensure audit log is recorded before email dispatch attempt

#### 2. Result Entry Permissions Enforcement

**Files**: 
- `app/Policies/ResultPolicy.php` (create)
- `app/Http/Middleware/EnsureResultEditable.php` (create)
- `app/Http/Controllers/School/ResultController.php` (modify)
- `routes/web.php` (modify)

**Specific Changes**:
1. **Create ResultPolicy**: Implement authorization methods:
   - `update()`: Check result status (not approved/published/locked) and user role
   - `approve()`: Check user is Result Officer or School Admin with permission
   - `publish()`: Check user is School Admin with permission
   - `viewAny()`: Check user has access to school and assigned classes (for teachers)

2. **Create EnsureResultEditable Middleware**: Check workflow state before allowing edits

3. **Add Policy Checks**: Add `$this->authorize('update', $result)` in controller methods

4. **Add Middleware to Routes**: Apply `EnsureResultEditable` to result edit routes

5. **Implement Teacher Scope Validation**: Check `ClassSubjectAssignment` for teacher access

6. **Implement Result Officer Permission Flags**: Check permission flags in policy methods

#### 3. Result Workspace Finalization

**Files**:
- `resources/views/school/results/partials/result-grid.blade.php` (create/modify)
- `resources/js/result-workspace.js` (create)
- `app/Services/ResultWorkflowService.php` (create)
- `app/Services/ResultValidationService.php` (create)
- `app/Http/Controllers/School/ResultController.php` (modify)

**Specific Changes**:
1. **Complete Result Grid Blade Template**: Add all inline action buttons (Add, Edit, Save Draft, Submit, Return, Approve, Publish, Unpublish, View Audit Log) with proper authorization checks

2. **Add Complete Columns**: Ensure grid includes CA, Exam, Total, Grade, Pass/Fail, Remarks, Status, Source, Timestamps, Audit Trail columns

3. **Implement JavaScript for Inline Editing**: Create `result-workspace.js` with inline editing, navigation handling, and AJAX operations

4. **Create ResultWorkflowService**: Implement state machine for workflow transitions:
   - `canTransition($result, $toStatus)`: Validate state transitions
   - `transition($result, $toStatus, $user)`: Execute transition with audit logging
   - Valid transitions: Draft → Submitted → Returned → Reviewed → Approved → Published → Unpublished → Archived → Locked

5. **Create ResultValidationService**: Implement auto-calculation:
   - `calculateTotal($ca, $exam)`: Calculate total score
   - `applyGrading($total, $gradingScale)`: Apply grading scale
   - `detectPassFail($grade)`: Determine pass/fail status
   - `preventDuplicates($student, $subject, $term)`: Check for existing results

6. **Integrate Audit Logging**: Call `AuditLogService::log()` for all result operations

#### 4. Student 360 Result Operations

**Files**:
- `resources/views/school/students/partials/student-360-results.blade.php` (modify)
- `resources/js/student-360-results.js` (create)
- `app/Http/Controllers/School/Student360ResultController.php` (create)
- `resources/css/responsive-academic-grids.css` (create)

**Specific Changes**:
1. **Implement Inline Editing JavaScript**: Create `student-360-results.js` with:
   - Click handlers for inline edit activation
   - Input field rendering and validation
   - Save/cancel handlers

2. **Create AJAX Endpoints**: Create `Student360ResultController` with:
   - `saveDraft()`: Save result as draft
   - `autosave()`: Auto-save on input change (debounced)
   - `switchSubject()`: Load results for different subject

3. **Implement Quick Subject Switching**: Add subject dropdown with AJAX loading

4. **Add Validation Indicators**: Create validation indicator components showing:
   - Required field indicators
   - Score range validation
   - Duplicate detection warnings

5. **Implement Responsive Academic Grids**: Create `responsive-academic-grids.css` with:
   - Mobile breakpoints for tables
   - Horizontal scroll containers
   - Card-based layout for mobile
   - Touch-friendly input controls


#### 5. Communication Log Visibility Enforcement

**Files**:
- `app/Policies/CommunicationLogPolicy.php` (create)
- `app/Http/Middleware/EnsureCommunicationAccess.php` (create)
- `resources/views/layouts/partials/sidebar.blade.php` (modify)
- `resources/views/school/dashboard.blade.php` (modify)
- `routes/web.php` (modify)

**Specific Changes**:
1. **Create CommunicationLogPolicy**: Implement authorization methods:
   - `viewAny()`: Return true only for Super Admin and School Admin with explicit permission
   - `view()`: Check user can access specific communication log
   - `create()`: Check user can create communication logs

2. **Create EnsureCommunicationAccess Middleware**: Check user role and permissions before allowing access to communication routes

3. **Add Middleware to Routes**: Apply `EnsureCommunicationAccess` to all communication log routes

4. **Update Sidebar Blade Template**: Add `@can('viewAny', App\Models\CommunicationLog::class)` checks before rendering communication menu items

5. **Update Dashboard Blade Template**: Add `@can('viewAny', App\Models\CommunicationLog::class)` checks before rendering communication widgets/cards

6. **Add Gate Checks in Controllers**: Add `$this->authorize('viewAny', CommunicationLog::class)` in controller methods

#### 6. School Mail System Completion

**Files**:
- `app/Services/SchoolMailService.php` (create)
- `app/Mail/SchoolMailable.php` (create)
- `app/Contracts/MailConfigInterface.php` (modify)
- `app/Listeners/SendStudentTransactionalEmail.php` (modify)
- `app/Listeners/SendStaffTransactionalEmail.php` (modify)
- `database/migrations/xxxx_add_encrypted_smtp_credentials.php` (create)

**Specific Changes**:
1. **Create SchoolMailService**: Implement dynamic SMTP switching:
   - `resolveMailDriver($school)`: Determine which SMTP to use (school or platform)
   - `configureSchoolSMTP($school)`: Configure Laravel mailer with school SMTP settings
   - `configureFallbackSMTP()`: Configure platform SMTP as fallback
   - `switchSMTP($school)`: Dynamically switch SMTP configuration
   - `testConnection($config)`: Test SMTP connection before use

2. **Create SchoolMailable Base Class**: Extend Laravel Mailable with:
   - `buildWithSchoolConfig($school)`: Build email with school-specific SMTP
   - Queue-safe serialization of school mail config
   - Automatic failover to platform SMTP on failure

3. **Implement Failover Behavior**: In mail listeners, wrap mail sending in try-catch:
   - Try school SMTP first
   - On failure, log error and retry with platform SMTP
   - Update communication log with delivery status

4. **Encrypt SMTP Credentials**: 
   - Add migration to encrypt existing SMTP passwords in `mail_settings` table
   - Use Laravel's `Crypt::encrypt()` for password storage
   - Decrypt in `SchoolMailService` when configuring SMTP

5. **Update Mail Listeners**: Modify `SendStudentTransactionalEmail` and `SendStaffTransactionalEmail` to use `SchoolMailService` for SMTP resolution

6. **Implement Queue-Safe Mail**: Ensure school mail config is serializable for queue workers:
   - Store school_id instead of School model in queued jobs
   - Resolve school and configure SMTP in queue worker
   - Handle school deletion gracefully in queue

#### 7. Role Sidebar Cleanup

**Files**:
- `resources/views/layouts/partials/sidebar.blade.php` (modify)
- `app/View/Components/Sidebar.php` (create)
- `config/sidebar.php` (create)
- `app/Http/Middleware/EnsureAuthorizedRoute.php` (create)
- `routes/web.php` (modify)

**Specific Changes**:
1. **Create Sidebar Configuration**: Create `config/sidebar.php` with role-to-module mapping:
   ```php
   'super_admin' => ['platform_settings', 'schools', 'subscriptions', 'audit_logs'],
   'school_admin' => ['dashboard', 'students', 'staff', 'classes', 'results', 'communications', 'settings'],
   'result_officer' => ['dashboard', 'results', 'report_cards'],
   'teacher' => ['dashboard', 'my_classes', 'my_results'],
   ```

2. **Create Sidebar Component**: Create `app/View/Components/Sidebar.php` with:
   - `filterMenuByRole($user)`: Filter menu items based on user role
   - `checkPermissions($menuItem, $user)`: Check if user has permission for menu item
   - `removeDuplicates($menu)`: Remove duplicate menu items
   - `removeOrphanLinks($menu)`: Remove links to non-existent routes

3. **Update Sidebar Blade Template**: Use Sidebar component to render filtered menu

4. **Create EnsureAuthorizedRoute Middleware**: Check user has permission to access route even if they know the URL

5. **Apply Middleware to Routes**: Add `EnsureAuthorizedRoute` to all protected routes

6. **Implement Role-Specific Filtering**:
   - Super Admin: Show only Platform modules
   - School Admin: Show only School operational modules
   - Result Officer: Show only Result workspace modules
   - Teachers: Show only Assigned operational tools


#### 8. Language System Stabilization

**Files**:
- `app/Http/Middleware/SetLocale.php` (modify)
- `app/Models/LanguagePreference.php` (modify)
- `resources/lang/en/backend.php` (create)
- `resources/lang/fr/backend.php` (create)
- `resources/lang/ar/backend.php` (create)
- `resources/css/rtl.css` (modify)
- `resources/views/layouts/partials/navbar.blade.php` (modify)

**Specific Changes**:
1. **Fix SetLocale Middleware**: Modify to persist language preference:
   ```php
   public function handle($request, Closure $next)
   {
       $locale = $this->resolveLocale($request);
       app()->setLocale($locale);
       
       // Persist to database if authenticated
       if ($request->user()) {
           LanguagePreference::updateOrCreate(
               ['user_id' => $request->user()->id],
               ['language' => $locale]
           );
       }
       
       // Persist to session
       session(['locale' => $locale]);
       
       return $next($request);
   }
   ```

2. **Complete Backend Translation Files**: Create/update translation files for:
   - `backend.php`: Backend-specific translations (dashboard, navigation, buttons)
   - Ensure all translation keys used in Blade templates exist
   - Add missing translations for validation messages
   - Add missing translations for navbar elements

3. **Fix Translation Keys in Blade Templates**: Replace hardcoded text with `__()` calls:
   - Navbar: `{{ __('backend.dashboard') }}`
   - Validation: `{{ __('validation.required') }}`
   - Dashboard: `{{ __('backend.welcome') }}`

4. **Complete RTL Support**: Modify `resources/css/rtl.css`:
   - Fix spacing issues: Add proper padding/margin for RTL
   - Fix alignment issues: Use `text-align: right` for RTL
   - Fix float issues: Use `float: right` for RTL elements
   - Fix flexbox direction: Use `flex-direction: row-reverse` for RTL
   - Test all major UI components in RTL mode

5. **Fix Session Persistence**: Ensure session stores locale correctly:
   - Check session driver configuration
   - Verify session is saved after locale change
   - Test locale persistence across requests

#### 9. Responsive Design Stabilization

**Files**:
- `resources/css/responsive.css` (create/modify)
- `resources/css/tables.css` (modify)
- `resources/css/mobile.css` (create)
- `resources/css/dark-mode.css` (modify)
- `resources/js/sidebar-collapse.js` (modify)
- `resources/js/dropdown-positioning.js` (create)

**Specific Changes**:
1. **Fix Overlapping Text and Hidden Buttons**: Add responsive breakpoints:
   ```css
   @media (max-width: 768px) {
       .btn { font-size: 0.875rem; padding: 0.5rem 0.75rem; }
       .text-truncate { max-width: 150px; }
   }
   ```

2. **Fix Table Overflow**: Wrap tables in scrollable containers:
   ```html
   <div class="table-responsive">
       <table class="table">...</table>
   </div>
   ```
   ```css
   .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
   ```

3. **Fix Z-Index Issues**: Create z-index scale:
   ```css
   .sidebar { z-index: 1000; }
   .navbar { z-index: 1010; }
   .dropdown { z-index: 1020; }
   .modal { z-index: 1030; }
   ```

4. **Fix Sidebar Collapse**: Complete `sidebar-collapse.js`:
   - Add mobile toggle button
   - Implement collapse/expand animation
   - Handle touch events
   - Persist collapse state in localStorage

5. **Fix Dropdown Overflow**: Create `dropdown-positioning.js`:
   - Detect viewport boundaries
   - Reposition dropdown if overflowing
   - Add scrolling for long dropdowns
   - Handle mobile touch events

6. **Fix Dark Mode Contrast**: Update `dark-mode.css`:
   ```css
   .dark-mode { background: #1a1a1a; color: #e0e0e0; }
   .dark-mode .card { background: #2a2a2a; border-color: #3a3a3a; }
   .dark-mode .btn-primary { background: #4a90e2; color: #ffffff; }
   ```

7. **Fix Sticky Headers**: Add sticky header CSS:
   ```css
   .table thead th { position: sticky; top: 0; background: #fff; z-index: 10; }
   ```

8. **Optimize for Low-End Android**: 
   - Reduce CSS complexity
   - Minimize JavaScript execution
   - Use CSS transforms instead of position changes
   - Lazy load images and heavy components


#### 10. Performance Optimization

**Files**:
- `app/Http/Controllers/School/ResultController.php` (modify)
- `app/Http/Controllers/School/StudentController.php` (modify)
- `app/Models/Result.php` (modify)
- `app/Models/Student.php` (modify)
- `database/migrations/xxxx_add_performance_indexes.php` (create)
- `app/Services/CacheService.php` (create)

**Specific Changes**:
1. **Eliminate N+1 Queries**: Add eager loading to all relationship queries:
   ```php
   // Before (N+1)
   $results = Result::where('term_id', $termId)->get();
   foreach ($results as $result) {
       echo $result->student->name; // N+1 query
   }
   
   // After (Eager Loading)
   $results = Result::with(['student', 'subject', 'schoolClass'])
       ->where('term_id', $termId)
       ->get();
   ```

2. **Add Database Indexes**: Create migration to add indexes:
   ```php
   Schema::table('results', function (Blueprint $table) {
       $table->index('status');
       $table->index('workflow_status');
       $table->index(['school_id', 'term_id', 'academic_session_id']);
       $table->index(['student_id', 'subject_id', 'term_id']);
   });
   
   Schema::table('students', function (Blueprint $table) {
       $table->index('status');
       $table->index(['school_id', 'school_class_id']);
   });
   
   Schema::table('communication_logs', function (Blueprint $table) {
       $table->index('status');
       $table->index(['school_id', 'created_at']);
   });
   ```

3. **Implement Pagination**: Add pagination to large result sets:
   ```php
   // Before (Load all)
   $students = Student::where('school_id', $schoolId)->get();
   
   // After (Paginated)
   $students = Student::where('school_id', $schoolId)
       ->paginate(50);
   ```

4. **Implement Lazy Loading**: Use lazy loading for dashboard widgets:
   ```javascript
   // Load widgets on scroll or on-demand
   document.addEventListener('DOMContentLoaded', function() {
       const widgets = document.querySelectorAll('[data-lazy-widget]');
       const observer = new IntersectionObserver((entries) => {
           entries.forEach(entry => {
               if (entry.isIntersecting) {
                   loadWidget(entry.target);
                   observer.unobserve(entry.target);
               }
           });
       });
       widgets.forEach(widget => observer.observe(widget));
   });
   ```

5. **Create Query Scopes**: Add query scopes to models:
   ```php
   // In Result model
   public function scopeForTerm($query, $termId)
   {
       return $query->where('term_id', $termId);
   }
   
   public function scopePublished($query)
   {
       return $query->where('workflow_status', 'published');
   }
   
   public function scopeForSchool($query, $schoolId)
   {
       return $query->where('school_id', $schoolId);
   }
   
   // Usage
   $results = Result::forSchool($schoolId)
       ->forTerm($termId)
       ->published()
       ->with(['student', 'subject'])
       ->get();
   ```

6. **Implement Safe Caching**: Create `CacheService` for frequently accessed data:
   ```php
   class CacheService
   {
       public function getSchoolSettings($schoolId)
       {
           return Cache::remember("school_settings_{$schoolId}", 3600, function() use ($schoolId) {
               return School::with('settings')->find($schoolId);
           });
       }
       
       public function getGradingScale($schoolId)
       {
           return Cache::remember("grading_scale_{$schoolId}", 3600, function() use ($schoolId) {
               return GradingScale::where('school_id', $schoolId)->get();
           });
       }
       
       public function invalidateSchoolCache($schoolId)
       {
           Cache::forget("school_settings_{$schoolId}");
           Cache::forget("grading_scale_{$schoolId}");
       }
   }
   ```

7. **Optimize Controller Queries**: Update controllers to use optimized queries:
   ```php
   // In ResultController
   public function index(Request $request)
   {
       $results = Result::with(['student', 'subject', 'schoolClass', 'term', 'academicSession'])
           ->forSchool($request->user()->school_id)
           ->forTerm($request->input('term_id'))
           ->when($request->input('status'), function($query, $status) {
               return $query->where('workflow_status', $status);
           })
           ->paginate(50);
       
       return view('school.results.index', compact('results'));
   }
   ```

8. **Add Performance Monitoring**: Log slow queries for monitoring:
   ```php
   // In AppServiceProvider
   DB::listen(function ($query) {
       if ($query->time > 1000) { // Log queries taking more than 1 second
           Log::warning('Slow query detected', [
               'sql' => $query->sql,
               'bindings' => $query->bindings,
               'time' => $query->time,
           ]);
       }
   });
   ```


## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, surface counterexamples that demonstrate the bugs on unfixed code, then verify the fixes work correctly and preserve existing behavior. Given the comprehensive nature of this stabilization pass (10 bug categories, 60 bug scenarios), testing will focus on:

1. **Exploratory Bug Condition Checking**: Confirm each bug exists on unfixed code
2. **Fix Checking**: Verify fixes resolve the bugs
3. **Preservation Checking**: Ensure existing functionality remains unchanged
4. **Integration Testing**: Verify fixes work together without conflicts

### Exploratory Bug Condition Checking

**Goal**: Surface counterexamples that demonstrate the bugs BEFORE implementing fixes. Confirm or refute the root cause analysis. If we refute, we will need to re-hypothesize.

**Test Plan**: Write tests that simulate each bug condition and run them on the UNFIXED code to observe failures and understand the root causes.

**Test Cases**:

1. **Scratch Card Crash Test**: Simulate valid scratch card result check (will fail on unfixed code)
   - Expected: System crashes with `StudentTransactionalEmailRequested::__construct()` error
   - Confirms: Event dispatch pattern is incorrect

2. **Permission Bypass Test**: Simulate teacher editing approved result (will fail on unfixed code)
   - Expected: Edit operation allowed instead of blocked
   - Confirms: Authorization policies missing

3. **Workspace Navigation Test**: Simulate result entry in Student 360 (will fail on unfixed code)
   - Expected: Navigation breaks during operation
   - Confirms: JavaScript navigation handling incomplete

4. **Inline Editing Test**: Simulate inline edit in Student 360 Result view (will fail on unfixed code)
   - Expected: Inline editing does not activate
   - Confirms: JavaScript inline editing not implemented

5. **Communication Visibility Test**: Simulate teacher accessing communication logs (will fail on unfixed code)
   - Expected: Communication logs visible to teacher
   - Confirms: Authorization enforcement missing

6. **School SMTP Test**: Simulate email sending with school SMTP configured (will fail on unfixed code)
   - Expected: School SMTP not used or fails
   - Confirms: Dynamic SMTP switching not implemented

7. **Sidebar Duplication Test**: Simulate sidebar rendering for any role (will fail on unfixed code)
   - Expected: Duplicated menu items visible
   - Confirms: Menu filtering logic incomplete

8. **Language Persistence Test**: Simulate language preference change (will fail on unfixed code)
   - Expected: Preference not saved, reverts on reload
   - Confirms: Persistence logic incomplete

9. **Mobile Layout Test**: Simulate mobile device access (will fail on unfixed code)
   - Expected: Overlapping text, broken tables, hidden buttons
   - Confirms: Responsive CSS incomplete

10. **N+1 Query Test**: Simulate loading result list with relationships (will fail on unfixed code)
    - Expected: N+1 queries detected in query log
    - Confirms: Eager loading missing

**Expected Counterexamples**:
- Scratch card result check crashes before displaying result
- Teachers can edit locked results without authorization errors
- Navigation breaks when performing result operations
- Inline editing does not work in Student 360
- Communication logs visible to unauthorized roles
- School SMTP settings not applied to emails
- Sidebar shows duplicate menu items
- Language preference not persisted across requests
- Mobile layouts broken with overlapping elements
- N+1 queries causing performance degradation

### Fix Checking

**Goal**: Verify that for all inputs where the bug conditions hold, the fixed system produces the expected behavior.

**Pseudocode:**
```
FOR ALL bugCategory IN [1..10] DO
  FOR ALL bugScenario IN bugCategory DO
    input := generateBugConditionInput(bugScenario)
    result := fixedSystem(input)
    ASSERT expectedBehavior(result, bugScenario)
  END FOR
END FOR
```

**Testing Approach**: For each of the 10 bug categories, write tests that verify the expected correct behavior:

1. **Scratch Card Fix Tests**:
   - Test valid scratch card result check completes without crash
   - Test result loads and displays correctly
   - Test audit log records successful access

2. **Permission Enforcement Tests**:
   - Test teacher cannot edit approved/published/locked results
   - Test teacher cannot access unassigned classes
   - Test Result Officer with disabled permissions denied operations
   - Test School Admin operations properly scoped

3. **Workspace Completion Tests**:
   - Test navigation works in all three workspaces
   - Test all inline actions present and functional
   - Test all columns complete and displayed
   - Test workflow state transitions enforced
   - Test auto-calculation works correctly
   - Test audit logging complete

4. **Student 360 Operation Tests**:
   - Test inline editing works correctly
   - Test save draft and autosave functional
   - Test quick subject switching works
   - Test validation indicators present
   - Test responsive grids work on mobile/tablet

5. **Communication Visibility Tests**:
   - Test communication logs hidden from teachers
   - Test communication logs hidden from result officers
   - Test direct route access denied for unauthorized roles
   - Test menu items/widgets hidden for unauthorized roles

6. **Mail System Tests**:
   - Test school SMTP works for all email types
   - Test fallback SMTP activates when school SMTP fails
   - Test queue compatibility works correctly
   - Test all email types delivered successfully
   - Test dynamic SMTP switching works
   - Test encrypted credentials stored and retrieved

7. **Sidebar Cleanup Tests**:
   - Test no duplicated menu items
   - Test only authorized modules visible per role
   - Test no permission leakage
   - Test no orphan links
   - Test hidden routes protected
   - Test role-specific module visibility

8. **Language System Tests**:
   - Test translation persistence works
   - Test backend translations complete
   - Test navbar/validation/dashboard translations work
   - Test session persistence works
   - Test RTL support complete with proper spacing/alignment

9. **Responsive Design Tests**:
   - Test no overlapping text on mobile/tablet
   - Test tables scroll correctly
   - Test buttons visible and accessible
   - Test z-index issues resolved
   - Test sidebar collapse works
   - Test dropdown overflow fixed
   - Test dark mode contrast sufficient
   - Test sticky headers work
   - Test layouts usable on low-end Android

10. **Performance Tests**:
    - Test N+1 queries eliminated
    - Test eager loading implemented
    - Test indexed queries optimized
    - Test pagination implemented
    - Test lazy loading works
    - Test query scopes available
    - Test caching implemented
    - Test performance acceptable for large schools


### Preservation Checking

**Goal**: Verify that for all inputs where the bug conditions do NOT hold, the fixed system produces the same result as the original system.

**Pseudocode:**
```
FOR ALL input WHERE NOT anyBugCondition(input) DO
  ASSERT originalSystem(input) = fixedSystem(input)
END FOR
```

**Testing Approach**: Property-based testing is recommended for preservation checking because:
- It generates many test cases automatically across the input domain
- It catches edge cases that manual unit tests might miss
- It provides strong guarantees that behavior is unchanged for all non-buggy inputs

**Test Plan**: Observe behavior on UNFIXED code first for non-bug inputs, then write property-based tests capturing that behavior.

**Test Cases**:

1. **Valid Scratch Card Preservation**: Observe that valid scratch card checking with correct credentials works on unfixed code, then write test to verify this continues after fix
   - Test invalid credentials still show appropriate errors
   - Test complete result display with all subjects/scores/grades
   - Test scratch card reuse prevention

2. **Authorized Result Operations Preservation**: Observe that authorized result operations work on unfixed code, then write test to verify this continues after fix
   - Test authorized users can perform result entry within scope
   - Test valid workflow transitions process correctly
   - Test result calculations accurate

3. **Super Admin Communication Access Preservation**: Observe that Super Admin can access communication logs on unfixed code, then write test to verify this continues after fix
   - Test Super Admin sees all communication logs across schools
   - Test authorized School Admin sees school-scoped logs
   - Test communication emails deliver successfully

4. **RBAC Preservation**: Observe that role-based access control works on unfixed code, then write test to verify this continues after fix
   - Test users access features within authorized scope
   - Test users denied access outside authorized scope
   - Test school_id isolation enforced

5. **Platform SMTP Fallback Preservation**: Observe that platform SMTP fallback works on unfixed code, then write test to verify this continues after fix
   - Test platform SMTP used when school SMTP not configured
   - Test email branding includes school logo/colors
   - Test email delivery failures logged

6. **Language Preference Preservation**: Observe that language preferences work on unfixed code, then write test to verify this continues after fix
   - Test English translations display correctly
   - Test French translations display correctly
   - Test Arabic translations display with RTL layout

7. **Desktop Layout Preservation**: Observe that desktop layouts work on unfixed code, then write test to verify this continues after fix
   - Test desktop layouts display correctly
   - Test supported mobile/tablet devices functional

8. **Small/Medium School Performance Preservation**: Observe that small/medium schools perform well on unfixed code, then write test to verify this continues after fix
   - Test existing indexes used efficiently
   - Test existing caching works appropriately

9. **Audit System Preservation**: Observe that audit logging works on unfixed code, then write test to verify this continues after fix
   - Test audit-worthy actions logged with metadata
   - Test audit logs queryable and accurate
   - Test audit logs display formatted correctly

10. **Student 360 Non-Result Preservation**: Observe that Student 360 non-result sections work on unfixed code, then write test to verify this continues after fix
    - Test Student 360 profile section functional
    - Test Student 360 enrollment section functional
    - Test Result Management authorized operations functional
    - Test report card generation accurate
    - Test result publication visibility controls enforced

### Unit Tests

**Scratch Card Tests**:
- Test event dispatch pattern correction
- Test email dispatch error handling
- Test audit logging before email dispatch

**Permission Tests**:
- Test ResultPolicy authorization methods
- Test EnsureResultEditable middleware
- Test teacher scope validation
- Test Result Officer permission flags

**Workspace Tests**:
- Test ResultWorkflowService state transitions
- Test ResultValidationService auto-calculation
- Test inline action authorization checks
- Test audit logging integration

**Student 360 Tests**:
- Test inline editing activation
- Test save draft endpoint
- Test autosave debouncing
- Test subject switching endpoint
- Test validation indicator rendering

**Communication Tests**:
- Test CommunicationLogPolicy authorization
- Test EnsureCommunicationAccess middleware
- Test sidebar menu filtering
- Test dashboard widget filtering

**Mail System Tests**:
- Test SchoolMailService SMTP resolution
- Test dynamic SMTP switching
- Test failover behavior
- Test credential encryption/decryption
- Test queue serialization

**Sidebar Tests**:
- Test Sidebar component filtering
- Test duplicate removal
- Test orphan link removal
- Test role-specific module visibility

**Language Tests**:
- Test SetLocale middleware persistence
- Test translation key resolution
- Test session persistence
- Test RTL CSS application

**Responsive Tests**:
- Test responsive breakpoints
- Test table scrolling
- Test sidebar collapse
- Test dropdown positioning
- Test dark mode contrast

**Performance Tests**:
- Test eager loading queries
- Test database indexes
- Test pagination
- Test lazy loading
- Test query scopes
- Test caching

### Property-Based Tests

**Authorization Properties**:
- Generate random user/role combinations and verify authorization rules enforced
- Generate random result states and verify edit permissions correct
- Generate random resource access attempts and verify scope validation

**Workflow Properties**:
- Generate random workflow state transitions and verify validation enforced
- Generate random score inputs and verify auto-calculation correct
- Generate random result operations and verify audit logging complete

**Mail Properties**:
- Generate random school SMTP configurations and verify correct SMTP used
- Generate random email types and verify delivery successful
- Generate random SMTP failures and verify failover activates

**UI Properties**:
- Generate random role/permission combinations and verify sidebar filtering correct
- Generate random language preferences and verify persistence works
- Generate random device types and verify responsive layouts functional

**Performance Properties**:
- Generate random query patterns and verify N+1 queries eliminated
- Generate random data sizes and verify pagination works
- Generate random access patterns and verify caching effective

### Integration Tests

**End-to-End Scratch Card Flow**:
- Test complete scratch card result check flow from form submission to result display
- Test email notification delivery
- Test audit log recording

**End-to-End Result Management Flow**:
- Test complete result entry flow across all three workspaces
- Test workflow state transitions from draft to published
- Test permission enforcement at each step

**End-to-End Communication Flow**:
- Test communication log creation and visibility
- Test role-based access enforcement
- Test email delivery with school SMTP

**End-to-End Language Flow**:
- Test language preference change and persistence
- Test UI translation updates
- Test RTL layout activation

**End-to-End Mobile Flow**:
- Test complete mobile user journey
- Test responsive layouts across breakpoints
- Test touch interactions

**End-to-End Performance Flow**:
- Test large school data loading
- Test query optimization effectiveness
- Test caching behavior

### Manual Testing Checklist

**Cross-Browser Testing**:
- [ ] Chrome (desktop and mobile)
- [ ] Firefox (desktop and mobile)
- [ ] Safari (desktop and mobile)
- [ ] Edge (desktop)

**Device Testing**:
- [ ] Desktop (1920x1080, 1366x768)
- [ ] Tablet (iPad, Android tablet)
- [ ] Mobile (iPhone, Android high-end, Android low-end)

**Role Testing**:
- [ ] Super Admin complete workflow
- [ ] School Admin complete workflow
- [ ] Result Officer complete workflow
- [ ] Teacher complete workflow

**Language Testing**:
- [ ] English UI complete
- [ ] French UI complete
- [ ] Arabic UI complete with RTL

**Performance Testing**:
- [ ] Small school (< 100 students)
- [ ] Medium school (100-500 students)
- [ ] Large school (500-1000 students)
- [ ] Very large school (1000+ students)

**Accessibility Testing**:
- [ ] Keyboard navigation
- [ ] Screen reader compatibility
- [ ] Color contrast (WCAG AA)
- [ ] Focus indicators

