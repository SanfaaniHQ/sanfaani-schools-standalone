<?php

namespace App\Services\Standalone;

use App\Models\Backup;
use App\Models\School;
use App\Models\UpdatePackage;
use App\Services\Backups\BackupService;
use App\Services\Licensing\LicenseValidationService;
use Illuminate\Support\Facades\Route;

class StandaloneDashboardSummaryService
{
    public function __construct(
        private StandaloneEditionService $edition,
        private StandaloneSyncService $sync,
        private StandaloneSystemHealthService $health,
        private LicenseValidationService $licenses,
        private BackupService $backups,
    ) {}

    public function forOwner(?School $school = null): array
    {
        $school ??= School::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->first()
            ?? School::query()->orderBy('id')->first();

        $schoolSummary = $school
            ? $this->forSchool($school, true)
            : $this->emptySchoolSummary();

        $editionStatus = $this->edition->status();
        $syncStatus = $this->sync->status();
        $offlineAttendanceHealth = $school
            ? $this->sync->offlineAttendanceSyncHealth($school)
            : $this->sync->offlineAttendanceSyncHealth();
        $healthSummary = $this->health->summary($school);
        $licenseStatus = $this->licenses->status($school);
        $licenseReady = $this->licenseReady($licenseStatus);
        $latestBackup = $this->latestBackup($school);
        $backupReadiness = $this->backups->preUpdateReadiness($school);
        $latestUpdate = UpdatePackage::query()->latest('id')->first();
        $warnings = collect($editionStatus['warnings']);

        if (! $school) {
            $warnings->push('No school workspace has been created yet.');
        }

        if ($healthSummary['overall']['status'] !== 'pass') {
            $warnings->push('Standalone system health: '.$healthSummary['overall']['message']);
        }

        if (! $licenseReady) {
            $warnings->push('The standalone license needs attention.');
        }

        if (! $backupReadiness['ready']) {
            $warnings->push($backupReadiness['message']);
        }

        if (($syncStatus['failed_count'] ?? 0) > 0) {
            $warnings->push($syncStatus['failed_count'].' local sync item(s) need review.');
        }

        if (($offlineAttendanceHealth['conflict_count'] + $offlineAttendanceHealth['failed_validation_count'] + $offlineAttendanceHealth['failed_permission_count']) > 0) {
            $warnings->push('Offline attendance sync has server-known conflicts or failed attempts to review.');
        }

        $workspaceHref = $this->route('workspace.create');
        $schoolChecklist = collect($schoolSummary['checklist'])
            ->map(function (array $item) use ($workspaceHref): array {
                if (! in_array($item['key'], ['backup', 'license', 'system_health'], true)) {
                    $item['href'] = $workspaceHref;
                }

                return $item;
            })
            ->all();

        $ownerChecklist = [
            $this->checklistItem(
                'installation',
                'Installation completed',
                (bool) $editionStatus['installed'],
                $editionStatus['installed'] ? 'Installer lock or installed configuration is present.' : 'Complete the guided installer before handover.',
                $editionStatus['installed'] ? null : $this->route('installer.welcome'),
            ),
            $this->checklistItem(
                'school_workspace',
                'School workspace created',
                (bool) $school,
                $school ? $school->name : 'Create the first school and assign its administrator.',
                $workspaceHref,
            ),
            ...$schoolChecklist,
        ];

        return [
            'context' => 'owner',
            'school_name' => $school?->name,
            'primary_action' => [
                'label' => $school ? 'Open school workspace' : 'Choose school workspace',
                'href' => $this->route('workspace.create'),
            ],
            'progress' => $this->progress($ownerChecklist),
            'checklist' => $ownerChecklist,
            'health' => [
                [
                    'label' => 'Installation',
                    'value' => $editionStatus['installed'] ? 'Installed' : 'Setup pending',
                    'meta' => $editionStatus['installer_enabled'] ? 'Guided installer enabled' : 'Installer disabled',
                    'tone' => $editionStatus['installed'] ? 'success' : 'warning',
                    'href' => $this->route('admin.standalone.status'),
                ],
                [
                    'label' => 'License',
                    'value' => $this->label($licenseStatus),
                    'meta' => $this->licenseMeta($school),
                    'tone' => $licenseReady ? 'success' : 'warning',
                    'href' => $this->route('admin.license.index'),
                ],
                [
                    'label' => 'Backup readiness',
                    'value' => $backupReadiness['ready'] ? 'Ready' : 'Action needed',
                    'meta' => $latestBackup
                        ? 'Latest: '.$this->label($latestBackup->status)
                        : 'No backup metadata recorded',
                    'tone' => $backupReadiness['ready'] ? 'success' : 'warning',
                    'href' => $this->route('admin.backups.index'),
                ],
                [
                    'label' => 'Guided updates',
                    'value' => $latestUpdate ? $this->label($latestUpdate->status) : 'No package',
                    'meta' => $latestUpdate
                        ? 'Package '.$latestUpdate->version
                        : 'Current version '.(string) config('version.version', '1.0.0'),
                    'tone' => $latestUpdate && $latestUpdate->status === UpdatePackage::STATUS_PRECHECK_BLOCKED ? 'warning' : 'neutral',
                    'href' => $this->route('admin.updates.index'),
                ],
                [
                    'label' => 'Local-first sync',
                    'value' => $syncStatus['enabled'] ? 'Configured' : 'Optional',
                    'meta' => $syncStatus['enabled']
                        ? (($syncStatus['pending_count'] ?? 0).' pending / '.($syncStatus['failed_count'] ?? 0).' failed')
                        : 'Local database remains the source of truth',
                    'tone' => ($syncStatus['failed_count'] ?? 0) > 0 ? 'warning' : 'info',
                    'href' => $this->route('admin.standalone.status'),
                ],
                [
                    'label' => 'Offline attendance sync',
                    'value' => $offlineAttendanceHealth['receipt_total'].' receipts',
                    'meta' => $offlineAttendanceHealth['synced_count'].' synced / '.($offlineAttendanceHealth['conflict_count'] + $offlineAttendanceHealth['failed_validation_count'] + $offlineAttendanceHealth['failed_permission_count']).' needs review',
                    'tone' => ($offlineAttendanceHealth['conflict_count'] + $offlineAttendanceHealth['failed_validation_count'] + $offlineAttendanceHealth['failed_permission_count']) > 0 ? 'warning' : 'info',
                    'href' => $this->route('admin.standalone.status'),
                ],
                [
                    'label' => 'System health',
                    'value' => $healthSummary['overall']['label'],
                    'meta' => $healthSummary['overall']['message'],
                    'tone' => $healthSummary['overall']['tone'],
                    'href' => $this->route('admin.standalone.status'),
                ],
            ],
            'operations' => $this->ownerOperations($school, $schoolSummary['operations']),
            'planned' => $this->plannedModules(),
            'warnings' => $warnings->unique()->values()->all(),
            'offline_statement' => $this->offlineStatement(),
        ];
    }

