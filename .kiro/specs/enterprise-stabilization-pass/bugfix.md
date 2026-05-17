# Bugfix Requirements Document

## Introduction

This document addresses critical bugs and missing implementations in the Sanfaani Schools Multi-School SaaS platform, a production-grade Laravel 13 application. The platform serves multiple schools with school_id isolation, role-based access control (Super Admin, School Admin, Result Officer, Teachers), and comprehensive result management workflows.

The bugs span multiple critical areas including result workspace operations, permission enforcement, scratch card result checking, communication log visibility, school mail system, role-based UI rendering, language system stability, responsive design issues, and performance optimization. These issues affect core business workflows including result entry, approval, publication, and public result checking via scratch cards.

## Bug Analysis

### Current Behavior (Defect)

#### 1. Scratch Card Result Check Crash

1.1 WHEN a student enters valid scratch card credentials and submits the result check form THEN the system crashes with error `StudentTransactionalEmailRequested::__construct(): Argument #1 ($school) must be of type App\Models\School`

1.2 WHEN the scratch card result check crashes THEN the result does not load and the user cannot view their result

1.3 WHEN the scratch card result check crashes THEN the audit log may not record the successful result access attempt

#### 2. Result Entry Permissions Broken

1.4 WHEN a teacher attempts to edit an approved result THEN the system allows the edit operation instead of preventing it

1.5 WHEN a teacher attempts to edit a published result THEN the system allows the edit operation instead of preventing it

1.6 WHEN a teacher attempts to edit a locked result THEN the system allows the edit operation instead of preventing it

1.7 WHEN a teacher attempts to access students or classes not assigned to them THEN the system allows access instead of denying it

1.8 WHEN a Result Officer with disabled permission flags attempts restricted operations THEN the system allows the operations instead of enforcing permission flags

1.9 WHEN a School Admin attempts operations outside their lifecycle management scope THEN the system may allow unauthorized operations

#### 3. Result Workspace Finalization Issues

1.10 WHEN a user performs result entry or editing in Student 360 Result Workspace THEN navigation breaks during result operations

1.11 WHEN a user performs result entry or editing in Result Management Workspace THEN navigation breaks during result operations

1.12 WHEN a teacher performs result entry in Assigned Teacher Result Entry THEN navigation breaks during result operations

1.13 WHEN a user views the result grid THEN inline actions (Add, Edit, Save Draft, Submit, Return, Approve, Publish, Unpublish, View Audit Log) are missing

1.14 WHEN a user views the result grid THEN columns for CA, Exam, Total, Grade, Pass/Fail, Remarks, Status, Source, Timestamps, and Audit Trail are incomplete or missing

1.15 WHEN a user attempts to transition result workflow states THEN state transitions (Draft → Submitted → Returned → Reviewed → Approved → Published → Unpublished → Archived → Locked) are not properly enforced

1.16 WHEN a user enters CA and Exam scores THEN the validation engine does not automatically calculate total, apply grading, detect pass/fail status, or prevent duplicates

1.17 WHEN result operations are performed THEN the result audit system does not log all actions properly

#### 4. Student 360 Result Operations

1.18 WHEN a user attempts inline editing in Student 360 Result view THEN the inline editing does not work

1.19 WHEN a user attempts to save draft or use autosave in Student 360 Result view THEN the save draft/autosave functionality is broken

1.20 WHEN a user attempts quick subject switching in Student 360 Result view THEN the quick subject switching is broken

1.21 WHEN a user views Student 360 Result validation THEN validation indicators are missing

1.22 WHEN a user views published results in Student 360 THEN publish visibility has issues

1.23 WHEN a user views audit information in Student 360 THEN audit visibility has issues

1.24 WHEN a user views Student 360 on mobile or tablet devices THEN responsive academic grids are broken

#### 5. Communication Log Visibility Leak

1.25 WHEN a teacher accesses the system THEN communication logs are visible to them instead of being hidden

