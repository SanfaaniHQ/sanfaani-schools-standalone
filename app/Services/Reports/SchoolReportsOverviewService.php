<?php

namespace App\Services\Reports;

use App\Models\Admissions\AdmissionApplication;
use App\Models\Backup;
use App\Models\CbtAttempt;
use App\Models\CbtExam;
use App\Models\CbtResultPublication;
use App\Models\LiveClass;
use App\Models\LmsCbtActivity;
use App\Models\LmsClassroom;
use App\Models\LmsMaterial;
use App\Models\School;
use App\Models\SchoolNotificationLog;
use App\Models\SchoolNotificationTemplate;
use App\Models\Student;
use App\Models\StudentAttendanceRecord;
use App\Models\StudentFeeInvoice;
use App\Models\UpdatePackage;
use App\Services\Finance\SchoolFinanceReportService;
use App\Services\Standalone\StandaloneSyncService;
use App\Services\Standalone\StandaloneSystemHealthService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;

class SchoolReportsOverviewService
{
    private const STUDENT_STATUSES = ['active', 'inactive', 'graduated', 'transferred', 'withdrawn'];

    public function __construct(
        private SchoolFinanceReportService $financeReports,
        private StandaloneSyncService $sync,
        private StandaloneSystemHealthService $health,
    ) {}

    public function overview(School $school, array $filters = []): array
    {
        return [
            'filters' => $filters,
            'status_options' => $this->statusOptions(),
            'summary_cards' => $this->summaryCards($school, $filters),
            'groups' => [
                $this->populationGroup($school, $filters),
                $this->admissionsGroup($school, $filters),
                $this->attendanceGroup($school, $filters),
                $this->financeGroup($school, $filters),
                $this->learningGroup($school, $filters),
                $this->liveClassesGroup($school, $filters),
                $this->communicationsGroup($school, $filters),
                $this->offlineOperationsGroup($school, $filters),
            ],
            'export_links' => $this->exportLinks($filters),
            'privacy_notes' => $this->privacyNotes(),
        ];
    }

    public function filterOptions(School $school): array
    {
        return [
            'classes' => $school->schoolClasses()
                ->where('status', 'active')
                ->orderBy('name')
                ->orderBy('section')
                ->get(['id', 'name', 'section']),
            'academicSessions' => $school->academicSessions()
                ->where('status', 'active')
                ->latest()
                ->get(['id', 'name']),
            'terms' => $school->terms()
                ->where('status', 'active')
                ->with('academicSession:id,name')
                ->latest()
                ->get(['id', 'academic_session_id', 'name']),
            'statuses' => $this->statusOptions(),
        ];
    }

    private function summaryCards(School $school, array $filters): array
    {
        $finance = $this->financeSummary($school, $filters);
        $attendance = $this->attendanceSummary($school, $filters);
        $offline = $this->sync->offlineAttendanceSyncHealth($school->id, $this->offlineFilters($filters));

        return [
            $this->card('Students', $this->countStudents($school, $filters), 'School-scoped student records', 'neutral', $this->route('school.students.index')),
            $this->card('Attendance Records', $attendance['total'], $attendance['attendance_percentage'].'% attendance score', 'info', $this->route('school.attendance.reports', $this->attendanceRouteFilters($filters))),
            $this->card('Finance Outstanding', 'NGN '.number_format($finance['summary']['total_outstanding'], 2), $finance['summary']['invoices'].' invoice(s)', 'warning', $this->route('school.finance.reports', $this->financeRouteFilters($filters))),
            $this->card('Learning Links', $this->learningLinkCount($school, $filters), 'LMS classrooms plus active CBT links', 'info', $this->route('school.lms.index')),
            $this->card('Communications', $this->notificationLogCount($school, $filters), 'Operational notification log summaries', 'neutral', $this->route('school.communications.index')),
            $this->card('Offline Sync', $offline['receipt_total'], $offline['synced_count'].' synced / '.($offline['conflict_count'] + $offline['failed_validation_count'] + $offline['failed_permission_count']).' needs review', $this->needsReviewTone($offline), $this->route('school.attendance.offline-sync-monitor', $this->offlineRouteFilters($filters))),
        ];
    }

