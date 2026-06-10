<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\DatabaseBackupService;
use App\Services\Security\SecretRedactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class SystemMaintenanceController extends Controller
{
    public function index(DatabaseBackupService $backups)
    {
        return view('admin.system-maintenance.index', [
            'backups' => $backups->backups(),
            'backupRetentionCount' => (int) config('sanfaani.backups.retention_count', 10),
        ]);
    }

    public function clearAllCache(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        return $this->run($request, $auditLog, 'clear_all_cache', ['optimize:clear'], 'All Laravel caches cleared.');
    }

    public function clearConfigCache(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        return $this->run($request, $auditLog, 'clear_config_cache', ['config:clear'], 'Configuration cache cleared.');
    }

    public function clearRouteCache(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        return $this->run($request, $auditLog, 'clear_route_cache', ['route:clear'], 'Route cache cleared.');
    }

    public function clearViewCache(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        return $this->run($request, $auditLog, 'clear_view_cache', ['view:clear'], 'View cache cleared.');
    }

    public function clearAppCache(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        return $this->run($request, $auditLog, 'clear_app_cache', ['cache:clear'], 'Application cache cleared.');
    }

    public function optimize(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        return $this->run($request, $auditLog, 'optimize_application', [
            'optimize:clear',
            'config:cache',
            'route:cache',
            'view:cache',
        ], 'Application cache optimized.');
    }

    public function storageLink(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        return $this->run($request, $auditLog, 'storage_link', ['storage:link'], 'Storage link is ready.');
    }

    public function createBackup(
        Request $request,
        DatabaseBackupService $backups,
        AuditLogService $auditLog
    ): RedirectResponse {
        try {
            $backup = $backups->create();

            $auditLog->log('database_backup_created', null, null, metadata: [
                'file_name' => $backup['file_name'],
                'size' => $backup['size'],
            ], request: $request);

            return back()->with('success', 'Database backup created: '.$backup['file_name'].' ('.$backup['size_for_humans'].').');
        } catch (Throwable $exception) {
            $auditLog->log('database_backup_failed', null, null, metadata: [
                'message' => app(SecretRedactionService::class)->redact($exception),
            ], request: $request);

            return back()->with('error', 'Backup failed. Review the audit log and server log for redacted details.');
        }
    }

    public function cleanupBackups(
        Request $request,
        DatabaseBackupService $backups,
        AuditLogService $auditLog
    ): RedirectResponse {
        $data = $request->validate([
            'keep' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $deleted = $backups->cleanup((int) ($data['keep'] ?? config('sanfaani.backups.retention_count', 10)));

        $auditLog->log('database_backup_cleanup', null, null, metadata: [
            'deleted' => $deleted,
            'deleted_count' => count($deleted),
        ], request: $request);

        return back()->with('success', count($deleted).' old backup file(s) removed.');
    }

    public function downloadBackup(
        Request $request,
        string $fileName,
        DatabaseBackupService $backups,
        AuditLogService $auditLog
    ): BinaryFileResponse {
        try {
            $path = $backups->pathFor($fileName);
        } catch (RuntimeException $exception) {
            abort(404, $exception->getMessage());
        }

        $auditLog->log('database_backup_downloaded', null, null, metadata: [
            'file_name' => basename($path),
        ], request: $request);

        $response = response()->download($path, basename($path), [
            'Content-Type' => 'application/sql',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');
        $response->headers->addCacheControlDirective('no-cache');
        $response->headers->addCacheControlDirective('must-revalidate');

        return $response;
    }

    private function run(Request $request, AuditLogService $auditLog, string $action, array $commands, string $success): RedirectResponse
    {
        $outputs = [];

        try {
            foreach ($commands as $command) {
                $exitCode = Artisan::call($command);
                $output = trim(Artisan::output());
                $outputs[$command] = $output;

                if ($exitCode !== 0 && ! ($command === 'storage:link' && str_contains($output, 'already exists'))) {
                    throw new RuntimeException($output ?: "Command {$command} failed.");
                }
            }

            $auditLog->log("system_maintenance_{$action}", null, null, metadata: [
                'commands' => $commands,
                'output' => $outputs,
            ], request: $request);

            return back()->with('success', $success);
        } catch (Throwable $exception) {
            $auditLog->log("system_maintenance_{$action}_failed", null, null, metadata: [
                'commands' => $commands,
                'message' => $exception->getMessage(),
            ], request: $request);

            return back()->with('error', 'Maintenance action failed: '.$exception->getMessage());
        }
    }
}