1.26 WHEN a Result Officer accesses the system THEN communication logs are visible to them instead of being hidden

1.27 WHEN unauthorized roles access communication log routes directly THEN backend authorization policies are not enforced and access is granted

1.28 WHEN unauthorized roles view dashboards or navigation THEN communication log menu items, widgets, dashboard cards, quick links, API access, and navigation badges are visible instead of hidden

#### 6. School Mail System Issues

1.29 WHEN a school's SMTP settings are configured THEN the school SMTP settings do not work properly for all email types

1.30 WHEN school SMTP fails or is not configured THEN fallback SMTP is not properly configured or does not activate

1.31 WHEN emails are queued for sending THEN queue compatibility issues prevent proper email delivery

1.32 WHEN transactional emails, branded school emails, notification emails, support escalation emails, scratch card emails, or result publication emails are sent THEN not all email types work correctly

1.33 WHEN the mail system attempts to resolve mail drivers, switch SMTP dynamically, load per-school configuration, handle queue-safe mail, implement failover behavior, or store encrypted credentials THEN these operations are incomplete or broken

#### 7. Role Sidebar Issues

1.34 WHEN users view the sidebar navigation THEN duplicated menu items are rendered

1.35 WHEN users view the sidebar navigation THEN unauthorized modules are visible to roles that should not see them

1.36 WHEN users view the sidebar navigation THEN permission leakage occurs showing items the user cannot access

1.37 WHEN users view the sidebar navigation THEN orphan links to non-existent or unauthorized pages are present

1.38 WHEN users access routes directly THEN hidden-but-accessible routes allow unauthorized access

1.39 WHEN Super Admin views the sidebar THEN non-Platform modules are visible instead of only Platform modules

1.40 WHEN School Admin views the sidebar THEN non-School operational modules are visible instead of only School operational modules

1.41 WHEN Result Officer views the sidebar THEN non-Result workspace modules are visible instead of only Result workspace modules

1.42 WHEN Teachers view the sidebar THEN non-Assigned operational tools are visible instead of only Assigned operational tools

#### 8. Language System Instability

1.43 WHEN a user changes their language preference THEN translation persistence is broken and the preference is not saved

1.44 WHEN backend translations are loaded THEN backend translation loading is incomplete for some areas

1.45 WHEN navbar, validation messages, or dashboard elements are displayed THEN translation issues occur

1.46 WHEN session or user preference persistence is attempted THEN session persistence and user preference persistence are broken

1.47 WHEN Arabic language is selected THEN RTL support is incomplete with spacing and alignment issues

#### 9. Responsive Design Issues

1.48 WHEN users view the application on mobile or tablet devices THEN overlapping text, broken table overflow, and hidden buttons occur

1.49 WHEN users interact with UI elements on mobile devices THEN z-index issues, sidebar collapse bugs, and mobile topbar issues occur

1.50 WHEN users interact with dropdowns or view dark mode THEN dropdown overflow and dark mode contrast problems occur

1.51 WHEN users scroll tables or view cards THEN sticky headers do not work, tables do not scroll correctly, and cards do not align properly

1.52 WHEN users access the application on low-end Android devices THEN layouts are unusable

#### 10. Performance Issues

1.53 WHEN database queries are executed THEN N+1 queries occur causing performance degradation

1.54 WHEN relationships are loaded THEN missing eager loading causes multiple unnecessary queries

1.55 WHEN queries are executed THEN indexed query usage is not optimized

1.56 WHEN large result sets are displayed THEN pagination optimization is needed but not implemented

1.57 WHEN resources are loaded THEN lazy loading is not implemented causing unnecessary data loading

1.58 WHEN queries are executed THEN query scopes are missing for common filtering patterns

1.59 WHEN data is accessed repeatedly THEN safe caching is not implemented causing redundant queries

1.60 WHEN large schools with thousands of students use the system THEN performance issues occur making the system slow or unusable

