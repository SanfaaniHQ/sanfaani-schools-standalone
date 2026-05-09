# Result Authorization Fix Design

## Overview

This design addresses the authorization bug in `ManualResultController` and `TeacherResultEntryController` where legitimate users receive "403 | You cannot access this result" errors when editing or deleting results. The current implementation only checks tenant isolation (school_id matching) but does not implement proper role-based access control.

The fix will implement a comprehensive authorization system that:
- Grants School Admins full access to all results in their school
- Grants Result Officers access based on feature permissions (manual_entry)
- Grants Teachers access to results for their assigned classes/subjects with status-based restrictions
- Grants Super Admins in support mode access to results in the support school
- Maintains strict tenant isolation to prevent cross-school access

## Glossary

- **Bug_Condition (C)**: The condition that triggers the bug - when authorized users are incorrectly denied access to results they should be able to edit/delete
- **Property (P)**: The desired behavior - authorized users can successfully edit/delete results based on their role, permissions, and assignments
- **Preservation**: Tenant isolation and cross-school access prevention that must remain unchanged by the fix
- **authorizeResult()**: The method in `ManualResultController` (line 113) that currently only checks school_id matching
- **authorizeSubmission()**: The method in `TeacherResultEntryController` (line 177) that checks school_id, teacher ownership, and status
- **roleContext**: The effective role name for the current user ('school_admin', 'result_officer', 'teacher', 'super_admin')
- **CurrentSchoolService**: Service that resolves the active school context from session or user attributes
- **SchoolRoleFeatureService**: Service that checks if a feature is enabled for a role in a school
- **TeacherSubjectAssignment**: Model representing teacher assignments to subjects (optionally scoped to class/session/term)
- **TeacherClassAssignment**: Model representing teacher assignments to classes (optionally scoped to session/term)

## Bug Details

### Bug Condition

The bug manifests when an authorized user (School Admin, Result Officer with permissions, Teacher with assignments, or Super Admin in support mode) attempts to edit or delete a result belonging to their school. The `authorizeResult()` method in `ManualResultController` only checks if `$studentResult->school_id !== $school->id`, which is insufficient for proper authorization.

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input of type { user: User, result: StudentResult, operation: 'edit'|'delete' }
  OUTPUT: boolean
  
  school := CurrentSchoolService.get(input.user)
  roleContext := CurrentSchoolService.roleContext(input.user)
  
  RETURN input.result.school_id == school.id
         AND (
           roleContext == 'school_admin'
           OR (roleContext == 'result_officer' AND hasFeature(school, 'result_officer', 'results.manual_entry'))
           OR (roleContext == 'teacher' AND isAssignedTo(input.user, input.result) AND canModifyStatus(input.result.status, input.operation))
           OR (roleContext == 'super_admin' AND inSupportMode(input.user))
         )
         AND currentlyReturns403Error()
