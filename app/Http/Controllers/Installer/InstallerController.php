<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\Installer\InstallerDatabaseService;
use App\Services\Installer\InstallerRequirementsService;
use App\Services\Installer\InstallerSetupService;
use App\Services\Installer\InstallerStateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class InstallerController extends Controller
{
    public function __construct(
        private InstallerStateService $state,
        private InstallerRequirementsService $requirements,
        private InstallerDatabaseService $database,
        private InstallerSetupService $setup,
        private AuditLogService $auditLog,
    ) {}

    public function welcome(): View
    {
        return $this->view('welcome', ['step' => 'welcome']);
    }

    public function requirements(): View
    {
        $checks = $this->requirements->requirements();
        $this->auditInstallerCheck('requirements', $checks);

        return $this->view('requirements', [
            'step' => 'requirements',
            'checks' => $checks,
        ]);
    }

    public function permissions(): View
    {
        $checks = $this->requirements->permissions();
        $this->auditInstallerCheck('permissions', $checks);

        return $this->view('permissions', [
            'step' => 'permissions',
            'checks' => $checks,
        ]);
    }

    public function database(): View
    {
        $status = $this->database->status();
        $this->safeAudit('installer_check_ran', [
            'check' => 'database',
            'deployment_mode' => config('sanfaani.deployment.mode'),
            'installer_enabled' => (bool) config('installer.enabled', false),
            'connected' => (bool) $status['connected'],
            'pending_migrations_count' => $status['pending_migrations_count'],
        ]);

        return $this->view('database', [
            'step' => 'database',
            'status' => $status,
            'migrationCheck' => $this->requirements->migrationReadiness($status['pending_migrations_count']),
        ]);
    }

    public function environment(): View
    {
        $checks = $this->requirements->environment();
        $this->auditInstallerCheck('environment', $checks);

        return $this->view('environment', [
            'step' => 'environment',
            'checks' => $checks,
        ]);
    }

    public function appKey(): View
    {
        $check = $this->requirements->appKeyStatus();
        $this->auditInstallerCheck('app_key', [$check]);

        return $this->view('app-key', [
            'step' => 'app-key',
            'check' => $check,
        ]);
    }

    public function migrations(): View
    {
        $status = $this->database->status();
        $check = $this->requirements->migrationReadiness($status['pending_migrations_count']);
        $this->auditInstallerCheck('migrations', [$check]);

        return $this->view('migrations', [
            'step' => 'migrations',
            'status' => $status,
            'check' => $check,
        ]);
    }

    public function admin(): View
    {
        return $this->view('admin', [
            'step' => 'admin',
            'admin' => Session::get('installer.admin', []),
        ]);
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $emailRules = ['required', 'email', 'max:255'];

        if ($this->usersTableReady()) {
            $emailRules[] = Rule::unique('users', 'email');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        Session::put('installer.admin', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
        ]);

        return redirect()->route('installer.school');
    }

    public function school(): View
    {
        return $this->view('school', [
            'step' => 'school',
            'school' => Session::get('installer.school', []),
        ]);
    }

    public function storeSchool(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'alpha_dash', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'school_motto' => ['nullable', 'string', 'max:255'],
        ]);

        Session::put('installer.school', $data);

        return redirect()->route('installer.smtp');
    }

    public function smtp(): View
    {
        return $this->view('smtp', [
            'step' => 'smtp',
            'smtp' => Session::get('installer.smtp', []),
        ]);
    }

    public function storeSmtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mailer' => ['nullable', 'in:smtp,log,array'],
            'host' => ['required_if:mailer,smtp', 'nullable', 'string', 'max:255', 'not_regex:/[\s\/:\\\\\r\n]/'],
            'port' => ['required_if:mailer,smtp', 'nullable', 'integer', 'between:1,65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'required_with:username', 'string', 'max:2000'],
            'encryption' => ['required_if:mailer,smtp', 'nullable', 'in:tls,ssl,none'],
            'from_address' => ['required_if:mailer,smtp', 'nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:160', 'not_regex:/[\r\n]/'],
        ]);

        Session::put('installer.smtp', [
            'mailer' => $data['mailer'] ?? 'log',
            'host' => $data['host'] ?? null,
            'port' => $data['port'] ?? null,
            'username' => $data['username'] ?? null,
            'password' => filled($data['password'] ?? null) ? Crypt::encryptString($data['password']) : null,
            'password_encrypted' => filled($data['password'] ?? null),
            'password_provided' => filled($data['password'] ?? null),
            'encryption' => $data['encryption'] ?? null,
            'from_address' => $data['from_address'] ?? null,
            'from_name' => $data['from_name'] ?? null,
        ]);

        return redirect()->route('installer.review');
    }

    public function review(): View|RedirectResponse
    {
        if (! Session::has('installer.admin') || ! Session::has('installer.school')) {
            return redirect()->route('installer.admin')
                ->with('error', 'Complete the owner and school setup before final review.');
        }

        $database = $this->database->status();
        $this->safeAudit('installer_status_viewed', [
            'step' => 'review',
            'deployment_mode' => config('sanfaani.deployment.mode'),
            'installer_enabled' => (bool) config('installer.enabled', false),
            'database_connected' => (bool) $database['connected'],
            'pending_migrations_count' => $database['pending_migrations_count'],
        ]);

        return $this->view('review', [
            'step' => 'review',
            'admin' => Session::get('installer.admin'),
            'school' => Session::get('installer.school'),
            'smtp' => Session::get('installer.smtp', []),
            'database' => $database,
            'diagnostics' => $this->requirements->diagnostics($database),
        ]);
    }

    public function complete(): View|RedirectResponse
    {
        if (! Session::has('installer.admin') || ! Session::has('installer.school')) {
            return redirect()->route('installer.admin')
                ->with('error', 'Complete the installer forms before finalizing.');
        }

        $this->safeAudit('installer_completion_attempted', [
            'deployment_mode' => config('sanfaani.deployment.mode'),
            'installer_enabled' => (bool) config('installer.enabled', false),
            'admin_email_present' => filled(Session::get('installer.admin.email')),
            'school_name_present' => filled(Session::get('installer.school.name')),
        ]);

        try {
            $result = $this->setup->finalizeInstallation(
                Session::get('installer.admin'),
                Session::get('installer.school'),
                ['smtp_placeholder' => $this->installerSmtpForSetup()]
            );
        } catch (RuntimeException $exception) {
            $this->safeAudit('installer_completion_failed', [
                'deployment_mode' => config('sanfaani.deployment.mode'),
                'failed_reason_code' => 'finalization_error',
            ]);

            return redirect()->route('installer.review')
                ->with('error', $exception->getMessage());
        }

        $this->safeAudit('installer_completion_succeeded', [
            'deployment_mode' => config('sanfaani.deployment.mode'),
            'school_id' => $result['school']->id,
            'admin_user_id' => $result['admin']->id,
        ]);

        Session::forget('installer');

        return view('installer.complete', [
            'step' => 'complete',
            'steps' => config('installer.steps', []),
            'result' => $result,
            'metadata' => $this->state->installationMetadata(),
            'lockPath' => $this->state->lockPath(),
            'lockLabel' => $this->lockLabel(),
            'diagnostics' => $this->requirements->diagnostics($this->database->status()),
        ]);
    }

    private function installerSmtpForSetup(): array
    {
        $smtp = (array) Session::get('installer.smtp', []);

        if (! ($smtp['password_encrypted'] ?? false) || ! filled($smtp['password'] ?? null)) {
            $smtp['password'] = null;

            return $smtp;
        }

        try {
            $smtp['password'] = Crypt::decryptString($smtp['password']);
        } catch (Throwable) {
            throw new RuntimeException('The installer SMTP password can no longer be decrypted. Re-enter it before completing setup.');
        }

        unset($smtp['password_encrypted']);

        return $smtp;
    }

    private function view(string $view, array $data = []): View
    {
        return view('installer.'.$view, array_merge($data, [
            'steps' => config('installer.steps', []),
            'lockPath' => $this->state->lockPath(),
            'lockLabel' => $this->lockLabel(),
        ]));
    }

    private function auditInstallerCheck(string $check, array $checks): void
    {
        $statuses = collect($checks)->countBy('status');

        $this->safeAudit('installer_check_ran', [
            'check' => $check,
            'deployment_mode' => config('sanfaani.deployment.mode'),
            'installer_enabled' => (bool) config('installer.enabled', false),
            'pass_count' => (int) $statuses->get('pass', 0),
            'warning_count' => (int) $statuses->get('warning', 0),
            'fail_count' => (int) $statuses->get('fail', 0),
        ]);
    }

    private function safeAudit(string $action, array $metadata = []): void
    {
        try {
            if (! Schema::hasTable('audit_logs')) {
                return;
            }

            $this->auditLog->log($action, metadata: $metadata);
        } catch (Throwable) {
            //
        }
    }

    private function lockLabel(): string
    {
        return 'storage/app/'.ltrim((string) config('installer.lock_file', 'installed.lock'), '/\\');
    }

    private function usersTableReady(): bool
    {
        try {
            return Schema::hasTable('users');
        } catch (Throwable) {
            return false;
        }
    }
}
