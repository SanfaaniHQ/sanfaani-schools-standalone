<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\BackupLog;
use App\Models\BackupRestorePlan;
use App\Services\AuditLogService;
use App\Services\Backups\BackupLogService;
use App\Services\Backups\BackupPreflightService;
use App\Services\Backups\BackupRestorePlanService;
use App\Services\Backups\BackupRetentionService;
use App\Services\Backups\BackupService;
use App\Services\Backups\BackupVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BackupController extends Controller
{
    public function __construct(
        private BackupService $backups,
        private BackupPreflightService $preflight,
        private BackupVerificationService $verification,
        private BackupRetentionService $retention,
        private BackupLogService $logs,
        private BackupRestorePlanService $restorePlans,
        private AuditLogService $auditLog,
    ) {}

    public function index(): View
    {
        $decision = $this->authorizeBackupAccess();
        $school = $this->backups->defaultSchool();
        $query = $this->backups->visibleBackups(auth()->user())->latest();

        return view('admin.backups.index', [
            'label' => $decision['label'],
            'decision' => $decision,
            'backups' => (clone $query)->paginate(10),
            'latestBackup' => (clone $query)->first(),
            'retentionPolicy' => $this->backups->retentionPolicy(),
            'preUpdateReadiness' => $this->backups->preUpdateReadiness($school),
            'logs' => BackupLog::with(['backup', 'creator'])
                ->when($school, fn ($query) => $query->where(function ($query) use ($school): void {
                    $query->where('school_id', $school->id)->orWhereNull('school_id');
                }))
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function create(): View
    {
        $decision = $this->authorizeBackupAccess();

        return view('admin.backups.create', [
            'label' => $decision['label'],
            'decision' => $decision,
            'preflight' => $this->preflight->run($this->backups->defaultSchool(), auth()->user())->toArray(),
            'retentionPolicy' => $this->backups->retentionPolicy(),
            'scopes' => [
                'database' => (bool) config('backups.database_enabled', true),
                'files' => (bool) config('backups.files_enabled', true),
                'config' => (bool) config('backups.config_enabled', true),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBackupAccess();

        $backup = $this->backups->createManualBackup($request->user(), trigger: 'manual_web');
        $this->auditLog->log('backup_requested', $backup, metadata: [
            'status' => $backup->status,
            'trigger' => $backup->trigger,
        ], request: $request);

        return redirect()
            ->route('admin.backups.show', $backup)
            ->with(
                in_array($backup->status, [Backup::STATUS_FAILED, Backup::STATUS_WARNING], true) ? 'error' : 'success',
                $backup->status === Backup::STATUS_FAILED
                    ? 'Backup metadata request failed. Review logs and shared-hosting guidance.'
                    : 'Backup metadata was created safely. Review verification before relying on it for updates.'
            );
    }

    public function show(Backup $backup): View
    {
        $decision = $this->authorizeBackupAccess();
        $this->authorizeBackupRecord($backup);

        $backup->load(['school', 'creator', 'items', 'logs.creator', 'latestVerification', 'restorePlan']);

        return view('admin.backups.show', [
            'label' => $decision['label'],
            'decision' => $decision,
            'backup' => $backup,
        ]);
    }

    public function verify(Backup $backup): RedirectResponse
    {
        $this->authorizeBackupAccess();
        $this->authorizeBackupRecord($backup);

        $verification = $this->verification->verify($backup, auth()->user());
        $this->auditLog->log('backup_verification_run', $backup->fresh(), metadata: [
            'verification_status' => $verification->status,
        ], request: request());

        return redirect()
            ->route('admin.backups.show', $backup)
            ->with(
                $verification->status === 'verified' ? 'success' : 'error',
                $verification->message ?: 'Backup verification completed.'
            );
    }

    public function restorePlan(Backup $backup): View
    {
        $decision = $this->authorizeBackupAccess();
        $this->authorizeBackupRecord($backup);

        $backup->load(['items', 'latestVerification']);
        $plan = $backup->restorePlan ?: $this->restorePlans->createForBackup($backup, auth()->user());
        $this->logs->log(
            'backup.restore_plan_viewed',
            'Manual restore plan was opened for review. No restore was performed.',
            $backup,
            severity: 'info',
            actor: auth()->user(),
        );
        $this->auditLog->log('backup_restore_plan_viewed', $backup, metadata: [
            'plan_id' => $plan instanceof BackupRestorePlan ? $plan->id : $plan->fresh()?->id,
            'manual_only' => true,
        ], request: request());

        return view('admin.backups.restore-plan', [
            'label' => $decision['label'],
            'decision' => $decision,
            'backup' => $backup,
            'plan' => $plan instanceof BackupRestorePlan ? $plan : $plan->fresh(),
        ]);
    }

    public function prune(): RedirectResponse
    {
        $this->authorizeBackupAccess();

        $count = $this->retention->pruneExpired(auth()->user());
        $this->auditLog->log('backup_retention_pruned', null, metadata: [
            'pruned_count' => $count,
        ], request: request());

        return redirect()
            ->route('admin.backups.index')
            ->with('success', "{$count} expired backup record(s) were pruned safely.");
    }

    private function authorizeBackupAccess(): array
    {
        $decision = $this->backups->checkAccess(auth()->user());

        abort_unless($decision['allowed'], 403, $decision['message']);

        return $decision;
    }

    private function authorizeBackupRecord(Backup $backup): void
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return;
        }

        abort_unless($backup->school_id && $backup->school_id === $user?->school_id, 403);
    }
}