    public function forSchool(School $school, bool $ownerContext = false): array
    {
        $activeSession = $school->academicSessions()->where('is_active', true)->first();
        $activeTerm = $school->terms()->where('is_active', true)->first();
        $openAdmissionCycle = $school->admissionCycles()->acceptingApplications()->first();
        $licenseStatus = $this->licenses->status($school);
        $licenseReady = $this->licenseReady($licenseStatus);
        $backupReadiness = $this->backups->preUpdateReadiness($school);
        $healthSummary = $this->health->summary($school);
        $offlineAttendanceHealth = $this->sync->offlineAttendanceSyncHealth($school);
        $systemWarnings = $healthSummary['overall']['status'] === 'pass'
            ? []
            : ['Standalone system health: '.$healthSummary['overall']['message']];

        if (($offlineAttendanceHealth['conflict_count'] + $offlineAttendanceHealth['failed_validation_count'] + $offlineAttendanceHealth['failed_permission_count']) > 0) {
            $systemWarnings[] = 'Offline attendance sync has server-known conflicts or failed attempts to review.';
        }

        $counts = [
            'users' => $school->users()->count(),
            'classes' => $school->schoolClasses()->count(),
            'subjects' => $school->subjects()->count(),
            'students' => $school->students()->count(),
            'admission_cycles' => $school->admissionCycles()->count(),
            'admission_applications' => $school->admissionApplications()->count(),
            'attendance_records' => $school->attendanceRecords()->count(),
            'offline_attendance_receipts' => $offlineAttendanceHealth['receipt_total'],
            'results' => $school->studentResults()->count(),
            'published_results' => $school->studentResults()->where('status', 'published')->count(),
            'cbt_question_banks' => $school->cbtQuestionBanks()->count(),
            'cbt_exams' => $school->cbtExams()->count(),
        ];

        $profileReady = filled($school->name)
            && filled($school->email)
            && filled($school->phone)
            && filled($school->address);
        $brandingReady = (filled($school->logo_path ?: $school->logo) && filled($school->primary_color))
            || $school->activeBrandingSetting()->exists();
        $resultSettingsReady = $school->reportCardSetting()->exists()
            || $school->resultAccessPolicies()->exists();

        $checklist = [
            $this->checklistItem('school_profile', 'School profile and contact details', $profileReady, $profileReady ? 'Core identity and contact fields are complete.' : 'Add email, phone, and address.', $this->route('school.profile.edit')),
            $this->checklistItem('branding', 'Branding and logo', $brandingReady, $brandingReady ? 'School branding is configured.' : 'Add a logo and primary color.', $this->route('school.branding.edit')),
            $this->checklistItem('active_session', 'Active academic session', (bool) $activeSession, $activeSession?->name ?? 'No active session selected.', $this->route('school.sessions.index')),
            $this->checklistItem('active_term', 'Active academic term', (bool) $activeTerm, $activeTerm?->name ?? 'No active term selected.', $this->route('school.terms.index')),
            $this->checklistItem('classes', 'Classes configured', $counts['classes'] > 0, $counts['classes'].' class(es) available.', $this->route('school.classes.index')),
            $this->checklistItem('subjects', 'Subjects configured', $counts['subjects'] > 0, $counts['subjects'].' subject(s) available.', $this->route('school.subjects.index')),
            $this->checklistItem('staff', 'Staff and role accounts', $counts['users'] > 0, $counts['users'].' user account(s) in school scope.', $this->route('school.staff.index')),
            $this->checklistItem('students', 'Student records', $counts['students'] > 0, $counts['students'].' student record(s).', $this->route('school.students.index')),
            $this->checklistItem(
                'attendance_foundation',
                'Attendance foundation',
                Route::has('school.attendance.index'),
                $this->edition->offlineAttendanceSyncEnabled()
                    ? 'Online attendance, the attendance-only browser offline capture pilot, and server-side sync monitor are available.'
                    : 'Online attendance is available; the attendance-only offline pilot is disabled by default.',
                $this->route('school.attendance.index')
            ),
            $this->checklistItem('admissions', 'Admissions cycle', $counts['admission_cycles'] > 0, $openAdmissionCycle ? $openAdmissionCycle->name.' is accepting applications.' : ($counts['admission_cycles'].' cycle(s), none currently open.'), $this->route('admin.admissions.index')),
            $this->checklistItem('result_settings', 'Result and report settings', $resultSettingsReady, $resultSettingsReady ? 'Report or access settings are configured.' : 'Configure report cards or result access rules.', $this->route('school.report-card-settings.edit')),
            $this->checklistItem('cbt', 'CBT setup', $counts['cbt_question_banks'] > 0 || $counts['cbt_exams'] > 0, $counts['cbt_question_banks'].' bank(s), '.$counts['cbt_exams'].' exam(s).', $this->route('school.cbt.dashboard')),
            $this->checklistItem('backup', 'Recent verified backup', (bool) $backupReadiness['ready'], $backupReadiness['message'], $ownerContext ? $this->route('admin.backups.index') : null),
            $this->checklistItem('license', 'Standalone license', $licenseReady, 'Status: '.$this->label($licenseStatus).'.', $ownerContext ? $this->route('admin.license.index') : null),
            $this->checklistItem('system_health', 'Standalone system health', $healthSummary['overall']['status'] === 'pass', $healthSummary['overall']['message'], $ownerContext ? $this->route('admin.standalone.status') : null),
        ];

        return [
            'context' => 'school',
            'school_name' => $school->name,
            'primary_action' => [
                'label' => 'Open setup guide',
                'href' => $this->route('onboarding.index'),
            ],
            'progress' => $this->progress($checklist),
            'checklist' => $checklist,
            'health' => [
                [
                    'label' => 'License',
                    'value' => $this->label($licenseStatus),
                    'meta' => $this->licenseMeta($school),
                    'tone' => $licenseReady ? 'success' : 'warning',
                    'href' => null,
                ],
                [
                    'label' => 'Backup readiness',
                    'value' => $backupReadiness['ready'] ? 'Ready' : 'Action needed',
                    'meta' => $backupReadiness['message'],
                    'tone' => $backupReadiness['ready'] ? 'success' : 'warning',
                    'href' => null,
                ],
                [
                    'label' => 'Local-first operation',
                    'value' => $this->edition->localFirstOfflineEnabled() ? 'Enabled' : 'Review',
                    'meta' => 'School server and local database remain authoritative',
                    'tone' => $this->edition->localFirstOfflineEnabled() ? 'info' : 'warning',
                    'href' => null,
                ],
                [
                    'label' => 'Offline attendance sync',
                    'value' => $offlineAttendanceHealth['receipt_total'].' receipts',
                    'meta' => $offlineAttendanceHealth['synced_count'].' synced / '.($offlineAttendanceHealth['conflict_count'] + $offlineAttendanceHealth['failed_validation_count'] + $offlineAttendanceHealth['failed_permission_count']).' needs review',
                    'tone' => ($offlineAttendanceHealth['conflict_count'] + $offlineAttendanceHealth['failed_validation_count'] + $offlineAttendanceHealth['failed_permission_count']) > 0 ? 'warning' : 'info',
                    'href' => $this->route('school.attendance.offline-sync-monitor'),
                ],
                [
                    'label' => 'System health',
                    'value' => $healthSummary['overall']['label'],
                    'meta' => $healthSummary['overall']['message'],
                    'tone' => $healthSummary['overall']['tone'],
                    'href' => null,
                ],
            ],
            'operations' => [
                [
                    'label' => 'Admissions',
                    'value' => $counts['admission_applications'],
                    'meta' => $openAdmissionCycle ? 'Applications open: '.$openAdmissionCycle->name : 'No open admission cycle',
                    'href' => $this->route('admin.admissions.index'),
                ],
                [
                    'label' => 'Attendance',
                    'value' => $counts['attendance_records'],
                    'meta' => $counts['offline_attendance_receipts'].' offline sync receipt(s)',
                    'href' => $this->route('school.attendance.offline-sync-monitor') ?? $this->route('school.attendance.index'),
                ],
                [
                    'label' => 'Results',
                    'value' => $counts['results'],
                    'meta' => $counts['published_results'].' published result(s)',
                    'href' => $this->route('school.result-system.index'),
                ],
                [
                    'label' => 'CBT',
                    'value' => $counts['cbt_exams'],
                    'meta' => $counts['cbt_question_banks'].' question bank(s)',
                    'href' => $this->route('school.cbt.dashboard'),
                ],
            ],
            'planned' => $this->plannedModules(),
            'warnings' => $systemWarnings,
            'offline_statement' => $this->offlineStatement(),
        ];
    }

