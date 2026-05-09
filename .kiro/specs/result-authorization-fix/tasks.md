# Implementation Plan

- [-] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - Authorized Users Receive 403 Errors
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bug exists
  - **Scoped PBT Approach**: For deterministic bugs, scope the property to the concrete failing case(s) to ensure reproducibility
  - Test implementation details from Bug Condition in design:
    - School Admin with school_id=5 attempts to edit result with school_id=5
    - Result Officer with manual_entry enabled attempts to edit result in their school
    - Teacher assigned to Math for Class 5 attempts to edit a draft Math result for Class 5
    - Super Admin in support mode for school_id=10 attempts to edit result with school_id=10
  - The test assertions should match the Expected Behavior Properties from design:
    - For any user and result where isBugCondition(user, result, operation) is true, the authorization logic should allow the operation without returning 403
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bug exists)
  - Document counterexamples found to understand root cause:
    - Which role contexts fail?
    - What error messages are returned?
    - Does the authorizeResult() method lack role-based checks?
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 1.9_

- [~] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Cross-School Access Prevention
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-buggy inputs:
    - User attempts to access result from different school
    - Super Admin not in support mode attempts to access school result
    - Teacher attempts to access result from another school
    - Teacher attempts to access another teacher's submission
  - Write property-based tests capturing observed behavior patterns from Preservation Requirements:
    - For any user and result where result.school_id != user.active_school_id, the system returns "403 | You cannot access this result"
    - For any Super Admin not in support mode, the system returns "403 | Your account is not assigned to a school"
    - For any Teacher accessing another teacher's submission, the system returns "403 | You cannot access another teacher result submission"
    - Tenant isolation uses strict integer comparison: (int) $studentResult->school_id === (int) $school->id
  - Property-based testing generates many test cases for stronger guarantees
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.10, 3.11_

- [ ] 3. Fix for result authorization bug

  - [~] 3.1 Update ManualResultController::authorizeResult() method
    - Add role context resolution using CurrentSchoolService::roleContext()
    - Implement School Admin authorization (full access to all results in their school)
    - Implement Super Admin support mode authorization (full access to results in support school)
    - Implement Result Officer authorization with feature permission check (results.manual_entry)
    - Implement Teacher authorization with assignment validation and status restrictions
    - Preserve tenant isolation check as first validation (strict integer comparison)
    - Add specific error messages for each authorization failure case
    - _Bug_Condition: isBugCondition(input) where input.result.school_id == school.id AND (roleContext == 'school_admin' OR (roleContext == 'result_officer' AND hasFeature) OR (roleContext == 'teacher' AND isAssignedTo AND canModifyStatus) OR (roleContext == 'super_admin' AND inSupportMode)) AND currentlyReturns403Error()_
    - _Expected_Behavior: For all inputs where isBugCondition is true, authorizeResult() allows the operation without aborting with 403_
    - _Preservation: Cross-school access continues to be blocked with "403 | You cannot access this result", tenant isolation uses strict integer comparison_
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 2.9, 2.10, 2.11, 2.12, 2.13, 2.14, 2.15, 2.16, 2.17, 2.18, 2.19, 2.20, 2.21, 2.22, 3.1, 3.2, 3.10_

  - [~] 3.2 Add isTeacherAssignedToResult() helper method to ManualResultController
    - Check TeacherSubjectAssignment for matching subject_id with optional class/session/term scoping
    - Check TeacherClassAssignment for matching school_class_id with optional session/term scoping
    - Return true if either assignment exists with status='active' and deleted_at=null
    - Handle cases where assignments are scoped to specific sessions/terms
    - _Requirements: 2.7, 2.8, 2.13, 2.14, 2.15, 2.16_

  - [~] 3.3 Update TeacherResultEntryController::authorizeSubmission() method
    - Add School Admin authorization check before teacher-specific logic
    - Add Super Admin support mode authorization check
    - Add Result Officer feature permission check
    - Preserve existing teacher ownership and status checks
    - Maintain existing error messages for teacher-specific restrictions
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.21, 2.22, 3.7_

  - [~] 3.4 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Authorized Users Can Edit/Delete Results
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Run bug condition exploration test from step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - Verify all role contexts now pass:
      - School Admin can edit/delete results in their school
      - Result Officer with manual_entry can edit/delete results
      - Teacher can edit/delete draft/returned results for assigned classes/subjects
      - Super Admin in support mode can edit/delete results in support school
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.7, 2.8, 2.15, 2.16, 2.21, 2.22_

  - [~] 3.5 Verify preservation tests still pass
    - **Property 2: Preservation** - Cross-School Access Prevention
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Confirm all preservation behaviors still work:
      - Cross-school access is blocked with "403 | You cannot access this result"
      - Super Admin non-support mode is blocked
      - Teacher cross-school access is blocked
      - Teacher cross-submission access is blocked
      - Tenant isolation uses strict integer comparison
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.10, 3.11_