### Expected Behavior (Correct)

#### 1. Scratch Card Result Check Crash

2.1 WHEN a student enters valid scratch card credentials and submits the result check form THEN the system SHALL dispatch the StudentTransactionalEmailRequested event correctly without crashing

2.2 WHEN the scratch card result check completes successfully THEN the result SHALL load and display to the user

2.3 WHEN the scratch card result check completes successfully THEN the audit log SHALL record the successful result access attempt with all relevant metadata

#### 2. Result Entry Permissions Enforcement

2.4 WHEN a teacher attempts to edit an approved result THEN the system SHALL prevent the edit operation and display an appropriate error message

2.5 WHEN a teacher attempts to edit a published result THEN the system SHALL prevent the edit operation and display an appropriate error message

2.6 WHEN a teacher attempts to edit a locked result THEN the system SHALL prevent the edit operation and display an appropriate error message

2.7 WHEN a teacher attempts to access students or classes not assigned to them THEN the system SHALL deny access and display an appropriate error message

2.8 WHEN a Result Officer with disabled permission flags attempts restricted operations THEN the system SHALL enforce permission flags and deny unauthorized operations

2.9 WHEN a School Admin attempts operations outside their lifecycle management scope THEN the system SHALL enforce proper authorization and deny unauthorized operations

#### 3. Result Workspace Finalization

2.10 WHEN a user performs result entry or editing in Student 360 Result Workspace THEN navigation SHALL work correctly without breaking

2.11 WHEN a user performs result entry or editing in Result Management Workspace THEN navigation SHALL work correctly without breaking

2.12 WHEN a teacher performs result entry in Assigned Teacher Result Entry THEN navigation SHALL work correctly without breaking

2.13 WHEN a user views the result grid THEN inline actions (Add, Edit, Save Draft, Submit, Return, Approve, Publish, Unpublish, View Audit Log) SHALL be present and functional

2.14 WHEN a user views the result grid THEN all columns (CA, Exam, Total, Grade, Pass/Fail, Remarks, Status, Source, Timestamps, Audit Trail) SHALL be complete and properly displayed

2.15 WHEN a user attempts to transition result workflow states THEN state transitions (Draft → Submitted → Returned → Reviewed → Approved → Published → Unpublished → Archived → Locked) SHALL be properly enforced with validation

2.16 WHEN a user enters CA and Exam scores THEN the validation engine SHALL automatically calculate total, apply grading, detect pass/fail status, and prevent duplicates

2.17 WHEN result operations are performed THEN the result audit system SHALL log all actions properly with complete metadata

#### 4. Student 360 Result Operations

2.18 WHEN a user attempts inline editing in Student 360 Result view THEN the inline editing SHALL work correctly

2.19 WHEN a user attempts to save draft or use autosave in Student 360 Result view THEN the save draft/autosave functionality SHALL work correctly

2.20 WHEN a user attempts quick subject switching in Student 360 Result view THEN the quick subject switching SHALL work correctly

2.21 WHEN a user views Student 360 Result validation THEN validation indicators SHALL be present and accurate

2.22 WHEN a user views published results in Student 360 THEN publish visibility SHALL work correctly

2.23 WHEN a user views audit information in Student 360 THEN audit visibility SHALL work correctly

2.24 WHEN a user views Student 360 on mobile or tablet devices THEN responsive academic grids SHALL display and function correctly

#### 5. Communication Log Visibility Enforcement

2.25 WHEN a teacher accesses the system THEN communication logs SHALL be hidden from them

2.26 WHEN a Result Officer accesses the system THEN communication logs SHALL be hidden from them

2.27 WHEN unauthorized roles access communication log routes directly THEN backend authorization policies SHALL be enforced and access SHALL be denied with appropriate error

2.28 WHEN unauthorized roles view dashboards or navigation THEN communication log menu items, widgets, dashboard cards, quick links, API access, and navigation badges SHALL be hidden

