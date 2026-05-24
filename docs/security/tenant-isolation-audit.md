# Tenant Isolation Audit

Date: 2026-05-24
Branch: `feature/v7-cbt-localization-hardening`

## Tenant Boundary Summary

Sanfaani Schools is a multi-school SaaS application where `schools.id` is the tenant boundary for operational school data. Platform/Super Admin workflows intentionally operate across schools for onboarding, subscriptions, scratch-card approvals, support escalation, marketing, audit, and platform settings. School routes are protected by `auth`, `school.context`, active role middleware, school feature middleware, and controller/service checks against the current school.

This audit did not introduce a new tenancy package or broad global scopes. The hardening approach keeps Super Admin platform visibility intact and verifies school-level users fail closed when they attempt to access records from another school.

## Tenant-Scoped Models

Models with direct `school_id` tenant ownership or nullable school context:

- `AcademicSession`
- `AdmissionNumberSetting`
- `AuditLog`
- `BulkCommunicationBatch`
- `BulkCommunicationRecipient`
- `CbtAccessCode`
- `CbtAttempt`
- `CbtAttemptAnswer`
- `CbtCandidate`
- `CbtEventLog`
- `CbtExam`
- `CbtExamQuestion`
- `CbtMarkingRecord`
- `CbtQuestion`
- `CbtQuestionBank`
- `CbtQuestionOption`
- `CbtResultPublication`
- `ClassSubjectAssignment`
- `CommunicationLog`
- `GradingScale`
- `LanguagePreference`
- `MailSetting`
- `NotificationPreference`
- `OnboardingProgress`
- `PaymentTransaction`
- `PdfSnapshot`
- `ReportCardCommentRule`
- `ReportCardSnapshot`
- `ResultPublication`
- `ResultVerification`
- `SchoolClass`
- `SchoolFeatureOverride`
- `SchoolPublicPage`
- `SchoolReportCardSetting`
- `SchoolResultAccessPolicy`
- `SchoolRoleFeatureSetting`
- `SchoolSubscription`
- `SchoolWebsiteSetting`
- `ScratchCard`
- `ScratchCardBatch`
- `ScratchCardUsage`
- `Student`
- `StudentClassEnrollment`
- `StudentElectiveSubject`
- `StudentPromotionBatch`
- `StudentPromotionItem`
- `StudentResult`
- `Subject`
- `SupportEscalationHistory`
- `SupportMessage`
- `SupportMessageAttachment`
- `SupportThread`
- `SupportThreadEvent`
- `TeacherClassAssignment`
- `TeacherResultSubmission`
- `TeacherSubjectAssignment`
- `Term`
- `User`
- `UserSchoolRole`

## Global/Platform Model List

Models that are platform-level by design:

- `School`, the tenant root managed by Super Admin workflows.
- `SubscriptionPlan` and `PlanFeature`, shared SaaS plan catalog.
- `PlatformSetting`, platform branding/settings.
- `PaymentGatewaySetting`, platform payment gateway configuration.
- `ReportCardTemplate`, shared template catalog.
- `LeadRequest`, `LeadNote`, `LeadOwnershipHistory`, `LeadCommunicationRecord`, and `LeadTimelineEvent`, platform CRM/onboarding data. `converted_school_id` links a lead to a school after conversion but does not make the lead school-owned.
- `MarketingAutomation`, `MarketingCampaign`, `MarketingEmailTemplate`, `MarketingCampaignRecipient`, `MarketingDeliveryEvent`, and `MarketingSuppression`, platform marketing data.
- `SystemUpdateLog`, platform update metadata.
- Spatie `Role` and `Permission` records, global authorization catalog.

No verified model currently needed a new `school_id` column. Marketing and lead records are intentionally platform-level.

## Role Boundary Matrix

| Role | Intended boundary |
| --- | --- |
| Super Admin | Global platform access, plus explicit support-mode access into a school. |
| School Admin | Current active school only; can manage school operational data for that tenant. |
| Teacher | Current active school only; student visibility is limited to assigned classes/subjects. |
| Result Officer | Current active school only; result workflow access only through enabled role features. |
| Parent | No private staff/school workspace routes are implemented yet; current behavior fails closed. |
| Student | No private student portal routes are implemented yet; current behavior fails closed. |
| Accountant | No accounting module is present; current behavior fails closed on school staff routes. |

## High-Risk Controllers