- [ ] 4. Add unit tests for authorization logic

  - [~] 4.1 Test School Admin authorization
    - Test School Admin can edit any result in their school
    - Test School Admin can delete any result in their school
    - Test School Admin cannot access results from other schools
    - _Requirements: 2.1, 2.2, 3.2_

  - [~] 4.2 Test Result Officer authorization
    - Test Result Officer with manual_entry enabled can edit/delete results
    - Test Result Officer with manual_entry disabled receives feature-specific 403
    - Test Result Officer cannot access results from other schools
    - _Requirements: 2.3, 2.4, 2.5, 2.6, 3.3_

  - [~] 4.3 Test Teacher authorization
    - Test Teacher can edit/delete draft results for assigned classes/subjects
    - Test Teacher can edit/delete returned results for assigned classes/subjects
    - Test Teacher cannot edit/delete submitted results (status restriction)
    - Test Teacher cannot edit/delete approved results (status restriction)
    - Test Teacher cannot edit/delete published results (status restriction)
    - Test Teacher cannot edit/delete voided results (status restriction)
    - Test Teacher cannot edit/delete results for unassigned classes
    - Test Teacher cannot edit/delete results for unassigned subjects
    - Test Teacher cannot access results from other schools
    - _Requirements: 2.7, 2.8, 2.9, 2.10, 2.11, 2.12, 2.13, 2.14, 2.15, 2.16, 2.17, 2.18, 2.19, 2.20, 3.4_

  - [~] 4.4 Test Super Admin authorization
    - Test Super Admin in support mode can edit/delete results in support school
    - Test Super Admin not in support mode receives appropriate 403
    - Test Super Admin in support mode cannot access results from other schools
    - _Requirements: 2.21, 2.22, 3.5, 3.6_

  - [~] 4.5 Test Teacher assignment validation helper
    - Test isTeacherAssignedToResult() returns true for subject assignments
    - Test isTeacherAssignedToResult() returns true for class assignments
    - Test isTeacherAssignedToResult() returns false for unassigned classes/subjects
    - Test isTeacherAssignedToResult() handles session/term scoping correctly
    - Test isTeacherAssignedToResult() checks status='active' and deleted_at=null
    - _Requirements: 2.7, 2.8, 2.13, 2.14, 2.15, 2.16_

- [ ] 5. Add integration tests for full workflows

  - [~] 5.1 Test School Admin edit workflow
    - Create School Admin user with school_id=5
    - Create result with school_id=5
    - Attempt to edit result via ManualResultController::edit()
    - Verify edit succeeds without 403 error
    - Verify audit log records the edit operation
    - _Requirements: 2.1, 3.12_

  - [~] 5.2 Test Result Officer delete workflow
    - Create Result Officer with manual_entry enabled
    - Create result in their school
    - Attempt to delete result via ManualResultController::destroy()
    - Verify delete succeeds without 403 error
    - Verify audit log records the delete operation
    - _Requirements: 2.4, 3.12_

  - [~] 5.3 Test Teacher edit draft workflow
    - Create Teacher with subject assignment to Math for Class 5
    - Create draft Math result for Class 5
    - Attempt to edit result via ManualResultController::edit()
    - Verify edit succeeds without 403 error
    - _Requirements: 2.7_

  - [~] 5.4 Test Teacher status restriction workflow
    - Create Teacher with subject assignment
    - Create submitted result for assigned subject
    - Attempt to edit result via ManualResultController::edit()
    - Verify receives "403 | Submitted, approved, published, or voided results cannot be edited by the teacher"
    - _Requirements: 2.9_

  - [~] 5.5 Test cross-school access prevention workflow
    - Create user with school_id=5
    - Create result with school_id=10
    - Attempt to edit result via ManualResultController::edit()
    - Verify receives "403 | You cannot access this result"
    - Verify tenant isolation is enforced
    - _Requirements: 3.1_

  - [~] 5.6 Test published result deletion prevention workflow
    - Create School Admin user
    - Create published result in their school
    - Attempt to delete result via ManualResultController::destroy()
    - Verify receives "Published results must be unpublished before deletion"
    - _Requirements: 3.8_