    private function populationGroup(School $school, array $filters): array
    {
        $studentQuery = $this->studentQuery($school, $filters);
        $classQuery = $school->schoolClasses()
            ->when(filled($filters['school_class_id'] ?? null), fn (Builder $query) => $query->whereKey((int) $filters['school_class_id']))
            ->when($this->statusMatches($filters, ['active', 'inactive', 'archived']), fn (Builder $query) => $query->where('status', $filters['status']));

        return $this->group(
            'population',
            'Students and Classes',
            'Population counts reuse the existing student, class, subject, session, and term records.',
            [
                $this->card('Total Students', (clone $studentQuery)->count(), 'Filtered school records', 'neutral', $this->route('school.students.index')),
                $this->card('Active Students', (clone $studentQuery)->where('status', 'active')->count(), 'Currently active learners', 'success'),
                $this->card('Classes', (clone $classQuery)->count(), 'Class arms in scope', 'neutral', $this->route('school.classes.index')),
                $this->card('Subjects', $school->subjects()->count(), 'Subject catalog records', 'neutral', $this->route('school.subjects.index')),
            ],
            [
                $this->link('Students', 'Student records and Student 360 profiles.', $this->route('school.students.index')),
                $this->link('Classes', 'Class arms and academic groups.', $this->route('school.classes.index')),
                $this->link('Subjects', 'Subject catalog and assignments.', $this->route('school.subjects.index')),
            ]
        );
    }

    private function admissionsGroup(School $school, array $filters): array
    {
        $query = AdmissionApplication::query()
            ->where('school_id', $school->id)
            ->when(filled($filters['school_class_id'] ?? null), fn (Builder $query) => $query->where('requested_class_id', (int) $filters['school_class_id']))
            ->when($this->statusMatches($filters, AdmissionApplication::STATUSES), fn (Builder $query) => $query->where('status', $filters['status']));

        $this->applyDateRange($query, 'submitted_at', $filters);

        $pendingStatuses = [
            AdmissionApplication::STATUS_SUBMITTED,
            AdmissionApplication::STATUS_UNDER_REVIEW,
            AdmissionApplication::STATUS_MISSING_DOCUMENTS,
            AdmissionApplication::STATUS_ENTRANCE_EXAM_SCHEDULED,
            AdmissionApplication::STATUS_INTERVIEW_SCHEDULED,
            AdmissionApplication::STATUS_WAITLISTED,
            AdmissionApplication::STATUS_PAYMENT_PENDING,
        ];

        return $this->group(
            'admissions',
            'Admissions',
            'Admissions are summarized without exposing applicant documents, tracking tokens, notes, or payment payloads.',
            [
                $this->card('Applicants', (clone $query)->count(), 'Submitted application records', 'neutral', $this->route('admin.admissions.applications.index')),
                $this->card('Pending Review', (clone $query)->whereIn('status', $pendingStatuses)->count(), 'Needs admissions action', 'warning'),
                $this->card('Admitted / Converted', (clone $query)->whereIn('status', [AdmissionApplication::STATUS_ADMITTED, AdmissionApplication::STATUS_CONVERTED])->count(), 'Accepted outcomes', 'success'),
                $this->card('Rejected', (clone $query)->where('status', AdmissionApplication::STATUS_REJECTED)->count(), 'Closed unsuccessful outcomes', 'neutral'),
            ],
            [
                $this->link('Admissions Dashboard', 'Existing school-scoped admissions workflow.', $this->route('admin.admissions.index')),
                $this->link('Applications', 'Application list, review, and conversion tools.', $this->route('admin.admissions.applications.index')),
            ]
        );
    }