    private function emptySchoolSummary(): array
    {
        return [
            'checklist' => [],
            'operations' => [
                ['label' => 'Admissions', 'value' => 0, 'meta' => 'Create the school workspace first', 'href' => $this->route('workspace.create')],
                ['label' => 'Attendance', 'value' => 0, 'meta' => 'Create the school workspace first', 'href' => $this->route('workspace.create')],
                ['label' => 'Results', 'value' => 0, 'meta' => 'Create the school workspace first', 'href' => $this->route('workspace.create')],
                ['label' => 'CBT', 'value' => 0, 'meta' => 'Create the school workspace first', 'href' => $this->route('workspace.create')],
            ],
        ];
    }

    private function ownerOperations(?School $school, array $moduleOperations): array
    {
        $workspaceHref = $this->route('workspace.create');

        if (! $school) {
            return collect(['Students', 'Classes', 'Subjects', 'Sessions and terms', 'Admissions', 'Attendance', 'Results', 'CBT'])
                ->map(fn (string $label): array => [
                    'label' => $label,
                    'value' => 0,
                    'meta' => 'Create the school workspace first',
                    'href' => $workspaceHref,
                ])
                ->all();
        }

        return [
            [
                'label' => 'Students',
                'value' => $school->students()->count(),
                'meta' => 'Student records in the school workspace',
                'href' => $workspaceHref,
            ],
            [
                'label' => 'Classes',
                'value' => $school->schoolClasses()->count(),
                'meta' => 'Class arms and academic groups',
                'href' => $workspaceHref,
            ],
            [
                'label' => 'Subjects',
                'value' => $school->subjects()->count(),
                'meta' => 'Subjects available for assignment',
                'href' => $workspaceHref,
            ],
            [
                'label' => 'Sessions and terms',
                'value' => $school->academicSessions()->count(),
                'meta' => $school->terms()->count().' term record(s)',
                'href' => $workspaceHref,
            ],
            ...collect($moduleOperations)
                ->map(function (array $item) use ($workspaceHref): array {
                    $item['href'] = $workspaceHref;

                    return $item;
                })
                ->all(),
        ];
    }