- [ ] 6. Update error messages for better user experience

  - [~] 6.1 Add role-specific error messages
    - Result Officer without feature: "This feature is not enabled for your role"
    - Teacher without assignment: "You are not assigned to this class and subject"
    - Teacher with wrong status: "Submitted, approved, published, or voided results cannot be edited/deleted by the teacher"
    - Cross-school access: "You cannot access this result" (preserve existing)
    - _Requirements: 2.5, 2.6, 2.9, 2.10, 2.11, 2.12, 2.13, 2.14, 2.17, 2.18, 2.19, 2.20, 3.1_

  - [~] 6.2 Ensure error messages are consistent across controllers
    - Verify ManualResultController uses consistent messages
    - Verify TeacherResultEntryController uses consistent messages
    - Preserve existing messages for unchanged behaviors
    - _Requirements: 3.7, 3.8_

- [ ] 7. Manual testing and verification

  - [~] 7.1 Test as School Admin
    - Log in as School Admin
    - Navigate to results list
    - Attempt to edit a result
    - Verify edit form loads without 403 error
    - Attempt to delete a result
    - Verify delete succeeds without 403 error
    - _Requirements: 2.1, 2.2_

  - [~] 7.2 Test as Result Officer with feature enabled
    - Log in as Result Officer
    - Verify manual_entry feature is enabled in role settings
    - Navigate to results list
    - Attempt to edit a result
    - Verify edit form loads without 403 error
    - _Requirements: 2.3, 2.4_

  - [~] 7.3 Test as Result Officer with feature disabled
    - Log in as Result Officer
    - Verify manual_entry feature is disabled in role settings
    - Navigate to results list
    - Attempt to edit a result
    - Verify receives "This feature is not enabled for your role" error
    - _Requirements: 2.5, 2.6_

  - [~] 7.4 Test as Teacher with assignment
    - Log in as Teacher
    - Verify teacher is assigned to a class and subject
    - Navigate to results list for assigned class
    - Attempt to edit a draft result
    - Verify edit form loads without 403 error
    - Attempt to edit a submitted result
    - Verify receives status-specific error message
    - _Requirements: 2.7, 2.8, 2.9_

  - [~] 7.5 Test as Teacher without assignment
    - Log in as Teacher
    - Navigate to results list for unassigned class
    - Attempt to edit a result
    - Verify receives "You are not assigned to this class and subject" error
    - _Requirements: 2.13, 2.14_

  - [~] 7.6 Test as Super Admin in support mode
    - Log in as Super Admin
    - Enter support mode for a school
    - Navigate to results list
    - Attempt to edit a result
    - Verify edit form loads without 403 error
    - _Requirements: 2.21, 2.22_

  - [~] 7.7 Test cross-school access prevention
    - Log in as any user
    - Attempt to access result from different school (via URL manipulation)
    - Verify receives "You cannot access this result" error
    - Verify tenant isolation is enforced
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.6_

- [~] 8. Checkpoint - Ensure all tests pass
  - Run all unit tests and verify they pass
  - Run all integration tests and verify they pass
  - Run bug condition exploration test and verify it now passes
  - Run preservation property tests and verify they still pass
  - Review manual testing results and confirm all scenarios work as expected
  - Ask the user if questions arise or if any edge cases need clarification