    private function attendanceGroup(School $school, array $filters): array
    {
        $summary = $this->attendanceSummary($school, $filters);
        $missingMeta = $summary['missing'] === null
            ? 'Missing count appears only for one class on one day'
            : $summary['missing'].' unmarked for selected class/date';

        return $this->group(
            'attendance',
            'Attendance',
            'Attendance reports reuse existing attendance records, status counts, date ranges, and offline sync boundaries.',
            [
                $this->card('Records', $summary['total'], 'Rows in the selected report scope', 'neutral', $this->route('school.attendance.reports', $this->attendanceRouteFilters($filters))),
                $this->card('Present', $summary['counts'][StudentAttendanceRecord::STATUS_PRESENT], 'Marked present', 'success'),
                $this->card('Absent', $summary['counts'][StudentAttendanceRecord::STATUS_ABSENT], 'Marked absent', 'warning'),
                $this->card('Late / Excused', $summary['counts'][StudentAttendanceRecord::STATUS_LATE] + $summary['counts'][StudentAttendanceRecord::STATUS_EXCUSED], $missingMeta, 'info'),
            ],
            [
                $this->link('Attendance Reports', 'Existing filtered attendance report screen.', $this->route('school.attendance.reports', $this->attendanceRouteFilters($filters))),
                $this->link('Offline Sync Monitor', 'Server-known offline attendance sync receipts.', $this->route('school.attendance.offline-sync-monitor', $this->offlineRouteFilters($filters))),
            ]
        );
    }

    private function financeGroup(School $school, array $filters): array
    {
        $report = $this->financeSummary($school, $filters);
        $summary = $report['summary'];

        return $this->group(
            'finance',
            'Finance',
            'Finance values reuse the existing finance report service and exclude references, notes, gateway payloads, and payment secrets.',
            [
                $this->card('Total Invoiced', 'NGN '.number_format($summary['total_invoiced'], 2), $summary['invoices'].' invoice(s)', 'neutral', $this->route('school.finance.reports', $this->financeRouteFilters($filters))),
                $this->card('Total Paid', 'NGN '.number_format($summary['total_paid'], 2), $summary['payments'].' payment(s)', 'success'),
                $this->card('Outstanding', 'NGN '.number_format($summary['total_outstanding'], 2), 'Open student balances', 'warning'),
                $this->card('Overdue', $report['overdue']['invoices'], 'NGN '.number_format($report['overdue']['balance'], 2), $report['overdue']['invoices'] > 0 ? 'warning' : 'success'),
            ],
            [
                $this->link('Finance Reports', 'Existing billed, paid, outstanding, and overdue reports.', $this->route('school.finance.reports', $this->financeRouteFilters($filters))),
                $this->link('Finance Audit', 'Existing finance audit review with safe metadata.', $this->route('school.finance.audit')),
                $this->link('Finance CSV Export', 'Existing protected finance summary export.', $this->route('school.import-export.finance.export', $this->financeExportFilters($filters))),
            ]
        );
    }

    private function learningGroup(School $school, array $filters): array
    {
        $classrooms = LmsClassroom::query()
            ->where('school_id', $school->id);
        $materials = LmsMaterial::query()
            ->where('school_id', $school->id);
        $activities = LmsCbtActivity::query()
            ->where('school_id', $school->id);
        $exams = CbtExam::query()
            ->where('school_id', $school->id);
        $attempts = CbtAttempt::query()
            ->where('school_id', $school->id);
        $publications = CbtResultPublication::query()
            ->where('school_id', $school->id);

        foreach ([$classrooms, $activities, $exams] as $query) {
            $this->applyAcademicFilters($query, $filters);
        }

        $this->applyClassFilter($classrooms, $filters);
        $this->applyClassFilter($activities, $filters);
        $this->applyClassFilter($exams, $filters);
        $this->applyDateRange($classrooms, 'created_at', $filters);
        $this->applyDateRange($materials, 'created_at', $filters);
        $this->applyDateRange($activities, 'created_at', $filters);
        $this->applyDateRange($exams, 'created_at', $filters);
        $this->applyDateRange($attempts, 'created_at', $filters);
        $this->applyDateRange($publications, 'created_at', $filters);

        if ($this->statusMatches($filters, [LmsClassroom::STATUS_ACTIVE, LmsClassroom::STATUS_ARCHIVED])) {
            $classrooms->where('status', $filters['status']);
        }

        if ($this->statusMatches($filters, LmsMaterial::STATUSES)) {
            $materials->where('status', $filters['status']);
        }

        if ($this->statusMatches($filters, [LmsCbtActivity::STATUS_ACTIVE, LmsCbtActivity::STATUS_ARCHIVED])) {
            $activities->where('status', $filters['status']);
        }

        if ($this->statusMatches($filters, ['draft', 'scheduled', 'open', 'published', 'closed', 'archived'])) {
            $exams->where('status', $filters['status']);
        }

        if ($this->statusMatches($filters, ['in_progress', 'resumed', 'submitted', 'graded', 'cancelled', 'expired'])) {
            $attempts->where('status', $filters['status']);
        }

        return $this->group(
            'learning',
            'LMS and CBT',
            'Learning summaries count classrooms, materials, CBT links, exams, attempts, and publications without loading CBT answers or snapshots.',
            [
                $this->card('LMS Classrooms', (clone $classrooms)->count(), 'Class and subject learning spaces', 'neutral', $this->route('school.lms.index')),
                $this->card('Published Materials', (clone $materials)->where('status', LmsMaterial::STATUS_PUBLISHED)->count(), 'Visible LMS materials', 'success'),
                $this->card('CBT Activity Links', (clone $activities)->where('status', LmsCbtActivity::STATUS_ACTIVE)->count(), 'Active LMS to CBT links', 'info'),
                $this->card('CBT Attempts', (clone $attempts)->count(), (clone $publications)->count().' result publication(s)', 'neutral', $this->route('school.cbt.dashboard')),
            ],
            [
                $this->link('LMS', 'Existing learning materials workspace.', $this->route('school.lms.index')),
                $this->link('CBT Center', 'Existing exams, question banks, marking, and result publishing.', $this->route('school.cbt.dashboard')),
            ]
        );
    }

