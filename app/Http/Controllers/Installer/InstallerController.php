<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Services\Installer\InstallerDatabaseService;
use App\Services\Installer\InstallerRequirementsService;
use App\Services\Installer\InstallerSetupService;
use App\Services\Installer\InstallerStateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use RuntimeException;

class InstallerController extends Controller
{
    public function __construct(
        private InstallerStateService $state,
        private InstallerRequirementsService $requirements,
        private InstallerDatabaseService $database,
        private InstallerSetupService $setup,
    ) {}

    public function welcome(): View
    {
        return $this->view('welcome', ['step' => 'welcome']);
    }

    public function requirements(): View
    {
        return $this->view('requirements', [
            'step' => 'requirements',
            'checks' => $this->requirements->requirements(),
        ]);
    }

    public function permissions(): View
    {
        return $this->view('permissions', [
            'step' => 'permissions',
            'checks' => $this->requirements->permissions(),
        ]);
    }

    public function database(): View
    {
        $status = $this->database->status();

        return $this->view('database', [
            'step' => 'database',
            'status' => $status,
            'migrationCheck' => $this->requirements->migrationReadiness($status['pending_migrations_count']),
        ]);
    }

    public function environment(): View
    {
        return $this->view('environment', [
            'step' => 'environment',
            'checks' => $this->requirements->environment(),
        ]);
    }

    public function appKey(): View
    {
        return $this->view('app-key', [
            'step' => 'app-key',
            'check' => $this->requirements->appKeyStatus(),
        ]);
    }

    public function migrations(): View
    {
        $status = $this->database->status();

        return $this->view('migrations', [
            'step' => 'migrations',
            'status' => $status,
            'check' => $this->requirements->migrationReadiness($status['pending_migrations_count']),
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
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
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
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'between:1,65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'in:tls,ssl,none'],
            'from_address' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
        ]);

        Session::put('installer.smtp', [
            'mailer' => $data['mailer'] ?? 'log',
            'host' => $data['host'] ?? null,
            'port' => $data['port'] ?? null,
            'username' => filled($data['username'] ?? null) ? 'configured' : null,
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

        return $this->view('review', [
            'step' => 'review',
            'admin' => Session::get('installer.admin'),
            'school' => Session::get('installer.school'),
            'smtp' => Session::get('installer.smtp', []),
            'database' => $this->database->status(),
        ]);
    }

    public function complete(): View|RedirectResponse
    {
        if (! Session::has('installer.admin') || ! Session::has('installer.school')) {
            return redirect()->route('installer.admin')
                ->with('error', 'Complete the installer forms before finalizing.');
        }

        try {
            $result = $this->setup->finalizeInstallation(
                Session::get('installer.admin'),
                Session::get('installer.school'),
                ['smtp_placeholder' => Session::get('installer.smtp', [])]
            );
        } catch (RuntimeException $exception) {
            return redirect()->route('installer.review')
                ->with('error', $exception->getMessage());
        }

        Session::forget('installer');

        return view('installer.complete', [
            'step' => 'complete',
            'steps' => config('installer.steps', []),
            'result' => $result,
            'metadata' => $this->state->installationMetadata(),
            'lockPath' => $this->state->lockPath(),
        ]);
    }

    private function view(string $view, array $data = []): View
    {
        return view('installer.'.$view, array_merge($data, [
            'steps' => config('installer.steps', []),
            'lockPath' => $this->state->lockPath(),
        ]));
    }
}