#### 6. School Mail System Completion

2.29 WHEN a school's SMTP settings are configured THEN the school SMTP settings SHALL work properly for all email types (transactional, branded, notification, support, scratch card, result publication)

2.30 WHEN school SMTP fails or is not configured THEN fallback SMTP SHALL be properly configured and activate automatically

2.31 WHEN emails are queued for sending THEN queue compatibility SHALL work correctly ensuring proper email delivery

2.32 WHEN transactional emails, branded school emails, notification emails, support escalation emails, scratch card emails, or result publication emails are sent THEN all email types SHALL work correctly

2.33 WHEN the mail system attempts to resolve mail drivers, switch SMTP dynamically, load per-school configuration, handle queue-safe mail, implement failover behavior, or store encrypted credentials THEN these operations SHALL be complete and functional

#### 7. Role Sidebar Cleanup

2.34 WHEN users view the sidebar navigation THEN no duplicated menu items SHALL be rendered

2.35 WHEN users view the sidebar navigation THEN only authorized modules SHALL be visible to each role

2.36 WHEN users view the sidebar navigation THEN no permission leakage SHALL occur and only accessible items SHALL be shown

2.37 WHEN users view the sidebar navigation THEN no orphan links to non-existent or unauthorized pages SHALL be present

2.38 WHEN users access routes directly THEN hidden routes SHALL be properly protected and unauthorized access SHALL be denied

2.39 WHEN Super Admin views the sidebar THEN only Platform modules SHALL be visible

2.40 WHEN School Admin views the sidebar THEN only School operational modules SHALL be visible

2.41 WHEN Result Officer views the sidebar THEN only Result workspace modules SHALL be visible

2.42 WHEN Teachers view the sidebar THEN only Assigned operational tools SHALL be visible

#### 8. Language System Stabilization

2.43 WHEN a user changes their language preference THEN translation persistence SHALL work correctly and the preference SHALL be saved

2.44 WHEN backend translations are loaded THEN backend translation loading SHALL be complete for all areas

2.45 WHEN navbar, validation messages, or dashboard elements are displayed THEN translations SHALL work correctly

2.46 WHEN session or user preference persistence is attempted THEN session persistence and user preference persistence SHALL work correctly

2.47 WHEN Arabic language is selected THEN RTL support SHALL be complete with proper spacing and alignment

#### 9. Responsive Design Stabilization

2.48 WHEN users view the application on mobile or tablet devices THEN no overlapping text, broken table overflow, or hidden buttons SHALL occur

2.49 WHEN users interact with UI elements on mobile devices THEN z-index issues, sidebar collapse bugs, and mobile topbar issues SHALL be resolved

2.50 WHEN users interact with dropdowns or view dark mode THEN dropdown overflow and dark mode contrast SHALL work correctly

2.51 WHEN users scroll tables or view cards THEN sticky headers SHALL work, tables SHALL scroll correctly, and cards SHALL align properly

2.52 WHEN users access the application on low-end Android devices THEN layouts SHALL be usable and functional

#### 10. Performance Optimization

2.53 WHEN database queries are executed THEN N+1 queries SHALL be eliminated through proper eager loading

2.54 WHEN relationships are loaded THEN eager loading SHALL be implemented to minimize queries

2.55 WHEN queries are executed THEN indexed query usage SHALL be optimized for performance

2.56 WHEN large result sets are displayed THEN pagination optimization SHALL be implemented

2.57 WHEN resources are loaded THEN lazy loading SHALL be implemented to load data only when needed

2.58 WHEN queries are executed THEN query scopes SHALL be available for common filtering patterns

2.59 WHEN data is accessed repeatedly THEN safe caching SHALL be implemented to reduce redundant queries

2.60 WHEN large schools with thousands of students use the system THEN performance SHALL be acceptable and the system SHALL remain responsive