    private function liveClassesGroup(School $school, array $filters): array
    {
        $query = LiveClass::query()
            ->where('school_id', $school->id);

        $this->applyAcademicFilters($query, $filters);
        $this->applyClassFilter($query, $filters);
        $this->applyDateRange($query, 'starts_at', $filters);

        if ($this->statusMatches($filters, LiveClass::STATUSES)) {
            $query->where('status', $filters['status']);
        }

        return $this->group(
            'live_classes',
            'Live Classes',
            'Live-class summaries exclude meeting passwords, provider payloads, credentials, and private meeting metadata.',
            [
                $this->card('Scheduled', (clone $query)->where('status', LiveClass::STATUS_SCHEDULED)->count(), 'Upcoming or planned sessions', 'info', $this->route('school.live-classes.index')),
                $this->card('Live', (clone $query)->where('status', LiveClass::STATUS_LIVE)->count(), 'Currently live sessions', 'success'),
                $this->card('Completed', (clone $query)->where('status', LiveClass::STATUS_COMPLETED)->count(), 'Finished sessions', 'neutral'),
                $this->card('Cancelled', (clone $query)->where('status', LiveClass::STATUS_CANCELLED)->count(), 'Cancelled sessions', 'warning'),
            ],
            [
                $this->link('Live Classes', 'Existing manual/provider-boundary live-class workspace.', $this->route('school.live-classes.index')),
            ]
        );
    }

    private function communicationsGroup(School $school, array $filters): array
    {
        $logs = SchoolNotificationLog::query()
            ->where('school_id', $school->id);
        $templates = SchoolNotificationTemplate::query()
            ->where('school_id', $school->id);

        $this->applyDateRange($logs, 'created_at', $filters);
        $this->applyDateRange($templates, 'created_at', $filters);

        if ($this->statusMatches($filters, SchoolNotificationLog::STATUSES)) {
            $logs->where('status', $filters['status']);
        }

        return $this->group(
            'communications',
            'Communications',
            'Communication summaries count operational logs and templates without exposing private payloads, provider responses, or recipient message bodies.',
            [
                $this->card('Notification Logs', (clone $logs)->count(), 'Safe message summaries only', 'neutral', $this->route('school.communications.logs')),
                $this->card('Pending', (clone $logs)->where('status', SchoolNotificationLog::STATUS_PENDING)->count(), 'Awaiting handling', 'warning'),
                $this->card('Sent / Logged', (clone $logs)->whereIn('status', [SchoolNotificationLog::STATUS_SENT, SchoolNotificationLog::STATUS_LOGGED])->count(), 'Recorded delivery outcomes', 'success'),
                $this->card('Templates', (clone $templates)->count(), 'School notification templates', 'info', $this->route('school.communications.templates')),
            ],
            [
                $this->link('Communication Center', 'Existing communication center.', $this->route('school.communications.index')),
                $this->link('Notification Logs', 'Existing operational notification log list.', $this->route('school.communications.logs')),
            ]
        );
    }

