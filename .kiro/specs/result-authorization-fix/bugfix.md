# Bugfix Requirements Document

## Introduction

This document defines the requirements for fixing the result edit/delete authorization bug in the Laravel school management system. Currently, users receive "403 | You cannot access this result" errors when attempting to edit or delete results they should legitimately have access to. The authorization logic is too restrictive and does not properly implement role-based access control for School Admins, Result Officers, and Teachers.

The fix will implement proper authorization rules that respect role hierarchies, feature access controls, teacher assignments, and submission statuses while maintaining strict tenant isolation to prevent cross-school data access.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN a School Admin attempts to edit a result belonging to their active school THEN the system returns "403 | You cannot access this result"

1.2 WHEN a School Admin attempts to delete a result belonging to their active school THEN the system returns "403 | You cannot access this result"

1.3 WHEN a Result Officer with manual_entry feature enabled attempts to edit a same-school result THEN the system returns "403 | You cannot access this result"

1.4 WHEN a Result Officer with manual_entry feature enabled attempts to delete a same-school result THEN the system returns "403 | You cannot access this result"

1.5 WHEN a Teacher attempts to edit a draft result for their assigned class and subject THEN the system returns "403 | You cannot access this result"

1.6 WHEN a Teacher attempts to edit a returned result for their assigned class and subject THEN the system returns "403 | You cannot access this result"

1.7 WHEN a Teacher attempts to delete a draft result for their assigned class and subject THEN the system returns "403 | You cannot access this result"

1.8 WHEN a Super Admin in support mode attempts to edit a result for the support school THEN the system returns "403 | You cannot access this result"

1.9 WHEN a Super Admin in support mode attempts to delete a result for the support school THEN the system returns "403 | You cannot access this result"

### Expected Behavior (Correct)

2.1 WHEN a School Admin attempts to edit any result belonging to their active school THEN the system SHALL allow the edit operation

2.2 WHEN a School Admin attempts to delete any result belonging to their active school THEN the system SHALL allow the delete operation

2.3 WHEN a Result Officer with manual_entry feature enabled attempts to edit a same-school result THEN the system SHALL allow the edit operation

2.4 WHEN a Result Officer with manual_entry feature enabled attempts to delete a same-school result THEN the system SHALL allow the delete operation

2.5 WHEN a Result Officer with manual_entry feature disabled attempts to edit a same-school result THEN the system SHALL return "403 | This feature is not enabled for your role"

2.6 WHEN a Result Officer with manual_entry feature disabled attempts to delete a same-school result THEN the system SHALL return "403 | This feature is not enabled for your role"

2.7 WHEN a Teacher attempts to edit a draft result for their assigned class and subject THEN the system SHALL allow the edit operation

2.8 WHEN a Teacher attempts to edit a returned result for their assigned class and subject THEN the system SHALL allow the edit operation

2.9 WHEN a Teacher attempts to edit a submitted result for their assigned class and subject THEN the system SHALL return "403 | Submitted, approved, published, or voided results cannot be edited by the teacher"

2.10 WHEN a Teacher attempts to edit an approved result for their assigned class and subject THEN the system SHALL return "403 | Submitted, approved, published, or voided results cannot be edited by the teacher"

2.11 WHEN a Teacher attempts to edit a published result for their assigned class and subject THEN the system SHALL return "403 | Submitted, approved, published, or voided results cannot be edited by the teacher"

2.12 WHEN a Teacher attempts to edit a voided result for their assigned class and subject THEN the system SHALL return "403 | Submitted, approved, published, or voided results cannot be edited by the teacher"

2.13 WHEN a Teacher attempts to edit a result for an unassigned class THEN the system SHALL return "403 | You are not assigned to this class and subject"

2.14 WHEN a Teacher attempts to edit a result for an unassigned subject THEN the system SHALL return "403 | You are not assigned to this class and subject"

2.15 WHEN a Teacher attempts to delete a draft result for their assigned class and subject THEN the system SHALL allow the delete operation

2.16 WHEN a Teacher attempts to delete a returned result for their assigned class and subject THEN the system SHALL allow the delete operation

2.17 WHEN a Teacher attempts to delete a submitted result for their assigned class and subject THEN the system SHALL return "403 | Submitted, approved, published, or voided results cannot be deleted by the teacher"

2.18 WHEN a Teacher attempts to delete an approved result for their assigned class and subject THEN the system SHALL return "403 | Submitted, approved, published, or voided results cannot be deleted by the teacher"

2.19 WHEN a Teacher attempts to delete a published result for their assigned class and subject THEN the system SHALL return "403 | Submitted, approved, published, or voided results cannot be deleted by the teacher"

2.20 WHEN a Teacher attempts to delete a voided result for their assigned class and subject THEN the system SHALL return "403 | Submitted, approved, published, or voided results cannot be deleted by the teacher"

2.21 WHEN a Super Admin in support mode attempts to edit a result for the support school THEN the system SHALL allow the edit operation

2.22 WHEN a Super Admin in support mode attempts to delete a result for the support school THEN the system SHALL allow the delete operation

### Unchanged Behavior (Regression Prevention)

3.1 WHEN any user attempts to access a result from a different school THEN the system SHALL CONTINUE TO return "403 | You cannot access this result"

3.2 WHEN a School Admin attempts to access a result from another school THEN the system SHALL CONTINUE TO return "403 | You cannot access this result"

3.3 WHEN a Result Officer attempts to access a result from another school THEN the system SHALL CONTINUE TO return "403 | You cannot access this result"

3.4 WHEN a Teacher attempts to access a result from another school THEN the system SHALL CONTINUE TO return "403 | You cannot access this result"

3.5 WHEN a Super Admin not in support mode attempts to access a school result THEN the system SHALL CONTINUE TO return "403 | Your account is not assigned to a school"

3.6 WHEN a Super Admin in support mode attempts to access a result from a different school than the support school THEN the system SHALL CONTINUE TO return "403 | You cannot access this result"

3.7 WHEN a Teacher attempts to access another teacher's result submission THEN the system SHALL CONTINUE TO return "403 | You cannot access another teacher result submission"

3.8 WHEN a user attempts to delete a published result via ManualResultController THEN the system SHALL CONTINUE TO return "Published results must be unpublished before deletion"

3.9 WHEN the public result checker is accessed THEN the system SHALL CONTINUE TO function without any authorization changes

3.10 WHEN tenant isolation checks are performed THEN the system SHALL CONTINUE TO enforce strict school_id matching using (int) $studentResult->school_id === (int) $school->id

3.11 WHEN active school context is resolved THEN the system SHALL CONTINUE TO check session('support_school_id'), session('active_school_id'), and auth()->user()->school_id in that order

3.12 WHEN audit logging is enabled THEN the system SHALL CONTINUE TO log result edit and delete operations
