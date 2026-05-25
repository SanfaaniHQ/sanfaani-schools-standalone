<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\Security\EmailSafetyService;
use App\Services\Security\LoggingSafetyService;
use App\Services\Security\ProductionReadinessService;
use App\Services\Security\SecurityAuditService;
use App\Services\Security\TokenSafetyService;
use App\Services\System\DeploymentModeService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SecurityDiagnosticsController extends Controller
{
    public function __construct(
        private SecurityAuditService $audit,
        private EmailSafetyService $email,
        private LoggingSafetyService $logging,
        private TokenSafetyService $tokens,
        private ProductionReadinessService $production,
        private DeploymentModeService $deployment,
    ) {}

    public function index(Request $request): View
    {
        [$logs, $summary] = $this->auditLogs($request);

        return view('admin.security.index', [
            'label' => $this->label(),
            'report' => $this->audit->report(),
            'logs' => $logs,
            'summary' => $summary,
            'filters' => $request->only(['search', 'severity']),
        ]);
    }

    public function audit(): View
    {
        return view('admin.security.audit', [
            'label' => $this->label(),
            'report' => $this->audit->report(),
        ]);
    }

    public function email(): View
    {
        return view('admin.security.email', [
            'label' => $this->label(),
            'checks' => $this->email->checks(),
        ]);
    }

    public function logging(): View
    {
        return view('admin.security.logging', [
            'label' => $this->label(),
            'checks' => $this->logging->checks(),
        ]);
    }

    public function tokens(): View
    {
        return view('admin.security.tokens', [
            'label' => $this->label(),
            'checks' => $this->tokens->checks(),
        ]);
    }

    public function production(): View
    {
        return view('admin.security.production', [
            'label' => $this->label(),
            'checks' => $this->production->checks(),
        ]);
    }

    private function label(): string
    {
        return (string) config(
            'security.labels.'.$this->deployment->mode(),
            str($this->deployment->mode())->replace('_', ' ')->title().' Security',
        );
    }

    private function auditLogs(Request $request): array
    {
        $logs = new LengthAwarePaginator([], 0, 25);
        $summary = [
            'critical' => 0,
            'warnings' => 0,
            'failed_logins' => 0,
            'permission_events' => 0,
        ];

        if (! Schema::hasTable('audit_logs')) {
            return [$logs, $summary];
        }

        $baseQuery = $this->securityQuery($request);
        $logs = (clone $baseQuery)
            ->with(['user', 'school'])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'critical' => (clone $baseQuery)->where('severity', 'critical')->count(),
            'warnings' => (clone $baseQuery)->whereIn('severity', ['warning', 'critical'])->count(),
            'failed_logins' => (clone $baseQuery)->where(function ($query) {
                $query->where('action', 'like', '%failed_login%')
                    ->orWhere('event', 'like', '%failed_login%')
                    ->orWhere('action', 'like', '%login_failed%')
                    ->orWhere('event', 'like', '%login_failed%');
            })->count(),
            'permission_events' => (clone $baseQuery)->where(function ($query) {
                $query->where('action', 'like', '%permission%')
                    ->orWhere('event', 'like', '%permission%')
                    ->orWhere('category', 'like', '%permission%');
            })->count(),
        ];

        return [$logs, $summary];
    }

    private function securityQuery(Request $request)
    {
        return AuditLog::query()
            ->where(function ($query) {
                $query->whereIn('category', ['security', 'auth', 'authentication', 'permission', 'impersonation'])
                    ->orWhere('action', 'like', '%login%')
                    ->orWhere('action', 'like', '%permission%')
                    ->orWhere('action', 'like', '%security%')
                    ->orWhere('action', 'like', '%impersonat%')
                    ->orWhere('event', 'like', '%login%')
                    ->orWhere('event', 'like', '%permission%')
                    ->orWhere('event', 'like', '%security%')
                    ->orWhere('event', 'like', '%impersonat%');
            })
            ->when($request->filled('severity'), fn ($query) => $query->where('severity', $request->input('severity')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($query) use ($search) {
                    $query->where('action', 'like', "%{$search}%")
                        ->orWhere('event', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
                });
            });
    }
}