    private function offlineOperationsGroup(School $school, array $filters): array
    {
        $offline = $this->sync->offlineAttendanceSyncHealth($school->id, $this->offlineFilters($filters));
        $health = $this->health->summary($school)['overall'];
        $latestBackup = Backup::query()
            ->where('school_id', $school->id)
            ->latest('id')
            ->first();
        $latestUpdate = UpdatePackage::query()
            ->latest('id')
            ->first();

        return $this->group(
            'operations',
            'Offline and Operations',
            'Operations summaries stay high-level and do not reveal backup paths, update internals, environment values, or sync tokens.',
            [
                $this->card('Offline Receipts', $offline['receipt_total'], $offline['synced_count'].' synced receipt(s)', $this->needsReviewTone($offline), $this->route('school.attendance.offline-sync-monitor', $this->offlineRouteFilters($filters))),
                $this->card('Sync Needs Review', $offline['conflict_count'] + $offline['failed_validation_count'] + $offline['failed_permission_count'], 'Conflicts or failed attempts', $this->needsReviewTone($offline)),
                $this->card('System Health', $health['label'], $health['message'], $health['tone']),
                $this->card('Backup / Update', $latestBackup ? str($latestBackup->status)->replace('_', ' ')->title()->toString() : 'No backup', $latestUpdate ? 'Latest update: '.str($latestUpdate->status)->replace('_', ' ')->title()->toString() : 'No update package', $latestBackup ? 'info' : 'warning'),
            ],
            [
                $this->link('Offline Sync Monitor', 'Existing attendance offline sync monitor.', $this->route('school.attendance.offline-sync-monitor', $this->offlineRouteFilters($filters))),
            ]
        );
    }

    private function financeSummary(School $school, array $filters): array
    {
        return $this->financeReports->report($school, $this->financeReportFilters($filters));
    }

    private function attendanceSummary(School $school, array $filters): array
    {
        $query = StudentAttendanceRecord::query()
            ->where('school_id', $school->id);

        $this->applyDateRange($query, 'attendance_date', $filters);
        $this->applyAcademicFilters($query, $filters);
        $this->applyClassFilter($query, $filters);

        if ($this->statusMatches($filters, StudentAttendanceRecord::STATUSES)) {
            $query->where('status', $filters['status']);
        }

        $records = (clone $query)->get(['student_id', 'status']);
        $counts = collect(StudentAttendanceRecord::STATUSES)
            ->mapWithKeys(fn (string $status): array => [$status => $records->where('status', $status)->count()])
            ->all();
        $total = array_sum($counts);
        $attended = ($counts[StudentAttendanceRecord::STATUS_PRESENT] ?? 0)
            + ($counts[StudentAttendanceRecord::STATUS_LATE] ?? 0)
            + ($counts[StudentAttendanceRecord::STATUS_EXCUSED] ?? 0);

        return [
            'counts' => $counts,
            'total' => $total,
            'attendance_percentage' => $total > 0 ? round(($attended / $total) * 100, 1) : 0.0,
            'missing' => $this->missingAttendanceCount($school, $filters),
        ];
    }

    private function missingAttendanceCount(School $school, array $filters): ?int
    {
        if (blank($filters['school_class_id'] ?? null) || blank($filters['date_from'] ?? null) || blank($filters['date_to'] ?? null)) {
            return null;
        }

        if ((string) $filters['date_from'] !== (string) $filters['date_to']) {
            return null;
        }

        if (filled($filters['status'] ?? null) || filled($filters['academic_session_id'] ?? null) || filled($filters['term_id'] ?? null)) {
            return null;
        }

        $classId = (int) $filters['school_class_id'];
        $expected = $school->students()
            ->where('status', 'active')
            ->where(function (Builder $query) use ($school, $classId): void {
                $query->where('school_class_id', $classId)
                    ->orWhereHas('classEnrollments', fn (Builder $enrollmentQuery) => $enrollmentQuery
                        ->where('school_id', $school->id)
                        ->where('school_class_id', $classId)
                        ->current());
            })
            ->count();
        $marked = StudentAttendanceRecord::query()
            ->where('school_id', $school->id)
            ->where('school_class_id', $classId)
            ->whereDate('attendance_date', (string) $filters['date_from'])
            ->distinct('student_id')
            ->count('student_id');

        return max($expected - $marked, 0);
    }

