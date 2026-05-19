@props([
    'result',
    'school' => null,
    'student' => null,
    'studentProfileUrl' => null,
    'notifyUrl' => null,
])

@php
    $user = auth()->user();
    $school = $school ?? $result->school;
    $student = $student ?? $result->student;
    $status = (string) $result->status;
    $isComplete = filled($result->ca_score)
        && filled($result->exam_score)
        && filled($result->total_score)
        && filled($result->grade);
    $isPublished = $status === \App\Enums\ResultWorkflowStatus::Published->value
        && filled($result->published_at)
        && blank($result->unpublished_at);
    $canPublishPermission = $school
        && $user?->can('publish', [\App\Models\StudentResult::class, $school]);
    $canUnpublishPermission = $school
        && $user?->can('unpublish', [\App\Models\StudentResult::class, $school]);
    $canPublish = $canPublishPermission
        && in_array($status, \App\Enums\ResultWorkflowStatus::publishableStudentResultValues(), true)
        && $isComplete;
    $canUnpublish = $canUnpublishPermission && $isPublished;
    $canSubmit = $user?->can('submit', $result);
    $canApprove = $user?->can('approve', $result);
    $canReturn = $user?->can('returnForCorrection', $result);
    $canEdit = $user?->can('update', $result);
    $workspaceUrl = $student && Route::has('school.students.results')
        ? route('school.students.results', array_filter([
            'student' => $student,
            'session' => $result->academic_session_id,
            'term' => $result->term_id,
            'result_type' => $result->result_type ?: 'term_result',
        ]))
        : null;
    $auditUrl = $studentProfileUrl
        ? $studentProfileUrl.'#activity-timeline'
        : ($student && Route::has('school.students.show') ? route('school.students.show', $student).'#activity-timeline' : null);
    $canOpenSchoolAuditLogs = in_array(app(\App\Services\CurrentSchoolService::class)->roleContext($user), ['super_admin', 'school_admin'], true);
    $auditLogUrl = $canOpenSchoolAuditLogs && Route::has('school.audit-logs.index')
        ? route('school.audit-logs.index', ['action' => 'result_', 'auditable_type' => class_basename($result)])
        : $auditUrl;
    $linkClass = 'inline-flex min-h-8 items-center rounded-md border border-border-subtle px-2 py-1 text-xs font-semibold text-text-secondary transition hover:bg-bg-tertiary hover:text-text-primary';
    $successClass = 'inline-flex min-h-8 items-center rounded-md border border-emerald-500/30 px-2 py-1 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-500/10 dark:text-emerald-300';
    $warningClass = 'inline-flex min-h-8 items-center rounded-md border border-amber-500/30 px-2 py-1 text-xs font-semibold text-amber-700 transition hover:bg-amber-500/10 dark:text-amber-300';
    $dangerClass = 'inline-flex min-h-8 items-center rounded-md border border-red-500/30 px-2 py-1 text-xs font-semibold text-red-700 transition hover:bg-red-500/10 dark:text-red-300';
@endphp

<div {{ $attributes->merge(['class' => 'flex min-w-56 flex-wrap gap-2']) }} data-result-actions>
    @if ($workspaceUrl)
        <a href="{{ $workspaceUrl }}" class="{{ $linkClass }}">View</a>
    @endif

    @if ($canEdit && Route::has('school.results.manual.edit'))
        <a href="{{ route('school.results.manual.edit', $result) }}" class="{{ $linkClass }}">Edit</a>
        @if (in_array($status, \App\Enums\ResultWorkflowStatus::teacherEditableValues(), true))
            <a href="{{ route('school.results.manual.edit', $result) }}" class="{{ $linkClass }}">Save Draft</a>
        @endif
    @endif

    @if ($canSubmit && Route::has('school.results.manual.submit'))
        <form method="POST" action="{{ route('school.results.manual.submit', $result) }}" data-result-action-form data-confirm="Submit this result for review?" data-loading-text="Submitting...">
            @csrf
            <button type="submit" class="{{ $successClass }}">Submit</button>
        </form>
    @endif

    @if ($canApprove && Route::has('school.results.manual.approve'))
        <form method="POST" action="{{ route('school.results.manual.approve', $result) }}" data-result-action-form data-confirm="Approve this result?" data-loading-text="Approving...">
            @csrf
            <button type="submit" class="{{ $successClass }}">Approve</button>
        </form>
    @endif

    @if ($canPublishPermission && $isComplete && Route::has('school.results.publishing.publish-single'))
        <form method="POST" action="{{ route('school.results.publishing.publish-single', $result) }}" data-result-action-form data-result-publish-form data-confirm="Publish this result now?" data-loading-text="Publishing..." @class(['hidden' => ! $canPublish])>
            @csrf
            <button type="submit" class="{{ $successClass }}">Publish</button>
        </form>
    @endif

    @if ($canUnpublishPermission && Route::has('school.results.publishing.unpublish-single'))
        <form method="POST" action="{{ route('school.results.publishing.unpublish-single', $result) }}" data-result-action-form data-result-unpublish-form data-confirm="Unpublish this result?" data-loading-text="Unpublishing..." @class(['hidden' => ! $canUnpublish])>
            @csrf
            <input type="hidden" name="unpublish_reason" value="Unpublished from the result row actions.">
            <button type="submit" class="{{ $dangerClass }}">Unpublish</button>
        </form>
    @endif

    @if ($canReturn && Route::has('school.results.manual.return'))
        <form method="POST" action="{{ route('school.results.manual.return', $result) }}" data-result-action-form data-confirm="Return this result for correction?" data-loading-text="Returning...">
            @csrf
            <input type="hidden" name="return_reason" value="Returned from the result row actions.">
            <button type="submit" class="{{ $warningClass }}">Return for Correction</button>
        </form>
    @endif

    @if ($auditUrl)
        <a href="{{ $auditUrl }}" class="{{ $linkClass }}">View History</a>
    @endif

    @if ($auditLogUrl && $auditLogUrl !== $auditUrl)
        <a href="{{ $auditLogUrl }}" class="{{ $linkClass }}">View Audit Log</a>
    @endif

    @if ($notifyUrl && $student?->guardian_email)
        <a href="{{ $notifyUrl }}" class="{{ $linkClass }}">Notify Parent/Student</a>
    @endif
</div>