- School tenant controllers: `StudentController`, `StudentResultWorkspaceController`, `ManualResultController`, `ResultPublishingController`, `TeacherResultEntryController`, `TeacherResultReviewController`, `TeacherAssignmentController`, `ScratchCardController`, `SupportThreadController`, `CommunicationController`, CBT controllers, academic setup controllers, staff/class/subject controllers.
- Platform controllers with intentional global visibility: `SchoolController`, `SchoolSubscriptionController`, `SchoolFeatureOverrideController`, `ScratchCardRequestController`, `LeadRequestController`, marketing controllers, `SupportThreadController`, `CommunicationController`, `AuditLogController`, `SecurityController`, payment/settings controllers.
- Public controllers: `ResultCheckerController`, `PublicResultController`, `CbtAccessController`, and `SchoolPublicPageController`.

## High-Risk Jobs

- `ProcessBulkCommunicationBatch`, school-owned bulk communication processing.
- `DispatchMarketingCampaign`, platform marketing campaign dispatch.
- `SendMarketingCampaignEmail`, platform marketing recipient dispatch.
- `RunMarketingAutomations`, platform automation scheduler.

## High-Risk Notifications/Emails

- `StudentTransactionalMail`, `StudentLifecycleMail`, `ResultPublishedNotification`, and `StudentCreatedGuardianNotification`.
- `StaffTransactionalMail`, `StaffLifecycleMail`, and `UserAccountCreatedNotification`.
- `SchoolNotificationMail`, `SchoolNotificationRequested` listener flow, and support ticket notifications.
- `ScratchCardRequestStatusNotification`.
- `MarketingCampaignMail`.

## File/Storage Isolation Findings

Support attachment download routes already check support-thread visibility before streaming files. The verified gap was storage organization: new support uploads used a shared `support-attachments` directory. This step changed new school support uploads to `support-attachments/schools/{school_id}/...` and platform-only support uploads to `support-attachments/platform/...`.

Existing historical files remain access-controlled through attachment records and support-thread authorization. A later migration/backfill can reorganize existing files if operationally required.

## Existing Protections

- `EnsureValidSchoolContext` validates active school/role context and rejects users who do not belong to the selected school.
- `EnsureActiveRole` enforces route role boundaries using the active role context.
- `EnsureSchoolFeatureEnabled` and `SchoolAuthorizationService` enforce school feature and role-feature access.
- `CurrentSchoolService` resolves active school from support mode, tenant context, or assigned school.
- Student access uses `SchoolAuthorizationService::canViewStudent`.
- Result access uses `StudentResultPolicy` and `TeacherResultSubmissionPolicy`.
- Teacher assignment access uses `TeacherAssignmentPolicy`.
- Support access uses `SupportRoutingService::visibleSchoolThreadsQuery` and platform visibility rules.
- School-owned controllers generally query through `$school->relationship()` or explicit `where('school_id', $school->id)`.
- Public result and CBT flows resolve school first, then scope result/student/exam lookups to that school.

## Gaps Found

- Support uploads were authorized at download time but stored under a shared directory rather than a tenant-prefixed path.
- `ProcessBulkCommunicationBatch` used the batch record as tenant context but did not accept an explicit school context for fail-closed queued execution.
- Existing test coverage covered several school features but did not centralize cross-school isolation checks across students, support, scratch cards, result publishing, platform-only routes, and queued jobs.

## Patches Applied

- New support uploads now use school-prefixed storage paths.
- Platform support uploads now use a platform-prefixed path when no school context exists.
- `ProcessBulkCommunicationBatch` now accepts an optional `schoolId` and returns without processing when the queued school context does not match the batch.
- Added security feature tests for tenant isolation, authorization boundaries, and tenant-aware jobs.

## Remaining Risks

- Historical support attachments may still exist under the previous shared directory. Authorization protects them, but storage layout can be backfilled later.
- Public result-checker and public CBT flows are school-scoped, but they should continue receiving regression tests as those modules expand.
- Parent, student, and accountant private portals are not implemented. When added, they need dedicated policies and route groups before exposing private data.
- Marketing and lead data is platform-scoped by design. If school-level marketing is introduced later, it should not reuse platform CRM records without explicit scoping.

## Recommended Next Security Tasks

- Add policy coverage for any new parent, student, or accountant portal before route exposure.
- Add route-model binding helpers or policies for remaining school-owned resources that currently use inline `school_id` checks.
- Add storage lifecycle checks for exports, report-card PDFs, CBT uploads, and generated files as those modules grow.
- Add security tests for public signed/download URLs whenever new file delivery endpoints are introduced.
