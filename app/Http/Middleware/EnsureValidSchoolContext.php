<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Services\AuditService;
use App\Services\CurrentSchoolService;
use App\Services\SchoolMailConfigService;
use App\Services\TenantContext;
use App\Services\UserWorkspaceService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureValidSchoolContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if (app(CurrentSchoolService::class)->inSupportMode($user)) {
            $school = School::where('status', 'active')->find(session('support_school_id'));

            if (! $school) {
                $this->clearSupportSession();

                return redirect()->route('admin.schools.index')
                    ->with('error', 'Support access is no longer valid.');
            }

            TenantContext::set($school->id, session('support_role_context', 'school_admin'));
            $response = $this->withSchoolMailConfig($school->id, fn () => $next($request));
            $this->logSupportAccess($request, $school->id);

            return $response;
        }

        if (session('is_support_session') || session()->has('support_school_id')) {
            $this->clearSupportSession();
        }

        $workspaces = app(UserWorkspaceService::class);

        if (TenantContext::workspaceType() === TenantContext::WORKSPACE_INSTALLATION_ADMIN) {
            if ($user->hasRole('super_admin')) {
                return redirect()->route('workspace.create')
                    ->with('error', 'Switch to a school workspace before opening school pages.');
            }

            session()->forget(['workspace.type', 'workspace.key']);
        }

        $context = $workspaces->activeSchoolContextFor($user);

        if (! $context) {
            $hadStoredContext = TenantContext::workspaceKey()
                || TenantContext::schoolId()
                || TenantContext::roleName();

            if ($hadStoredContext) {
                TenantContext::clear();

                return redirect()->route('workspace.create')
                    ->with('error', 'Your previous school workspace is no longer valid. Choose an assigned workspace.');
            }

            $schoolContexts = $workspaces->schoolContextsFor($user);

            if ($schoolContexts->count() === 1) {
                $context = $schoolContexts->first();
                $workspaces->select($user, $context);
            } else {
                TenantContext::clear();

                return redirect()->route('workspace.create')
                    ->with('error', 'Choose a valid school workspace before continuing.');
            }
        }

        $schoolId = (int) $context['school_id'];
        $roleName = (string) $context['role_name'];

        $school = School::where('status', 'active')->find($schoolId);

        if (! $school || ! TenantContext::userBelongsToSchool($user, $schoolId)) {
            TenantContext::clear();

            return redirect()->route('workspace.create')
                ->with('error', 'Your school context is no longer valid.');
        }

        if (! $this->roleBelongsToSchoolContext($user, $roleName, $schoolId)) {
            TenantContext::clear();

            return redirect()->route('workspace.create')
                ->with('error', 'Choose a valid role for this school workspace.');
        }

        TenantContext::set($school->id, $roleName);

        return $this->withSchoolMailConfig($school->id, fn () => $next($request));
    }

    private function withSchoolMailConfig(int $schoolId, Closure $callback): Response
    {
        $original = config('mail');

        try {
            SchoolMailConfigService::configure($schoolId);

            return $callback();
        } finally {
            Config::set('mail', $original);
            app(MailManager::class)->forgetMailers();
        }
    }

    private function roleBelongsToSchoolContext($user, string $roleName, int $schoolId): bool
    {
        if (! in_array($roleName, UserWorkspaceService::SCHOOL_WORKSPACE_ROLES, true)
            || ! $user->hasRole($roleName)) {
            return false;
        }

        if ($user->schoolRoles()->exists()) {
            return $user->activeSchoolRoles()
                ->where('school_id', $schoolId)
                ->where('role_name', $roleName)
                ->exists();
        }

        return (int) $user->school_id === $schoolId;
    }

    private function clearSupportSession(): void
    {
        session()->forget([
            'is_support_session',
            'support_school_id',
            'support_role_context',
            'support_reason',
            'support_access_started_by',
            'support_access_started_at',
            'support_access_last_confirmed_at',
        ]);

        TenantContext::clear();
    }

    private function logSupportAccess(Request $request, int $schoolId): void
    {
        $payload = [
            'school_id' => $schoolId,
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'path' => $request->path(),
            'reason' => session('support_reason'),
        ];

        try {
            if (Schema::hasTable('support_access_logs')) {
                DB::table('support_access_logs')->insert([
                    'impersonator_id' => $request->user()?->id,
                    'target_school_id' => $schoolId,
                    'action' => $payload['route'] ?: $payload['method'].' '.$payload['path'],
                    'reason' => session('support_reason'),
                    'ip' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                    'payload' => json_encode($payload),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (Throwable) {
            //
        }

        AuditService::log('support', 'support_route_accessed', $payload);
    }
}