    private function countStudents(School $school, array $filters): int
    {
        return $this->studentQuery($school, $filters)->count();
    }

    private function studentQuery(School $school, array $filters): Builder
    {
        $query = Student::query()
            ->where('school_id', $school->id)
            ->when(filled($filters['school_class_id'] ?? null), function (Builder $query) use ($school, $filters): void {
                $classId = (int) $filters['school_class_id'];

                $query->where(function (Builder $query) use ($school, $classId): void {
                    $query->where('school_class_id', $classId)
                        ->orWhereHas('classEnrollments', fn (Builder $enrollmentQuery) => $enrollmentQuery
                            ->where('school_id', $school->id)
                            ->where('school_class_id', $classId)
                            ->current());
                });
            })
            ->when($this->statusMatches($filters, self::STUDENT_STATUSES), fn (Builder $query) => $query->where('status', $filters['status']));

        $this->applyDateRange($query, 'created_at', $filters);

        return $query;
    }

    private function learningLinkCount(School $school, array $filters): int
    {
        $classrooms = LmsClassroom::query()->where('school_id', $school->id);
        $links = LmsCbtActivity::query()->where('school_id', $school->id)->where('status', LmsCbtActivity::STATUS_ACTIVE);

        foreach ([$classrooms, $links] as $query) {
            $this->applyAcademicFilters($query, $filters);
            $this->applyClassFilter($query, $filters);
            $this->applyDateRange($query, 'created_at', $filters);
        }

        return (clone $classrooms)->count() + (clone $links)->count();
    }

    private function notificationLogCount(School $school, array $filters): int
    {
        $query = SchoolNotificationLog::query()->where('school_id', $school->id);
        $this->applyDateRange($query, 'created_at', $filters);

        if ($this->statusMatches($filters, SchoolNotificationLog::STATUSES)) {
            $query->where('status', $filters['status']);
        }

        return $query->count();
    }

