<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CommunicationLog;
use App\Models\LeadRequest;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentResult;
use App\Models\SupportThread;
use App\Models\User;
use App\Services\CurrentSchoolService;
use App\Services\SchoolAuthorizationService;
use App\Services\SupportRoutingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    public function __invoke(
        Request $request,
        CurrentSchoolService $currentSchool,
        SchoolAuthorizationService $authorization,
        SupportRoutingService $support
    ): JsonResponse {
        $user = $request->user();
        $query = trim((string) $request->query('q', ''));

        if (! $user || mb_strlen($query) < 2) {
            return response()->json([
                'query' => $query,
                'groups' => [],
                'message' => 'Type at least 2 characters to search.',
            ]);
        }

        $school = $currentSchool->get($user);
        $role = $currentSchool->roleContext($user);
        $isPlatform = $user->hasRole('super_admin') && ! $currentSchool->inSupportMode($user);

        $groups = $isPlatform
            ? $this->platformResults($query, $user, $support)
            : $this->schoolResults($query, $user, $school, $role, $authorization, $support);

        return response()->json([
            'query' => $query,
            'groups' => $groups->filter(fn (array $group) => $group['items'] !== [])->values(),
        ]);
    }

    private function platformResults(string $query, User $user, SupportRoutingService $support): Collection
    {
        return collect([
            $this->schools($query),
            $this->platformStaff($query),
            $this->platformSupport($query, $support),
            $this->platformAuditLogs($query),
            $this->platformCommunications($query),
            $this->leads($query),
            $this->notifications($query, $user),
        ]);
    }

    private function schoolResults(
        string $query,
        User $user,
        ?School $school,
        ?string $role,
        SchoolAuthorizationService $authorization,
        SupportRoutingService $support
    ): Collection {
        if (! $school) {
            return collect();
        }

        return collect([
            $this->students($query, $user, $school, $authorization),
            $this->schoolStaff($query, $school, $role),
            $this->classes($query, $school, $role),
            $this->studentResults($query, $user, $school, $role, $authorization),
            $this->schoolSupport($query, $school, $user, $role, $support),
            $this->schoolAuditLogs($query, $school, $role),
            $this->schoolCommunications($query, $school, $role),
            $this->notifications($query, $user),
        ]);
    }

    private function schools(string $query): array
    {
        if (! $this->tableReady('schools') || ! Route::has('admin.schools.index')) {
            return $this->group('Schools');
        }

        $items = School::query()
            ->where(function (Builder $builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('school_code', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            })
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (School $school) => $this->item(
                $school->name,
                trim(($school->school_code ?: 'No code').' / '.ucfirst((string) $school->status), ' /'),
                route('admin.schools.index', ['search' => $query]),
                'School'
            ))
            ->all();

        return $this->group('Schools', $items);
    }

    private function platformStaff(string $query): array
    {
        if (! $this->tableReady('users') || ! Route::has('admin.schools.index')) {
            return $this->group('Staff');
        }

        $items = User::query()
            ->with('roles')
            ->where(function (Builder $builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('staff_code', 'like', "%{$query}%");
            })
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (User $staff) => $this->item(
                $staff->name,
                trim(($staff->email ?: 'No email').' / '.$staff->roles->pluck('name')->implode(', '), ' /'),
                route('admin.schools.index', ['search' => $query]),
                'Staff'
            ))
            ->all();

        return $this->group('Staff', $items);
    }

    private function students(string $query, User $user, School $school, SchoolAuthorizationService $authorization): array
    {
        if (! $this->tableReady('students') || ! Route::has('school.students.show')) {
            return $this->group('Students');
        }

        $students = Student::query()
            ->with('schoolClass')
            ->where('school_id', $school->id)
            ->where(function (Builder $builder) use ($query) {
                $builder->where('admission_number', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('middle_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('guardian_name', 'like', "%{$query}%")
                    ->orWhere('guardian_email', 'like', "%{$query}%");
            })
            ->latest('id')
            ->limit(12)
            ->get()
            ->filter(fn (Student $student) => $authorization->canViewStudent($user, $school, $student))
            ->take(6);

        return $this->group('Students', $students->map(fn (Student $student) => $this->item(
            $student->fullName() ?: 'Unnamed student',
            trim(($student->admission_number ?: 'No admission number').' / '.($student->schoolClass?->name ?: 'No class'), ' /'),
            route('school.students.show', $student),
            'Student'
        ))->values()->all());
    }

    private function schoolStaff(string $query, School $school, ?string $role): array
    {
        if ($role !== 'school_admin' || ! $this->tableReady('users') || ! Route::has('school.staff.index')) {
            return $this->group('Staff');
        }

        $items = User::query()
            ->with('roles')
            ->where(function (Builder $builder) use ($school) {
                $builder->where('school_id', $school->id)
                    ->orWhereHas('activeSchoolRoles', fn (Builder $roles) => $roles->where('school_id', $school->id));
            })
            ->where(function (Builder $builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('staff_code', 'like', "%{$query}%");
            })
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (User $staff) => $this->item(
                $staff->name,
                trim(($staff->staff_code ?: 'No staff code').' / '.$staff->roles->pluck('name')->implode(', '), ' /'),
                route('school.staff.index', ['search' => $query]),
                'Staff'
            ))
            ->all();

        return $this->group('Staff', $items);
    }

    private function classes(string $query, School $school, ?string $role): array
    {
        if ($role === 'teacher' || ! $this->tableReady('school_classes') || ! Route::has('school.classes.index')) {
            return $this->group('Classes');
        }

        $items = SchoolClass::query()
            ->where('school_id', $school->id)
            ->where(function (Builder $builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->orWhere('section', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(6)
            ->get()
            ->map(fn (SchoolClass $class) => $this->item(
                trim($class->name.' '.$class->section),
                $class->code ?: ucfirst((string) $class->status),
                route('school.classes.index', ['search' => $query]),
                'Class'
            ))
            ->all();

        return $this->group('Classes', $items);
    }

    private function studentResults(
        string $query,
        User $user,
        School $school,
        ?string $role,
        SchoolAuthorizationService $authorization
    ): array {
        if (! $this->tableReady('student_results') || ! Route::has('school.students.results')) {
            return $this->group('Results');
        }

        $results = StudentResult::query()
            ->with(['student.schoolClass', 'subject', 'academicSession', 'term'])
            ->where('school_id', $school->id)
            ->where(function (Builder $builder) use ($query) {
                $builder->where('grade', 'like', "%{$query}%")
                    ->orWhere('status', 'like', "%{$query}%")
                    ->orWhereHas('student', function (Builder $students) use ($query) {
                        $students->where('admission_number', 'like', "%{$query}%")
                            ->orWhere('first_name', 'like', "%{$query}%")
                            ->orWhere('last_name', 'like', "%{$query}%");
                    })
                    ->orWhereHas('subject', fn (Builder $subjects) => $subjects->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%"));
            })
            ->latest('id')
            ->limit(14)
            ->get()
            ->filter(function (StudentResult $result) use ($user, $school, $role, $authorization) {
                if (! $result->student) {
                    return false;
                }

                return $role !== 'teacher'
                    || $authorization->canViewStudent($user, $school, $result->student);
            })
            ->take(6);

        return $this->group('Results', $results->map(fn (StudentResult $result) => $this->item(
            ($result->student?->fullName() ?: 'Student result').' - '.($result->subject?->name ?: 'Subject'),
            trim(ucfirst(str_replace('_', ' ', (string) $result->status)).' / '.($result->term?->name ?: 'No term'), ' /'),
            route('school.students.results', [
                'student' => $result->student_id,
                'session' => $result->academic_session_id,
                'term' => $result->term_id,
                'result_type' => $result->result_type ?: 'term_result',
            ]),
            'Result'
        ))->values()->all());
    }

    private function schoolSupport(string $query, School $school, User $user, ?string $role, SupportRoutingService $support): array
    {
        if (! $this->tableReady('support_threads') || ! Route::has('school.support.show')) {
            return $this->group('Support Tickets');
        }

        $items = $support->visibleSchoolThreadsQuery($school, $user, $role)
            ->where(function (Builder $builder) use ($query) {
                $builder->where('subject', 'like', "%{$query}%")
                    ->orWhere('status', 'like', "%{$query}%")
                    ->orWhere('priority', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%");
            })
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (SupportThread $thread) => $this->item(
                '#'.$thread->id.' '.$thread->subject,
                ucfirst((string) $thread->status).' / '.ucfirst((string) $thread->priority),
                route('school.support.show', $thread),
                'Support'
            ))
            ->all();

        return $this->group('Support Tickets', $items);
    }

    private function platformSupport(string $query, SupportRoutingService $support): array
    {
        if (! $this->tableReady('support_threads') || ! Route::has('admin.support-threads.show')) {
            return $this->group('Support Tickets');
        }

        $items = $support->visiblePlatformThreadsQuery()
            ->with('school')
            ->where(function (Builder $builder) use ($query) {
                $builder->where('subject', 'like', "%{$query}%")
                    ->orWhere('status', 'like', "%{$query}%")
                    ->orWhere('priority', 'like', "%{$query}%")
                    ->orWhereHas('school', fn (Builder $schools) => $schools->where('name', 'like', "%{$query}%"));
            })
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (SupportThread $thread) => $this->item(
                '#'.$thread->id.' '.$thread->subject,
                trim(($thread->school?->name ?: 'Platform').' / '.ucfirst((string) $thread->status), ' /'),
                route('admin.support-threads.show', $thread),
                'Support'
            ))
            ->all();

        return $this->group('Support Tickets', $items);
    }

    private function schoolAuditLogs(string $query, School $school, ?string $role): array
    {
        if ($role !== 'school_admin' || ! $this->tableReady('audit_logs') || ! Route::has('school.audit-logs.index')) {
            return $this->group('Audit Logs');
        }

        $items = AuditLog::query()
            ->where('school_id', $school->id)
            ->where(function (Builder $builder) use ($query) {
                $builder->where('action', 'like', "%{$query}%")
                    ->orWhere('action_tag', 'like', "%{$query}%")
                    ->orWhere('event', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%");
            })
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (AuditLog $log) => $this->item(
                $log->action,
                trim(($log->category ?: 'audit').' / '.($log->created_at?->diffForHumans() ?: 'recent'), ' /'),
                route('school.audit-logs.index', ['action' => $query]),
                'Audit'
            ))
            ->all();

        return $this->group('Audit Logs', $items);
    }

    private function platformAuditLogs(string $query): array
    {
        if (! $this->tableReady('audit_logs') || ! Route::has('admin.audit-logs.index')) {
            return $this->group('Audit Logs');
        }

        $items = AuditLog::query()
            ->with('school')
            ->where(function (Builder $builder) use ($query) {
                $builder->where('action', 'like', "%{$query}%")
                    ->orWhere('action_tag', 'like', "%{$query}%")
                    ->orWhere('event', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%");
            })
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (AuditLog $log) => $this->item(
                $log->action,
                trim(($log->school?->name ?: 'Platform').' / '.($log->severity ?: 'info'), ' /'),
                route('admin.audit-logs.index', ['action' => $query]),
                'Audit'
            ))
            ->all();

        return $this->group('Audit Logs', $items);
    }

    private function schoolCommunications(string $query, School $school, ?string $role): array
    {
        if ($role !== 'school_admin' || ! $this->tableReady('communication_logs') || ! Route::has('school.communications.bulk')) {
            return $this->group('Communications');
        }

        $items = CommunicationLog::forSchool($school)
            ->search($query)
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (CommunicationLog $log) => $this->item(
                $log->subject,
                trim(($log->recipient ?: 'No recipient').' / '.ucfirst((string) $log->status), ' /'),
                route('school.communications.bulk', ['search' => $query]),
                'Communication'
            ))
            ->all();

        return $this->group('Communications', $items);
    }

    private function platformCommunications(string $query): array
    {
        if (! $this->tableReady('communication_logs') || ! Route::has('admin.communications.index')) {
            return $this->group('Communications');
        }

        $items = CommunicationLog::forSchool(null)
            ->search($query)
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (CommunicationLog $log) => $this->item(
                $log->subject,
                trim(($log->recipient ?: 'No recipient').' / '.ucfirst((string) $log->status), ' /'),
                route('admin.communications.index', ['type' => $log->type]),
                'Communication'
            ))
            ->all();

        return $this->group('Communications', $items);
    }

    private function leads(string $query): array
    {
        if (! $this->tableReady('lead_requests') || ! Route::has('admin.lead-requests.index')) {
            return $this->group('Leads');
        }

        $items = LeadRequest::query()
            ->search($query)
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (LeadRequest $lead) => $this->item(
                $lead->school_name ?: $lead->name,
                trim(($lead->email ?: $lead->phone ?: 'No contact').' / '.$lead->statusLabel(), ' /'),
                route('admin.lead-requests.show', $lead),
                'Lead'
            ))
            ->all();

        return $this->group('Leads', $items);
    }

    private function notifications(string $query, User $user): array
    {
        if (! $this->tableReady('notifications') || ! Route::has('notifications.index')) {
            return $this->group('Notifications');
        }

        $items = $user->notifications()
            ->where(function (Builder $builder) use ($query) {
                $builder->where('data->title', 'like', "%{$query}%")
                    ->orWhere('data->body', 'like', "%{$query}%")
                    ->orWhere('data->category', 'like', "%{$query}%")
                    ->orWhere('data->event', 'like', "%{$query}%");
            })
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn ($notification) => $this->item(
                (string) data_get($notification->data, 'title', class_basename($notification->type)),
                (string) data_get($notification->data, 'body', $notification->created_at?->diffForHumans()),
                (string) (data_get($notification->data, 'action_url') ?: route('notifications.index')),
                'Notification'
            ))
            ->all();

        return $this->group('Notifications', $items);
    }

    private function group(string $label, array $items = []): array
    {
        return [
            'label' => $label,
            'items' => array_values($items),
        ];
    }

    private function item(string $title, ?string $subtitle, string $url, string $type): array
    {
        return [
            'title' => $title,
            'subtitle' => $subtitle ?: $type,
            'url' => $url,
            'type' => $type,
        ];
    }

    private function tableReady(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
