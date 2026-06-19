<?php

namespace App\Services;

use App\Models\SchoolFeatureSetting;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SchoolRoleFeatureService
{
    private const SCHOOL_ROLES = [
        'school_admin',
        'teacher',
        'result_officer',
        'accountant',
        'parent',
        'student',
    ];

    private const SUPPORT_ROLES = [
        'school_admin',
        'teacher',
        'result_officer',
        'accountant',
        'parent',
        'student',
    ];

    private const FEATURE_ALIASES = [
        'manual_result_entry' => 'results.manual_entry',
        'csv_result_upload' => 'results.upload',
        'result_publishing' => 'results.publish',
        'public_result_checker' => 'public.result_checker',
        'scratch_cards' => 'scratch_cards.manage',
        'student_bulk_upload' => 'student.bulk_upload',
        'school_finance' => 'finance.manage',
        'communication_tools' => 'communication.logs.view',

        'school.profile.view' => 'school.profile.manage',
        'school.branding.view' => 'school.branding.manage',
        'school.settings.manage' => 'school.profile.manage',
        'staff.manage' => 'school.users.manage',
        'users.manage' => 'school.users.manage',
        'roles.manage' => 'school.roles.manage',
        'feature.control.manage' => 'school.features.manage',

        'school.feature-control.index' => 'school.features.manage',
        'school.feature-control.update' => 'school.features.manage',
        'school.role-features.edit' => 'school.features.manage',
        'school.role-features.update' => 'school.features.manage',
        'school.role-permissions.index' => 'school.roles.manage',
        'school.role-permissions.update' => 'school.roles.manage',

        'school.profile.edit' => 'school.profile.manage',
        'school.profile.update' => 'school.profile.manage',
        'school.public-page.edit' => 'school.profile.manage',
        'school.public-page.update' => 'school.profile.manage',
        'school.admission-number-settings.edit' => 'admission_numbers.manage',
        'school.admission-number-settings.update' => 'admission_numbers.manage',

        'classes.manage' => 'school.profile.manage',
        'classes.create' => 'school.profile.manage',
        'classes.store' => 'school.profile.manage',
        'classes.edit' => 'school.profile.manage',
        'classes.update' => 'school.profile.manage',
        'classes.destroy' => 'school.profile.manage',
        'classes.delete' => 'school.profile.manage',
        'classes.archive' => 'school.profile.manage',
        'classes.restore' => 'school.profile.manage',
        'school.classes.index' => 'school.profile.manage',
        'school.classes.create' => 'school.profile.manage',
        'school.classes.store' => 'school.profile.manage',
        'school.classes.edit' => 'school.profile.manage',
        'school.classes.update' => 'school.profile.manage',
        'school.classes.destroy' => 'school.profile.manage',
        'school.classes.restore' => 'school.profile.manage',
        'school.classes.upload.index' => 'school.profile.manage',
        'school.classes.upload.store' => 'school.profile.manage',
        'school.classes.upload.template' => 'school.profile.manage',

        'subjects.manage' => 'school.profile.manage',
        'subjects.create' => 'school.profile.manage',
        'subjects.store' => 'school.profile.manage',
        'subjects.edit' => 'school.profile.manage',
        'subjects.update' => 'school.profile.manage',
        'subjects.destroy' => 'school.profile.manage',
        'subjects.delete' => 'school.profile.manage',
        'subjects.archive' => 'school.profile.manage',
        'subjects.restore' => 'school.profile.manage',
        'school.subjects.index' => 'school.profile.manage',
        'school.subjects.create' => 'school.profile.manage',
        'school.subjects.store' => 'school.profile.manage',
        'school.subjects.edit' => 'school.profile.manage',
        'school.subjects.update' => 'school.profile.manage',
        'school.subjects.destroy' => 'school.profile.manage',
        'school.subjects.restore' => 'school.profile.manage',
        'school.subjects.upload.index' => 'school.profile.manage',
        'school.subjects.upload.store' => 'school.profile.manage',
        'school.subjects.upload.template' => 'school.profile.manage',
        'school.subject-assignments.index' => 'school.profile.manage',
        'school.subject-assignments.create' => 'school.profile.manage',
        'school.subject-assignments.store' => 'school.profile.manage',
        'school.subject-assignments.edit' => 'school.profile.manage',
        'school.subject-assignments.update' => 'school.profile.manage',
        'school.subject-assignments.archive' => 'school.profile.manage',
        'school.subject-assignments.restore' => 'school.profile.manage',

        'sessions.create' => 'sessions.manage',
        'sessions.store' => 'sessions.manage',
        'sessions.edit' => 'sessions.manage',
        'sessions.update' => 'sessions.manage',
        'sessions.destroy' => 'sessions.manage',
        'sessions.delete' => 'sessions.manage',
        'sessions.archive' => 'sessions.manage',
        'sessions.restore' => 'sessions.manage',
        'school.sessions.index' => 'sessions.manage',
        'school.sessions.create' => 'sessions.manage',
        'school.sessions.store' => 'sessions.manage',
        'school.sessions.edit' => 'sessions.manage',
        'school.sessions.update' => 'sessions.manage',
        'school.sessions.activate' => 'sessions.manage',
        'school.sessions.archive' => 'sessions.manage',
        'school.sessions.restore' => 'sessions.manage',

        'terms.create' => 'terms.manage',
        'terms.store' => 'terms.manage',
        'terms.edit' => 'terms.manage',
        'terms.update' => 'terms.manage',
        'terms.destroy' => 'terms.manage',
        'terms.delete' => 'terms.manage',
        'terms.archive' => 'terms.manage',
        'terms.restore' => 'terms.manage',
        'school.terms.index' => 'terms.manage',
        'school.terms.create' => 'terms.manage',
        'school.terms.store' => 'terms.manage',
        'school.terms.edit' => 'terms.manage',
        'school.terms.update' => 'terms.manage',
        'school.terms.activate' => 'terms.manage',
        'school.terms.archive' => 'terms.manage',
        'school.terms.restore' => 'terms.manage',

        'students.create' => 'students.manage',
        'students.store' => 'students.manage',
        'students.edit' => 'students.manage',
        'students.update' => 'students.manage',
        'students.destroy' => 'students.manage',
        'students.delete' => 'students.manage',
        'students.archive' => 'students.manage',
        'students.restore' => 'students.manage',
        'school.students.create' => 'students.manage',
        'school.students.store' => 'students.manage',
        'school.students.edit' => 'students.manage',
        'school.students.update' => 'students.manage',
        'school.students.destroy' => 'students.manage',
        'school.students.restore' => 'students.manage',
        'school.students.upload.index' => 'student.bulk_upload',
        'school.students.upload.store' => 'student.bulk_upload',
        'school.students.upload.template' => 'student.bulk_upload',
        'school.students.portal.parents.create' => 'students.manage',
        'school.students.portal.parents.link' => 'students.manage',
        'school.students.portal.parents.unlink' => 'students.manage',
        'school.students.portal.student-account.create' => 'students.manage',
        'school.students.portal.student-account.link' => 'students.manage',
        'school.students.portal.student-account.unlink' => 'students.manage',
        'school.students.elective-subjects.store' => 'students.manage',
        'school.students.elective-subjects.destroy' => 'students.manage',

        'staff.create' => 'school.users.manage',
        'staff.store' => 'school.users.manage',
        'staff.edit' => 'school.users.manage',
        'staff.update' => 'school.users.manage',
        'staff.disable' => 'school.users.manage',
        'staff.enable' => 'school.users.manage',
        'staff.archive' => 'school.users.manage',
        'staff.restore' => 'school.users.manage',
        'staff.destroy' => 'school.users.manage',
        'staff.delete' => 'school.users.manage',
        'school.staff.index' => 'school.users.manage',
        'school.staff.create' => 'school.users.manage',
        'school.staff.store' => 'school.users.manage',
        'school.staff.edit' => 'school.users.manage',
        'school.staff.update' => 'school.users.manage',
        'school.staff.disable' => 'school.users.manage',
        'school.staff.enable' => 'school.users.manage',
        'school.staff.archive' => 'school.users.manage',
        'school.staff.restore' => 'school.users.manage',
        'school.staff.destroy' => 'school.users.manage',
        'school.staff.send-setup-link' => 'school.users.manage',

        'result.access.approve' => 'result.access.manage',
        'result.access.reject' => 'result.access.manage',
        'result.access.unlock' => 'result.access.manage',
        'result.access.manual_unlock' => 'result.access.manage',
        'school.result-access-requests.index' => 'result.access.manage',
        'school.result-access-requests.approve' => 'result.access.manage',
        'school.result-access-requests.reject' => 'result.access.manage',
        'school.result-access-requests.manual-unlock' => 'result.access.manage',
        'portal.results.index' => 'result.access.portal',
        'portal.results.show' => 'result.access.portal',
        'portal.results.requests.store' => 'result.access.portal',

        'support.manage' => 'support.access',
        'support.view' => 'support.access',
        'support.create' => 'support.access',
        'support.reply' => 'support.access',
        'support.close' => 'support.access',
        'support.tickets.view' => 'support.access',
        'support.tickets.create' => 'support.access',
        'support.tickets.reply' => 'support.access',
        'support.threads.view' => 'support.access',
        'support.threads.create' => 'support.access',
        'support.threads.reply' => 'support.access',
        'support.messages.reply' => 'support.access',
        'support.uploads.create' => 'support.access',
        'support.attachments.download' => 'support.access',
        'school.support.index' => 'support.access',
        'school.support.create' => 'support.access',
        'school.support.store' => 'support.access',
        'school.support.show' => 'support.access',
        'school.support.reply' => 'support.access',
        'school.support.close' => 'support.access',
        'school.support-attachments.download' => 'support.access',

        'support.assign' => 'support.assign',
        'support.tickets.assign' => 'support.assign',
        'support.threads.assign' => 'support.assign',
        'school.support.assign' => 'support.assign',
        'support.escalate' => 'support.escalate',
        'support.tickets.escalate' => 'support.escalate',
        'support.threads.escalate' => 'support.escalate',
        'school.support.escalate' => 'support.escalate',
        'support.routing.manage' => 'support.routing.manage',

        'communications.manage' => 'communication.logs.view',
        'live_class.view' => 'live_classes.view',
        'live-class.view' => 'live_classes.view',
        'live-classes.view' => 'live_classes.view',
        'live_class.create' => 'live_classes.create',
        'live-class.create' => 'live_classes.create',
        'live-classes.create' => 'live_classes.create',
        'live_class.join' => 'live_classes.join',
        'live-class.join' => 'live_classes.join',
        'live-classes.join' => 'live_classes.join',
        'live_class.manage' => 'live_classes.manage',
        'live-class.manage' => 'live_classes.manage',
        'live-classes.manage' => 'live_classes.manage',
    ];

    private const FEATURE_PREREQUISITES = [
        'support.assign' => ['support.access'],
        'support.escalate' => ['support.access'],
        'support.routing.manage' => ['support.access'],
        'support.direct_escalation' => ['support.access'],
    ];

    /** @var array<string, bool|null> */
    private array $settingCache = [];

    public function roleNames(): array
    {
        return self::SCHOOL_ROLES;
    }

    public function catalog(): array
    {
        $supportRoles = self::SUPPORT_ROLES;

        return [
            'school.profile.manage' => $this->feature('School profile', 'Administration', 'Manage school profile, sessions, terms, and core settings.', ['school_admin']),
            'school.branding.manage' => $this->feature('Branding', 'Administration', 'Manage school logo, colors, and public identity.', ['school_admin']),
            'school.users.manage' => $this->feature('User management', 'Administration', 'Create, link, archive, restore, and manage portal users.', ['school_admin']),
            'school.roles.manage' => $this->feature('Roles and permissions', 'Administration', 'Manage school role capabilities and access settings.', ['school_admin']),
            'school.features.manage' => $this->feature('Feature control', 'Administration', 'Enable or disable school features by role.', ['school_admin']),
            'sessions.manage' => $this->feature('Academic sessions', 'Administration', 'Manage academic sessions.', ['school_admin']),
            'terms.manage' => $this->feature('Terms', 'Administration', 'Manage school terms.', ['school_admin']),
            'admission_numbers.manage' => $this->feature('Admission numbers', 'Administration', 'Manage admission number settings.', ['school_admin']),
            'teacher.assignment.manage' => $this->feature('Teacher assignments', 'Administration', 'Assign teachers to classes and subjects.', ['school_admin']),
            'teacher.assignments.view' => $this->feature('Assigned classes', 'Teaching', 'Allow teachers to view their assigned classes and subjects.', ['teacher']),

            'students.view' => $this->feature('Students', 'Academics', 'View student records and profile workflows.', ['school_admin', 'result_officer']),
            'students.view_assigned' => $this->feature('Assigned students', 'Academics', 'Allow teachers to view students in assigned classes only.', ['teacher']),
            'students.manage' => $this->feature('Manage students', 'Academics', 'Create and update student records.', ['school_admin', 'result_officer']),
            'student.bulk_upload' => $this->feature('Student bulk upload', 'Academics', 'Import student records in bulk.', ['school_admin']),
            'student.promote' => $this->feature('Student promotions', 'Academics', 'Promote students between classes and sessions.', ['school_admin', 'result_officer']),
            'student.transfer' => $this->feature('Student transfers', 'Academics', 'Transfer students between classes or schools.', ['school_admin', 'result_officer']),

            'reports.view' => $this->feature('Reports', 'Reports', 'View academic and operational reports.', ['school_admin', 'result_officer']),
            'reports.manage' => $this->feature('Report management', 'Reports', 'Manage report workflows and reporting tools.', ['school_admin', 'result_officer']),
            'pdf.snapshots' => $this->feature('PDF snapshots', 'Reports', 'Generate PDF report and result snapshots.', ['school_admin', 'result_officer']),

            'results.view' => $this->feature('View results', 'Results', 'View student result records.', ['school_admin', 'teacher', 'result_officer']),
            'results.manual_entry' => $this->feature('Result entry', 'Results', 'Enter student results manually.', ['school_admin', 'result_officer']),
            'results.upload' => $this->feature('Result upload', 'Results', 'Upload result sheets and templates.', ['school_admin', 'result_officer']),
            'results.review' => $this->feature('Result review', 'Results', 'Review and approve submitted results.', ['school_admin', 'result_officer']),
            'results.publish' => $this->feature('Result publishing', 'Results', 'Publish and unpublish approved results.', ['school_admin', 'result_officer']),
            'results.manage' => $this->feature('Result management', 'Results', 'Prepare, review, publish, and manage student results.', ['school_admin', 'result_officer']),
            'teacher.results.view' => $this->feature('Teacher result workspace', 'Results', 'Allow teachers to view their result submissions.', ['teacher']),
            'teacher.results.create' => $this->feature('Teacher result entry', 'Results', 'Allow teachers to enter results for assigned classes.', ['teacher']),
            'teacher.results.submit' => $this->feature('Teacher result submissions', 'Results', 'Allow teachers to submit result entries for review.', ['teacher']),
            'result.access.manage' => $this->feature('Result access requests', 'Results', 'Approve, reject, and manage result access requests.', ['school_admin', 'result_officer']),
            'result.access.portal' => $this->feature('Portal result access', 'Portal', 'Allow parents and students to request or unlock published results.', ['parent', 'student']),
            'public.result_checker' => $this->feature('Public result checker', 'Portal', 'Allow public result checker workflows for published results.', ['school_admin', 'result_officer']),
            'scratch_cards.manage' => $this->feature('Scratch cards', 'Results', 'Manage scratch-card batches and result access cards.', ['school_admin', 'result_officer']),
            'report_cards.basic' => $this->feature('Report cards', 'Results', 'View and generate basic report cards.', ['school_admin', 'result_officer']),
            'report_cards.customize' => $this->feature('Report card customization', 'Results', 'Customize report-card layout and branding.', ['school_admin']),
            'report_cards.signatures' => $this->feature('Report card signatures', 'Results', 'Manage report-card signature blocks.', ['school_admin']),
            'report_cards.comments' => $this->feature('Report card comments', 'Results', 'Manage automated report-card comments.', ['school_admin', 'result_officer']),

            'communication.send' => $this->feature('Student communication', 'Communication', 'Send student and guardian messages.', ['school_admin']),
            'communication.bulk' => $this->feature('Bulk communication', 'Communication', 'Send bulk SMS, email, or WhatsApp messages where configured.', ['school_admin']),
            'communication.logs.view' => $this->feature('Communication center', 'Communication', 'View communication dashboard, logs, and templates.', ['school_admin']),
            'communication.templates.manage' => $this->feature('Communication templates', 'Communication', 'Manage reusable communication templates.', ['school_admin']),
            'portal.conversations' => $this->feature('In-app messages', 'Communication', 'Use portal conversations between school, parents, students, and teachers.', $supportRoles),

            'teacher.reviews' => $this->feature('Teacher reviews', 'Feedback', 'Allow parents and students to submit teacher feedback for moderation.', ['school_admin', 'result_officer', 'parent', 'student']),

            'support.access' => $this->feature('Support portal', 'Support', 'Create, view, and reply to school support tickets.', $supportRoles),
            'support.direct_escalation' => $this->feature('Direct support escalation', 'Support', 'Allow non-admin users to route a support request directly to platform support.', []),
            'support.assign' => $this->feature('Support assignment', 'Support', 'Assign internal school support tickets.', ['school_admin']),
            'support.escalate' => $this->feature('Support escalation', 'Support', 'Escalate internal support tickets to platform support.', ['school_admin']),
            'support.routing.manage' => $this->feature('Support routing', 'Support', 'Manage internal support routing rules.', ['school_admin']),

            'finance.view' => $this->feature('Finance', 'Finance', 'View fees, invoices, payments, and finance summaries.', ['school_admin', 'accountant']),
            'finance.manage' => $this->feature('Finance management', 'Finance', 'Manage invoices, fees, payments, and finance settings.', ['school_admin', 'accountant']),

            'attendance.view' => $this->feature('Attendance', 'Attendance', 'View attendance records and dashboards.', ['school_admin', 'teacher', 'result_officer']),
            'attendance.manage' => $this->feature('Attendance capture', 'Attendance', 'Capture and update attendance for allowed classes.', ['school_admin', 'teacher']),
            'attendance.reports.view' => $this->feature('Attendance reports', 'Attendance', 'View attendance reports.', ['school_admin', 'teacher', 'result_officer']),
            'attendance.offline.sync' => $this->feature('Offline attendance sync', 'Attendance', 'Sync attendance captured offline.', ['school_admin', 'teacher']),
            'offline.attendance.sync' => $this->feature('Offline attendance', 'Attendance', 'Use offline attendance capture and sync tools.', ['school_admin', 'teacher']),

            'lms.view' => $this->feature('Learning materials', 'Learning', 'View LMS materials, classrooms, and learning tools.', ['school_admin', 'teacher', 'student']),
            'lms.manage' => $this->feature('LMS management', 'Learning', 'Manage learning materials and classrooms.', ['school_admin', 'teacher']),
            'lms.materials.manage' => $this->feature('LMS materials', 'Learning', 'Create and update learning materials.', ['school_admin', 'teacher']),
            'lms.assignments.post' => $this->feature('LMS assignments', 'Learning', 'Post assignments to learners.', ['school_admin', 'teacher']),
            'live_classes.view' => $this->feature('Live classes', 'Learning', 'View live class schedules and sessions.', ['school_admin', 'teacher', 'result_officer', 'parent', 'student']),
            'live_classes.create' => $this->feature('Schedule live classes', 'Learning', 'Create live classes for permitted class and subject scopes.', ['school_admin', 'teacher']),
            'live_classes.join' => $this->feature('Join live classes', 'Learning', 'Join live classes through resolved participant invitations.', ['school_admin', 'teacher', 'result_officer', 'accountant', 'parent', 'student']),
            'live_classes.manage' => $this->feature('Live class management', 'Learning', 'Create and manage live classes.', ['school_admin', 'teacher']),
            'live_classes.recordings.manage' => $this->feature('Live class recordings', 'Learning', 'Manage live class recordings.', ['school_admin', 'teacher']),
            'cbt.view' => $this->feature('CBT access', 'CBT', 'View CBT activities and assessments.', ['school_admin', 'teacher', 'result_officer', 'student']),
            'cbt.manage' => $this->feature('CBT management', 'CBT', 'Manage CBT setup, attempts, and assessments.', ['school_admin', 'teacher', 'result_officer']),
            'cbt.question_bank' => $this->feature('CBT question bank', 'CBT', 'Manage CBT question banks.', ['school_admin', 'teacher', 'result_officer']),
            'cbt.mark_theory' => $this->feature('CBT theory marking', 'CBT', 'Mark CBT theory responses.', ['school_admin', 'teacher', 'result_officer']),
            'cbt.publish_results' => $this->feature('CBT results', 'CBT', 'Publish CBT results.', ['school_admin', 'result_officer']),
            'cbt.public_competition' => $this->feature('CBT competition mode', 'CBT', 'Manage public CBT competition access.', ['school_admin']),
            'cbt.certificates' => $this->feature('CBT certificates', 'CBT', 'Generate CBT certificates.', ['school_admin', 'result_officer']),
        ];
    }

    public function groupedCatalog(): array
    {
        $groups = [];

        foreach ($this->catalog() as $key => $feature) {
            $groups[$feature['group']][$key] = $feature;
        }

        ksort($groups);

        return $groups;
    }

    public function getFeatures(?int $schoolId, ?string $roleName): array
    {
        $roleName = $this->normalizeRoleName($roleName);
        $features = [];

        foreach ($this->catalog() as $key => $feature) {
            $defaultEnabled = $this->roleSupports($roleName, $key);

            $features[$key] = [
                'key' => $key,
                'label' => $feature['label'],
                'group' => $feature['group'],
                'description' => $feature['description'],
                'enabled' => $this->isEnabled($schoolId, $roleName, $key),
                'default_enabled' => $defaultEnabled,
            ];
        }

        return $features;
    }

    public function getAvailableFeatures(?string $roleName = null): array
    {
        if (! $roleName) {
            return $this->catalog();
        }

        return collect($this->catalog())
            ->filter(fn (array $feature, string $key): bool => $this->roleSupports($roleName, $key))
            ->all();
    }

    public function enabledKeys(?int $schoolId, ?string $roleName): array
    {
        return collect($this->getFeatures($schoolId, $roleName))
            ->filter(fn (array $feature): bool => (bool) $feature['enabled'])
            ->keys()
            ->values()
            ->all();
    }

    public function isEnabled(?int $schoolId, ?string $roleName, string $featureKey): bool
    {
        $roleName = $this->normalizeRoleName($roleName);
        $featureKey = trim($featureKey);

        if ($featureKey === '' || $roleName === '') {
            return false;
        }

        $canonicalKey = $this->canonicalFeatureKey($featureKey);

        if ($schoolId && $this->settingsTableReady()) {
            $directSetting = $this->storedSetting($schoolId, $roleName, $this->directSettingKeys($featureKey));

            if ($directSetting !== null) {
                return $directSetting;
            }

            foreach (self::FEATURE_PREREQUISITES[$canonicalKey] ?? [] as $requiredKey) {
                $requiredSetting = $this->storedSetting($schoolId, $roleName, $this->directSettingKeys($requiredKey));

                if ($requiredSetting === false) {
                    return false;
                }
            }
        }

        return $this->roleSupports($roleName, $canonicalKey);
    }

    public function allows(?int $schoolId, ?string $roleName, string $featureKey): bool
    {
        return $this->isEnabled($schoolId, $roleName, $featureKey);
    }

    public function updateFeatures(int $schoolId, string $roleName, array $enabledKeys, ?int $actorId = null): void
    {
        if (! $this->settingsTableReady()) {
            return;
        }

        $roleName = $this->normalizeRoleName($roleName);
        $enabledKeys = collect($enabledKeys)
            ->map(fn ($featureKey) => $this->canonicalFeatureKey((string) $featureKey))
            ->unique()
            ->values()
            ->all();

        foreach ($this->catalog() as $featureKey => $feature) {
            SchoolFeatureSetting::query()->updateOrCreate([
                'school_id' => $schoolId,
                'role_name' => $roleName,
                'feature_key' => $featureKey,
            ], [
                'enabled' => in_array($featureKey, $enabledKeys, true),
                'updated_by' => $actorId,
                'metadata' => [
                    'source' => 'role_feature_control',
                ],
            ]);
        }

        $this->clearCache();
    }

    public function setFeature(...$arguments): bool
    {
        [$schoolId, $roleNames, $featureKey, $enabled, $actorId] = $this->parseSetFeatureArguments($arguments);

        if (! $schoolId || $featureKey === '' || ! $this->settingsTableReady()) {
            return false;
        }

        $featureKey = $this->canonicalFeatureKey($featureKey);
        $roleNames = $roleNames !== []
            ? array_values(array_unique(array_map(fn ($role) => $this->normalizeRoleName($role), $roleNames)))
            : $this->roleNames();

        foreach ($roleNames as $roleName) {
            if ($roleName === '') {
                continue;
            }

            SchoolFeatureSetting::query()->updateOrCreate([
                'school_id' => $schoolId,
                'role_name' => $roleName,
                'feature_key' => $featureKey,
            ], [
                'enabled' => $enabled,
                'updated_by' => $actorId,
                'metadata' => [
                    'source' => 'set_feature',
                ],
            ]);
        }

        $this->clearCache();

        return true;
    }

    public function enabled(...$arguments): bool
    {
        [$schoolId, $roleName, $featureKey] = $this->parseEnabledArguments($arguments);

        return $this->isEnabled($schoolId, $roleName, $featureKey);
    }

    public function roleSupports(?string $roleName, string $featureKey): bool
    {
        $roleName = $this->normalizeRoleName($roleName);

        if ($roleName === '') {
            return false;
        }

        if ($roleName === 'super_admin') {
            return true;
        }

        $featureKey = $this->canonicalFeatureKey($featureKey);
        $catalog = $this->catalog();

        if (isset($catalog[$featureKey])) {
            return in_array($roleName, $catalog[$featureKey]['defaults'] ?? [], true);
        }

        return in_array($roleName, $this->legacyDefaultsFor($featureKey), true);
    }

    private function feature(string $label, string $group, string $description, array $defaults): array
    {
        return compact('label', 'group', 'description', 'defaults');
    }

    private function parseSetFeatureArguments(array $arguments): array
    {
        $schoolId = null;
        $roleNames = [];
        $featureKey = '';
        $enabled = true;
        $actorId = null;
        $strings = [];

        foreach ($arguments as $argument) {
            if (is_object($argument) && method_exists($argument, 'getKey')) {
                $schoolId ??= (int) $argument->getKey();
                continue;
            }

            if (is_object($argument) && property_exists($argument, 'id')) {
                $schoolId ??= (int) $argument->id;
                continue;
            }

            if (is_int($argument)) {
                if ($schoolId === null) {
                    $schoolId = $argument;
                } else {
                    $actorId = $argument;
                }

                continue;
            }

            if (is_bool($argument)) {
                $enabled = $argument;
                continue;
            }

            if (is_array($argument)) {
                $roleNames = array_values(array_filter($argument, fn ($item): bool => is_string($item) && trim($item) !== ''));
                continue;
            }

            if (is_string($argument) && trim($argument) !== '') {
                $strings[] = trim($argument);
            }
        }

        if (count($strings) >= 2) {
            if ($this->isKnownRole($strings[0])) {
                $roleNames = [$strings[0]];
                $featureKey = $strings[1];
            } elseif ($this->isKnownRole($strings[1])) {
                $featureKey = $strings[0];
                $roleNames = [$strings[1]];
            } else {
                $featureKey = end($strings) ?: '';
            }
        } elseif (count($strings) === 1) {
            $featureKey = $strings[0];
        }

        return [$schoolId, $roleNames, $featureKey, $enabled, $actorId];
    }

    private function parseEnabledArguments(array $arguments): array
    {
        $schoolId = null;
        $roleName = null;
        $featureKey = '';
        $strings = [];

        foreach ($arguments as $argument) {
            if (is_int($argument) && $schoolId === null) {
                $schoolId = $argument;
                continue;
            }

            if (is_object($argument) && method_exists($argument, 'getKey')) {
                $schoolId ??= (int) $argument->getKey();
                continue;
            }

            if (is_object($argument) && property_exists($argument, 'id')) {
                $schoolId ??= (int) $argument->id;
                continue;
            }

            if (is_string($argument) && trim($argument) !== '') {
                $strings[] = trim($argument);
            }
        }

        if (count($strings) >= 2 && $this->isKnownRole($strings[0])) {
            $roleName = $strings[0];
            $featureKey = end($strings) ?: '';
        } elseif (count($strings) >= 2 && $this->isKnownRole($strings[1])) {
            $featureKey = $strings[0];
            $roleName = $strings[1];
        } elseif (count($strings) >= 1) {
            $featureKey = end($strings) ?: '';
        }

        if (! $roleName) {
            $roleName = app(CurrentSchoolService::class)->roleContext(auth()->user());
        }

        return [$schoolId, $roleName, $featureKey];
    }

    private function directSettingKeys(string $featureKey): array
    {
        $canonicalKey = $this->canonicalFeatureKey($featureKey);
        $keys = [$canonicalKey, trim($featureKey)];

        foreach (self::FEATURE_ALIASES as $alias => $target) {
            if ($target === $canonicalKey) {
                $keys[] = $alias;
            }
        }

        return array_values(array_unique(array_filter($keys)));
    }

    public function canonicalFeatureKey(string $featureKey): string
    {
        $featureKey = trim($featureKey);

        return self::FEATURE_ALIASES[$featureKey] ?? $featureKey;
    }

    private function storedSetting(int $schoolId, string $roleName, array $featureKeys): ?bool
    {
        $featureKeys = array_values(array_unique(array_filter($featureKeys)));

        if ($featureKeys === []) {
            return null;
        }

        $cacheKey = $schoolId.':'.$roleName.':'.implode('|', $featureKeys);

        if (array_key_exists($cacheKey, $this->settingCache)) {
            return $this->settingCache[$cacheKey];
        }

        try {
            $settings = SchoolFeatureSetting::query()
                ->where('school_id', $schoolId)
                ->where('role_name', $roleName)
                ->whereIn('feature_key', $featureKeys)
                ->get()
                ->keyBy('feature_key');

            foreach ($featureKeys as $featureKey) {
                if ($settings->has($featureKey)) {
                    return $this->settingCache[$cacheKey] = (bool) $settings[$featureKey]->enabled;
                }
            }
        } catch (Throwable) {
            return $this->settingCache[$cacheKey] = null;
        }

        return $this->settingCache[$cacheKey] = null;
    }

    private function legacyDefaultsFor(string $featureKey): array
    {
        $prefixes = [
            'school.' => ['school_admin'],
            'branding.' => ['school_admin'],
            'staff.' => ['school_admin'],
            'users.' => ['school_admin'],
            'roles.' => ['school_admin'],
            'feature.' => ['school_admin'],
            'sessions.' => ['school_admin'],
            'terms.' => ['school_admin'],
            'admission.' => ['school_admin'],
            'students.' => ['school_admin', 'teacher', 'result_officer'],
            'teacher.results.' => ['school_admin', 'teacher', 'result_officer'],
            'teacher.assignments.' => ['teacher'],
            'teacher.assignment.' => ['school_admin'],
            'attendance.' => ['school_admin', 'teacher', 'result_officer'],
            'offline.attendance.' => ['school_admin', 'teacher'],
            'support.' => self::SUPPORT_ROLES,
            'reports.' => ['school_admin', 'result_officer'],
            'results.' => ['school_admin', 'result_officer'],
            'result.' => ['school_admin', 'result_officer'],
            'scratch.' => ['school_admin', 'result_officer'],
            'communication.' => ['school_admin'],
            'communications.' => ['school_admin'],
            'finance.' => ['school_admin', 'accountant'],
            'lms.' => ['school_admin', 'teacher', 'student'],
            'cbt.' => ['school_admin', 'teacher', 'result_officer', 'student'],
            'live_class.' => ['school_admin', 'teacher', 'result_officer', 'parent', 'student'],
            'live-classes.' => ['school_admin', 'teacher', 'result_officer', 'parent', 'student'],
            'live_classes.' => ['school_admin', 'teacher', 'result_officer', 'parent', 'student'],
            'portal.' => self::SUPPORT_ROLES,
        ];

        foreach ($prefixes as $prefix => $roles) {
            if (str_starts_with($featureKey, $prefix)) {
                return $roles;
            }
        }

        return ['school_admin'];
    }

    private function normalizeRoleName(?string $roleName): string
    {
        return trim((string) $roleName);
    }

    private function isKnownRole(string $roleName): bool
    {
        return in_array($this->normalizeRoleName($roleName), array_merge(self::SCHOOL_ROLES, ['super_admin']), true);
    }

    private function settingsTableReady(): bool
    {
        try {
            return Schema::hasTable('school_feature_settings');
        } catch (Throwable) {
            return false;
        }
    }

    private function clearCache(): void
    {
        $this->settingCache = [];
    }
}
