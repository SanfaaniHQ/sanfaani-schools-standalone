<?php

namespace App\Services\Installer;

use App\Services\System\DeploymentModeService;
use Illuminate\Support\Facades\File;
use Throwable;

class InstallerStateService
{
    public function __construct(
        private DeploymentModeService $deployment,
    ) {}

    public function isInstalled(): bool
    {
        return (bool) config('sanfaani.deployment.installed', true)
            || File::exists($this->lockPath());
    }

    public function markInstalled(array $metadata = []): bool
    {
        File::ensureDirectoryExists(dirname($this->lockPath()));

        $payload = array_merge([
            'installed_at' => now()->toIso8601String(),
            'app_version' => config('version.version', 'unknown'),
            'deployment_mode' => $this->deployment->mode(),
            'license_mode' => $this->deployment->licenseMode(),
        ], $metadata);

        return File::put($this->lockPath(), json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
    }

    public function lockPath(): string
    {
        return storage_path('app/'.ltrim((string) config('installer.lock_file', 'installed.lock'), '/\\'));
    }

    public function installationMetadata(): array
    {
        if (! File::exists($this->lockPath())) {
            return [];
        }

        $metadata = json_decode((string) File::get($this->lockPath()), true);

        return is_array($metadata) ? $metadata : [];
    }

    public function canAccessInstaller(): bool
    {
        try {
            if (! (bool) config('installer.enabled', false) || $this->isInstalled()) {
                return false;
            }

            if (! $this->modeAllowsInstaller()) {
                return false;
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function modeAllowsInstaller(): bool
    {
        $mode = $this->deployment->mode();
        $allowedModes = collect((array) config('installer.allowed_modes', []))
            ->map(fn (mixed $value): string => str((string) $value)->trim()->lower()->replace(['-', ' '], '_')->toString())
            ->filter()
            ->values()
            ->all();

        if (in_array($mode, $allowedModes, true)) {
            return true;
        }

        return $mode === DeploymentModeService::MODE_MANAGED
            && (bool) config('installer.allow_managed', false);
    }
}