END FUNCTION
```

### Examples

- **School Admin Edit**: School Admin with school_id=5 attempts to edit result with school_id=5 → Currently returns 403, should allow
- **Result Officer Edit**: Result Officer with manual_entry enabled attempts to edit result in their school → Currently returns 403, should allow
- **Teacher Edit Draft**: Teacher assigned to Math for Class 5 attempts to edit a draft Math result for Class 5 → Currently returns 403, should allow
- **Teacher Edit Submitted**: Teacher assigned to Math for Class 5 attempts to edit a submitted Math result for Class 5 → Should return 403 with status-specific message
- **Super Admin Support**: Super Admin in support mode for school_id=10 attempts to edit result with school_id=10 → Currently returns 403, should allow

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Cross-school access must continue to be blocked with "403 | You cannot access this result"
- Teachers must continue to be blocked from accessing other teachers' result submissions
- Published results must continue to require unpublishing before deletion
- Tenant isolation checks must continue to use strict integer comparison: `(int) $studentResult->school_id === (int) $school->id`
- Active school context resolution must continue to check session('support_school_id'), session('active_school_id'), and auth()->user()->school_id in that order
- Audit logging must continue to log result edit and delete operations

**Scope:**
All inputs that involve cross-school access attempts should be completely unaffected by this fix. This includes:
- Attempts to access results from a different school than the user's active school
- Super Admin attempts to access school results when not in support mode
- Teacher attempts to access results from schools they are not assigned to

## Hypothesized Root Cause

Based on the bug description and code analysis, the root cause is:

1. **Incomplete Authorization Logic**: The `authorizeResult()` method in `ManualResultController` (line 113-118) only checks tenant isolation:
   ```php
   private function authorizeResult(StudentResult $studentResult, School $school): void
   {
       if ($studentResult->school_id !== $school->id) {
           abort(403, 'You cannot access this result.');
       }
   }
   ```
   This method does not check:
   - User's role context (school_admin, result_officer, teacher, super_admin)
   - Result Officer feature permissions (results.manual_entry)
   - Teacher assignments to the result's class and subject
   - Teacher restrictions based on result status

2. **Missing Role-Based Authorization**: The controller does not use `CurrentSchoolService::roleContext()` to determine the user's effective role and apply role-specific authorization rules.

3. **Missing Feature Permission Checks**: The controller does not use `SchoolRoleFeatureService::enabled()` to check if Result Officers have the 'results.manual_entry' feature enabled.

4. **Missing Teacher Assignment Validation**: The controller does not check if Teachers are assigned to the result's class and subject through `TeacherSubjectAssignment` or `TeacherClassAssignment`.

5. **Missing Status-Based Restrictions**: The controller does not enforce that Teachers can only edit/delete results with status 'draft' or 'returned'.

## Correctness Properties

Property 1: Bug Condition - Authorized Users Can Edit/Delete Results

_For any_ user and result where the bug condition holds (user is authorized based on role, permissions, and assignments), the fixed authorization logic SHALL allow the edit or delete operation to proceed without returning a 403 error.

**Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.7, 2.8, 2.15, 2.16, 2.21, 2.22**

Property 2: Preservation - Cross-School Access Prevention

_For any_ user and result where the bug condition does NOT hold (result belongs to a different school than the user's active school), the fixed authorization logic SHALL produce the same result as the original logic, returning "403 | You cannot access this result" and preserving strict tenant isolation.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.10, 3.11**

## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct:

**File**: `app/Http/Controllers/School/ManualResultController.php`

**Function**: `authorizeResult()` (line 113-118)

**Specific Changes**:
1. **Add Role Context Resolution**: Inject `CurrentSchoolService` and call `roleContext()` to get the user's effective role
   - Use `app(CurrentSchoolService::class)->roleContext(auth()->user())`
   - Store in variable `$roleContext`

2. **Implement School Admin Authorization**: Check if `$roleContext === 'school_admin'`
   - School Admins have full access to all results in their school
   - Return early if true (allow access)

3. **Implement Super Admin Support Mode Authorization**: Check if `$roleContext === 'super_admin'` and `app(CurrentSchoolService::class)->inSupportMode()`
   - Super Admins in support mode have full access to results in the support school
   - Return early if true (allow access)

4. **Implement Result Officer Authorization**: Check if `$roleContext === 'result_officer'`
   - Use `app(SchoolRoleFeatureService::class)->enabled($school->id, 'result_officer', 'results.manual_entry')`
   - If enabled, allow access
   - If disabled, abort with "403 | This feature is not enabled for your role"

5. **Implement Teacher Authorization**: Check if `$roleContext === 'teacher'`
   - Call new helper method `isTeacherAssignedToResult()` to check assignments
   - Check result status: only allow edit/delete for 'draft' or 'returned' status
   - If not assigned, abort with "403 | You are not assigned to this class and subject"
   - If wrong status, abort with "403 | Submitted, approved, published, or voided results cannot be edited/deleted by the teacher"

6. **Add Teacher Assignment Helper Method**: Create `isTeacherAssignedToResult(User $user, StudentResult $result, School $school): bool`
   - Check `TeacherSubjectAssignment` for matching subject_id with optional class/session/term scoping
   - Check `TeacherClassAssignment` for matching school_class_id with optional session/term scoping
   - Return true if either assignment exists with status='active' and deleted_at=null

7. **Preserve Tenant Isolation**: Keep the existing school_id check as the first validation
   - Maintain strict integer comparison: `(int) $studentResult->school_id !== (int) $school->id`
   - This ensures cross-school access is always blocked first

**File**: `app/Http/Controllers/School/TeacherResultEntryController.php`

**Function**: `authorizeSubmission()` (line 177-193)

**Specific Changes**:
1. **Add Role-Based Authorization**: The existing method already checks teacher ownership and status
   - Add School Admin and Super Admin support mode checks before teacher-specific logic
   - Add Result Officer feature permission check
   - Keep existing teacher ownership and status checks

2. **Preserve Existing Logic**: The teacher authorization logic is already correct
   - Keep the check: `$roleContext === 'teacher' && (int) $submission->teacher_user_id !== (int) auth()->id()`
   - Keep the status check: `! in_array($submission->status, ['draft', 'returned'], true)`

### New Helper Service (Optional Enhancement)

**File**: `app/Services/ResultAuthorizationService.php` (new file)

**Purpose**: Centralize authorization logic for reuse across controllers

**Methods**:
- `canEditResult(User $user, StudentResult $result, School $school): bool`
- `canDeleteResult(User $user, StudentResult $result, School $school): bool`
- `canEditSubmission(User $user, TeacherResultSubmission $submission, School $school): bool`
- `isTeacherAssignedToResult(User $user, StudentResult $result, School $school): bool`

## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, surface counterexamples that demonstrate the bug on unfixed code, then verify the fix works correctly and preserves existing behavior.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexamples that demonstrate the bug BEFORE implementing the fix. Confirm or refute the root cause analysis. If we refute, we will need to re-hypothesize.

**Test Plan**: Write tests that simulate authorized users attempting to edit/delete results in their school. Run these tests on the UNFIXED code to observe 403 failures and confirm the authorization logic is incomplete.

**Test Cases**:
1. **School Admin Edit Test**: Create a School Admin user, create a result in their school, attempt to edit via ManualResultController::edit() (will fail with 403 on unfixed code)
2. **Result Officer Edit Test**: Create a Result Officer with manual_entry enabled, create a result in their school, attempt to edit (will fail with 403 on unfixed code)
3. **Teacher Edit Draft Test**: Create a Teacher with subject assignment, create a draft result for that subject, attempt to edit (will fail with 403 on unfixed code)
4. **Super Admin Support Edit Test**: Create a Super Admin in support mode, create a result in support school, attempt to edit (will fail with 403 on unfixed code)

**Expected Counterexamples**:
- All authorized users receive "403 | You cannot access this result" errors
- Possible causes: missing role checks, missing feature permission checks, missing teacher assignment checks

### Fix Checking

**Goal**: Verify that for all inputs where the bug condition holds, the fixed function produces the expected behavior.

**Pseudocode:**
```
FOR ALL (user, result, operation) WHERE isBugCondition(user, result, operation) DO
  response := authorizeResult_fixed(result, school)
  ASSERT response does not abort with 403
  ASSERT operation is allowed to proceed