### Unchanged Behavior (Regression Prevention)

#### 1. Scratch Card Result Check

3.1 WHEN a student enters invalid scratch card credentials THEN the system SHALL CONTINUE TO display appropriate error messages without crashing

3.2 WHEN a student successfully views their result THEN the system SHALL CONTINUE TO display the complete result with all subjects, scores, grades, and report card data

3.3 WHEN scratch card usage is recorded THEN the system SHALL CONTINUE TO prevent reuse of the same card for the same result

#### 2. Result Entry Workflow

3.4 WHEN authorized users perform result entry operations within their scope THEN the system SHALL CONTINUE TO allow these operations

3.5 WHEN result workflow transitions are valid THEN the system SHALL CONTINUE TO process state transitions correctly

3.6 WHEN result calculations are performed THEN the system SHALL CONTINUE TO calculate totals, grades, and pass/fail status accurately

#### 3. Communication System

3.7 WHEN Super Admin accesses global communication history THEN the system SHALL CONTINUE TO display all communication logs across all schools

3.8 WHEN School Admin with explicit permission accesses school-scoped communication logs THEN the system SHALL CONTINUE TO display communication logs for their school only

3.9 WHEN communication emails are sent THEN the system SHALL CONTINUE TO deliver emails successfully with proper branding and content

#### 4. Role-Based Access Control

3.10 WHEN users access features within their authorized scope THEN the system SHALL CONTINUE TO grant access

3.11 WHEN users attempt to access features outside their authorized scope THEN the system SHALL CONTINUE TO deny access

3.12 WHEN role-based permissions are evaluated THEN the system SHALL CONTINUE TO enforce school_id isolation for multi-tenancy

#### 5. School Mail System

3.13 WHEN platform-level SMTP is configured and school SMTP is not configured THEN the system SHALL CONTINUE TO use platform SMTP as fallback

3.14 WHEN emails are sent with school branding THEN the system SHALL CONTINUE TO include school logo, colors, and branding elements

3.15 WHEN email delivery fails THEN the system SHALL CONTINUE TO log failures and update communication log status

#### 6. Language and Localization

3.16 WHEN users with English language preference access the system THEN the system SHALL CONTINUE TO display English translations

3.17 WHEN users with French language preference access the system THEN the system SHALL CONTINUE TO display French translations

3.18 WHEN users with Arabic language preference access the system THEN the system SHALL CONTINUE TO display Arabic translations with RTL layout

#### 7. Responsive Design

3.19 WHEN users access the application on desktop devices THEN the system SHALL CONTINUE TO display layouts correctly

3.20 WHEN users access the application on supported mobile and tablet devices THEN the system SHALL CONTINUE TO provide functional interfaces

#### 8. Performance

3.21 WHEN small to medium schools use the system THEN the system SHALL CONTINUE TO perform well

3.22 WHEN database queries use existing indexes THEN the system SHALL CONTINUE TO execute efficiently

3.23 WHEN caching is already implemented THEN the system SHALL CONTINUE TO use cached data appropriately

#### 9. Audit System

3.24 WHEN audit-worthy actions are performed THEN the system SHALL CONTINUE TO log these actions with complete metadata

3.25 WHEN audit logs are queried THEN the system SHALL CONTINUE TO return accurate historical records

3.26 WHEN audit logs are displayed in UI THEN the system SHALL CONTINUE TO show formatted, readable audit information

#### 10. Student 360 and Result Management

3.27 WHEN users access Student 360 profile, enrollment, or other non-result sections THEN the system SHALL CONTINUE TO function correctly

3.28 WHEN users access Result Management for authorized operations THEN the system SHALL CONTINUE TO provide full functionality

3.29 WHEN report cards are generated THEN the system SHALL CONTINUE TO produce accurate, properly formatted report cards

3.30 WHEN result publications are created THEN the system SHALL CONTINUE TO publish results correctly with proper visibility controls