    private function applyDateRange(Builder $query, string $column, array $filters): void
    {
        $query
            ->when(filled($filters['date_from'] ?? null), fn (Builder $query) => $query->whereDate($column, '>=', (string) $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn (Builder $query) => $query->whereDate($column, '<=', (string) $filters['date_to']));
    }

    private function applyAcademicFilters(Builder $query, array $filters): void
    {
        $query
            ->when(filled($filters['academic_session_id'] ?? null), fn (Builder $query) => $query->where('academic_session_id', (int) $filters['academic_session_id']))
            ->when(filled($filters['term_id'] ?? null), fn (Builder $query) => $query->where('term_id', (int) $filters['term_id']));
    }

    private function applyClassFilter(Builder $query, array $filters, string $column = 'school_class_id'): void
    {
        $query->when(filled($filters['school_class_id'] ?? null), fn (Builder $query) => $query->where($column, (int) $filters['school_class_id']));
    }

    private function statusMatches(array $filters, array $allowed): bool
    {
        return filled($filters['status'] ?? null)
            && in_array((string) $filters['status'], $allowed, true);
    }

    private function financeReportFilters(array $filters): array
    {
        $financeFilters = collect($filters)
            ->only(['date_from', 'date_to', 'school_class_id', 'academic_session_id', 'term_id'])
            ->filter(fn ($value): bool => filled($value))
            ->all();

        if ($this->statusMatches($filters, StudentFeeInvoice::STATUSES)) {
            $financeFilters['invoice_status'] = $filters['status'];
        }

        return $financeFilters;
    }

    private function offlineFilters(array $filters): array
    {
        return collect($filters)
            ->only(['date_from', 'date_to'])
            ->filter(fn ($value): bool => filled($value))
            ->when($this->statusMatches($filters, StandaloneSyncService::OFFLINE_ATTENDANCE_RESULT_STATUSES), fn ($collection) => $collection->put('status', $filters['status']))
            ->all();
    }

    private function attendanceRouteFilters(array $filters): array
    {
        return collect($filters)
            ->only(['date_from', 'date_to', 'school_class_id', 'academic_session_id', 'term_id'])
            ->when($this->statusMatches($filters, StudentAttendanceRecord::STATUSES), fn ($collection) => $collection->put('status', $filters['status']))
            ->filter(fn ($value): bool => filled($value))
            ->all();
    }

    private function financeRouteFilters(array $filters): array
    {
        return $this->financeReportFilters($filters);
    }

    private function offlineRouteFilters(array $filters): array
    {
        return $this->offlineFilters($filters);
    }

    private function financeExportFilters(array $filters): array
    {
        return $this->financeReportFilters($filters);
    }

    private function exportLinks(array $filters): array
    {
        return [
            $this->link('Student CSV Export', 'Existing protected student export.', $this->route('school.import-export.students.export', $this->studentExportFilters($filters))),
            $this->link('Attendance CSV Export', 'Existing protected attendance summary export.', $this->route('school.import-export.attendance.export', $this->attendanceRouteFilters($filters))),
            $this->link('Finance CSV Export', 'Existing protected finance summary export.', $this->route('school.import-export.finance.export', $this->financeExportFilters($filters))),
            $this->link('Import / Export Tools', 'Open the existing CSV import/export workspace.', $this->route('school.import-export.index')),
        ];
    }

    private function studentExportFilters(array $filters): array
    {
        return collect($filters)
            ->only(['school_class_id'])
            ->when($this->statusMatches($filters, self::STUDENT_STATUSES), fn ($collection) => $collection->put('status', $filters['status']))
            ->filter(fn ($value): bool => filled($value))
            ->all();
    }

    private function statusOptions(): array
    {
        return collect([
            ...self::STUDENT_STATUSES,
            ...AdmissionApplication::STATUSES,
            ...StudentAttendanceRecord::STATUSES,
            ...StudentFeeInvoice::STATUSES,
            ...LmsMaterial::STATUSES,
            LmsClassroom::STATUS_ACTIVE,
            LmsClassroom::STATUS_ARCHIVED,
            LmsCbtActivity::STATUS_ACTIVE,
            LmsCbtActivity::STATUS_ARCHIVED,
            'draft',
            'scheduled',
            'open',
            'published',
            'closed',
            'in_progress',
            'resumed',
            'submitted',
            'graded',
            'expired',
            ...LiveClass::STATUSES,
            ...SchoolNotificationLog::STATUSES,
            ...StandaloneSyncService::OFFLINE_ATTENDANCE_RESULT_STATUSES,
        ])
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function privacyNotes(): array
    {
        return [
            'Reports are scoped to the current school context only.',
            'Private data is summarized; raw CBT answers, answer payloads, and candidate secrets are never loaded by this page.',
            'Admission documents, tracking tokens, applicant private notes, and admission payment payloads are not shown.',
            'Finance references, notes, gateway payloads, and payment secrets stay in existing protected finance tools.',
            'Meeting passwords, provider credentials, provider payloads, notification private payloads, backup paths, and update internals are not exposed.',
            'This pack is not a BI engine, public report portal, PDF redesign, report scheduler, or delivery system.',
        ];
    }

    private function group(string $key, string $title, string $description, array $cards, array $links): array
    {
        return compact('key', 'title', 'description', 'cards', 'links');
    }

    private function card(string $label, int|float|string $value, ?string $meta = null, string $tone = 'neutral', ?string $href = null): array
    {
        return compact('label', 'value', 'meta', 'tone', 'href');
    }

    private function link(string $label, string $description, ?string $href): array
    {
        return compact('label', 'description', 'href');
    }

    private function route(string $name, array $parameters = []): ?string
    {
        return Route::has($name) ? route($name, $parameters) : null;
    }

    private function needsReviewTone(array $offline): string
    {
        return ($offline['conflict_count'] + $offline['failed_validation_count'] + $offline['failed_permission_count']) > 0
            ? 'warning'
            : 'info';
    }
}
