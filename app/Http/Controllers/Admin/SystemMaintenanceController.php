<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use Throwable;

class SystemMaintenanceController extends Controller
{
    public function index()
    {
        return view('admin.system-maintenance.index');
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