    private function latestBackup(?School $school): ?Backup
    {
        return Backup::query()
            ->when($school, fn ($query) => $query->where('school_id', $school->id))
            ->when(! $school, fn ($query) => $query->whereNull('school_id'))
            ->latest('id')
            ->first();
    }

    private function licenseReady(string $status): bool
    {
        return in_array($status, ['valid', 'offline_grace', 'validation_disabled', 'subscription_platform'], true);
    }

    private function licenseMeta(?School $school): string
    {
        $license = $this->licenses->current($school);

        if (! $license) {
            return $this->licenses->requiresValidation()
                ? 'No local license found'
                : 'Validation disabled by configuration';
        }

        $days = $this->licenses->daysUntilExpiry($license);

        return $days === null
            ? $this->label($license->license_type).' license'
            : max(0, $days).' day(s) until expiry';
    }

    private function checklistItem(string $key, string $label, bool $complete, string $detail, ?string $href): array
    {
        return compact('key', 'label', 'complete', 'detail', 'href');
    }

    private function progress(array $items): array
    {
        $total = count($items);
        $done = collect($items)->where('complete', true)->count();

        return [
            'done' => $done,
            'total' => $total,
            'percent' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
        ];
    }

    private function plannedModules(): array
    {
        $offlineAttendanceEnabled = $this->edition->offlineAttendanceSyncEnabled();

        return [
            [
                'label' => 'Offline attendance capture',
                'status' => $offlineAttendanceEnabled ? 'Available' : 'Disabled',
                'detail' => $offlineAttendanceEnabled
                    ? 'Attendance-only browser capture and validated sync are enabled.'
                    : 'The attendance-only browser pilot is available only when capture and sync are enabled.',
            ],
            ['label' => 'LMS and learning content', 'status' => 'Planned', 'detail' => 'Course delivery and content workflows remain future work.'],
            ['label' => 'Live classes', 'status' => 'Planned', 'detail' => 'No live-class provider is presented as available.'],
            ['label' => 'Full fees and accounting', 'status' => 'Planned', 'detail' => 'Current payments are limited to existing admissions and scratch-card workflows.'],
            ['label' => 'Full browser offline/PWA', 'status' => 'Not implemented', 'detail' => 'Local-first server operation is available; the attendance pilot does not make the full portal work offline.'],
        ];
    }

    private function offlineStatement(): string
    {
        $attendance = $this->edition->offlineAttendanceSyncEnabled()
            ? 'The attendance-only browser offline capture pilot is enabled.'
            : 'The attendance-only browser offline capture pilot is disabled by default.';

        return 'Local-first means the school server and hosted Laravel database remain the source of truth. '
            .$attendance
            .' Full portal offline mode is not implemented, and the server cannot see browser-local pending attendance until it syncs.';
    }

    private function route(string $name): ?string
    {
        return Route::has($name) ? route($name) : null;
    }

    private function label(string $value): string
    {
        return str($value)->replace('_', ' ')->title()->toString();
    }
}