END FOR
```

**Test Cases**:
1. **School Admin Authorization**: Verify School Admins can edit/delete any result in their school
2. **Result Officer Authorization**: Verify Result Officers with manual_entry can edit/delete results
3. **Result Officer Feature Disabled**: Verify Result Officers without manual_entry receive feature-specific 403
4. **Teacher Draft Authorization**: Verify Teachers can edit/delete draft results for assigned classes/subjects
5. **Teacher Returned Authorization**: Verify Teachers can edit/delete returned results for assigned classes/subjects
6. **Teacher Status Restriction**: Verify Teachers cannot edit/delete submitted/approved/published/voided results
7. **Teacher Assignment Restriction**: Verify Teachers cannot edit/delete results for unassigned classes/subjects
8. **Super Admin Support Authorization**: Verify Super Admins in support mode can edit/delete results in support school

### Preservation Checking

**Goal**: Verify that for all inputs where the bug condition does NOT hold, the fixed function produces the same result as the original function.

**Pseudocode:**
```
FOR ALL (user, result, operation) WHERE NOT isBugCondition(user, result, operation) DO
  ASSERT authorizeResult_original(result, school) = authorizeResult_fixed(result, school)
END FOR
```

**Testing Approach**: Property-based testing is recommended for preservation checking because:
- It generates many test cases automatically across the input domain
- It catches edge cases that manual unit tests might miss
- It provides strong guarantees that behavior is unchanged for all non-buggy inputs

**Test Plan**: Observe behavior on UNFIXED code first for cross-school access attempts, then write property-based tests capturing that behavior.

**Test Cases**:
1. **Cross-School Access Prevention**: Verify users cannot access results from other schools (observe 403 on unfixed code, verify same on fixed code)
2. **Super Admin Non-Support Prevention**: Verify Super Admins not in support mode cannot access school results (observe 403 on unfixed code, verify same on fixed code)
3. **Teacher Cross-School Prevention**: Verify Teachers cannot access results from other schools (observe 403 on unfixed code, verify same on fixed code)
4. **Published Result Deletion Prevention**: Verify published results cannot be deleted without unpublishing (observe error on unfixed code, verify same on fixed code)
5. **Tenant Isolation Preservation**: Verify strict school_id matching continues to work with integer comparison (observe behavior on unfixed code, verify same on fixed code)

### Unit Tests

- Test `authorizeResult()` with each role context (school_admin, result_officer, teacher, super_admin)
- Test Result Officer feature permission checks (enabled vs disabled)
- Test Teacher assignment validation (assigned vs unassigned)
- Test Teacher status restrictions (draft/returned vs submitted/approved/published/voided)
- Test cross-school access prevention for each role
- Test Super Admin support mode vs non-support mode

### Property-Based Tests

- Generate random user/result combinations and verify authorized users can access same-school results
- Generate random cross-school access attempts and verify all are blocked with 403
- Generate random Teacher assignments and verify only assigned results are accessible
- Generate random result statuses and verify Teachers can only modify draft/returned results

### Integration Tests

- Test full edit flow: School Admin edits result → success
- Test full delete flow: Result Officer deletes result → success
- Test Teacher edit flow: Teacher edits draft result for assigned subject → success
- Test Teacher restriction flow: Teacher attempts to edit submitted result → 403 with status message
- Test cross-school flow: User attempts to edit result from different school → 403 with tenant message
- Test audit logging: Verify edit/delete operations are logged correctly after authorization passes
