<?php

namespace App\Services\Standalone;

use App\Services\System\DeploymentModeService;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class StandaloneEditionService
{
    public function __construct(
        private DeploymentModeService $deployment,
    ) {}

    public function productEdition(): string
    {
        return $this->normalize(config('standalone.product_edition', 'standalone')) ?: 'standalone';
    }

    public function productEditionLabel(): string
    {
        return $this->isStandalone() ? 'Standalone School' : str($this->productEdition())->replace('_', ' ')->title()->toString();
    }

    public function isStandalone(): bool
    {
        return $this->productEdition() === 'standalone';
    }

    public function isStandaloneMode(): bool
    {
        return $this->isStandalone()
            && $this->deploymentMode() === DeploymentModeService::MODE_SINGLE_SCHOOL;
    }

    public function deploymentMode(): string
    {
        try {
            return $this->deployment->mode();
        } catch (InvalidArgumentException) {
            return $this->normalize(config('sanfaani.deployment.mode', $this->defaultDeploymentMode()));
        }
    }

    public function defaultDeploymentMode(): string
    {
        return $this->normalize(config('standalone.deployment_mode', DeploymentModeService::MODE_SINGLE_SCHOOL))
            ?: DeploymentModeService::MODE_SINGLE_SCHOOL;
    }

    public function licenseMode(): string
    {
        try {
            return $this->deployment->licenseMode();
        } catch (InvalidArgumentException) {
            return $this->normalize(config('sanfaani.deployment.license_mode', $this->defaultLicenseMode()));
        }
    }

    public function defaultLicenseMode(): string
    {
        return $this->normalize(config('standalone.license_mode', DeploymentModeService::LICENSE_ANNUAL))
            ?: DeploymentModeService::LICENSE_ANNUAL;
    }

    public function installerShouldBeEnabled(): bool
    {
        return (bool) config('standalone.installer_enabled', true)
            && (bool) config('installer.enabled', true);
    }

    public function installed(): bool
    {
        return (bool) config('standalone.installed', false)
            || File::exists(storage_path('app/'.ltrim((string) config('installer.lock_file', 'installed.lock'), '/\\')));
    }

    public function offlineMode(): string
    {
        return $this->normalize(config('standalone.offline_mode', 'local_first')) ?: 'local_first';
    }

    public function localFirstOfflineEnabled(): bool
    {
        return $this->offlineMode() === 'local_first';
    }

    public function cloudSyncEnabled(): bool
    {
        return (bool) config('standalone.sync.enabled', false);
    }

    public function syncEndpoint(): ?string
    {
        $endpoint = trim((string) config('standalone.sync.endpoint', ''));

        return $endpoint === '' ? null : $endpoint;
    }

    public function syncTokenConfigured(): bool
    {
        return trim((string) config('standalone.sync.token', '')) !== '';
    }

    public function backupSyncEnabled(): bool
    {
        return (bool) config('standalone.sync.backup_enabled', false);
    }

    public function pwaOfflineCaptureEnabled(): bool
    {
        return (bool) config('standalone.pwa_offline.capture_enabled', false);
    }

    public function pwaOfflineSyncEnabled(): bool
    {
        return (bool) config('standalone.pwa_offline.sync_enabled', false);
    }

    public function pwaOfflineAllowedModules(): array
    {
        return collect((array) config('standalone.pwa_offline.allowed_modules', []))
            ->map(fn (mixed $module): string => $this->normalize($module))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function pwaOfflineModuleAllowed(string $module): bool
    {
        return in_array($this->normalize($module), $this->pwaOfflineAllowedModules(), true);
    }

    public function offlineAttendanceCaptureEnabled(): bool
    {
        return $this->pwaOfflineCaptureEnabled()
            && $this->pwaOfflineModuleAllowed('attendance');
    }

    public function offlineAttendanceSyncEnabled(): bool
    {
        return $this->offlineAttendanceCaptureEnabled()
            && $this->pwaOfflineSyncEnabled();
    }

    public function recommendedEnvironment(): array
    {
        return (array) config('standalone.recommended_env', []);
    }

    public function demotedFlows(): array
    {
        return (array) config('standalone.demoted_flows', []);
    }

    public function surfaceGates(): array
    {
        return (array) config('standalone.surface_gates', []);
    }

    public function standaloneNavigationEnabled(): bool
    {
        return $this->isStandaloneMode()
            && (bool) data_get($this->surfaceGates(), 'standalone_navigation_enabled', true);
    }

    public function privateHomepageEnabled(): bool
    {
        return $this->isStandaloneMode()
            && (bool) data_get($this->surfaceGates(), 'private_homepage_enabled', true);
    }

    public function hidesSaasSurfaces(): bool
    {
        return $this->isStandaloneMode()
            && (bool) data_get($this->surfaceGates(), 'hide_saas_surfaces', true);
    }

    public function hidesMarketplaceSurfaces(): bool
    {
        return $this->isStandaloneMode()
            && (bool) data_get($this->surfaceGates(), 'hide_marketplace_surfaces', true);
    }

    public function hidesDemoSurfaces(): bool
    {
        return $this->isStandaloneMode()
            && (bool) data_get($this->surfaceGates(), 'hide_demo_surfaces', true);
    }

    public function hidesPlatformMarketingSurfaces(): bool
    {
        return $this->isStandaloneMode()
            && (bool) data_get($this->surfaceGates(), 'hide_platform_marketing_surfaces', true);
    }

    public function warnings(): array
    {
        $warnings = [];

        if (! $this->isStandalone()) {
            $warnings[] = 'Product edition is not configured as standalone.';
        }

        if ($this->isStandalone() && $this->deploymentMode() === DeploymentModeService::MODE_SAAS) {
            $warnings[] = 'Standalone edition is currently using SaaS deployment mode; use single_school for private school installations.';
        }

        if ($this->isStandalone() && $this->licenseMode() === DeploymentModeService::LICENSE_SUBSCRIPTION) {
            $warnings[] = 'Standalone edition is currently using subscription license mode; annual or lifetime is recommended.';
        }

        if ($this->isStandalone() && $this->deploymentMode() === DeploymentModeService::MODE_SAAS && (bool) config('features.features.saas_billing.enabled', false)) {
            $warnings[] = 'SaaS billing is visible in the current deployment behavior and should not be the standalone main flow.';
        }

        if ($this->isStandalone() && ((bool) config('demo.enabled', false) || (bool) config('sanfaani.deployment.demo_enabled', false))) {
            $warnings[] = 'Demo/customer acquisition mode is enabled; standalone owners should use installer, license activation, and the local dashboard as the main flow.';
        }

        if ($this->isStandalone() && (bool) config('demo.marketplace.enabled', false)) {
            $warnings[] = 'Marketplace live demo is enabled; it should stay demoted for standalone private school installations.';
        }

        if ($this->cloudSyncEnabled() && (! $this->syncEndpoint() || ! $this->syncTokenConfigured())) {
            $warnings[] = 'Standalone sync is enabled but endpoint or token is missing.';
        }

        return $warnings;
    }

    public function status(): array
    {
        return [
            'product_edition' => $this->productEdition(),
            'product_label' => $this->productEditionLabel(),
            'deployment_mode' => $this->deploymentMode(),
            'recommended_deployment_mode' => $this->defaultDeploymentMode(),
            'installer_enabled' => $this->installerShouldBeEnabled(),
            'installed' => $this->installed(),
            'license_mode' => $this->licenseMode(),
            'recommended_license_mode' => $this->defaultLicenseMode(),
            'offline_mode' => $this->offlineMode(),
            'local_first_offline_enabled' => $this->localFirstOfflineEnabled(),
            'sync_enabled' => $this->cloudSyncEnabled(),
            'sync_endpoint' => $this->syncEndpoint(),
            'sync_endpoint_configured' => $this->syncEndpoint() !== null,
            'sync_token_configured' => $this->syncTokenConfigured(),
            'backup_sync_enabled' => $this->backupSyncEnabled(),
            'pwa_offline_capture_enabled' => $this->pwaOfflineCaptureEnabled(),
            'pwa_offline_sync_enabled' => $this->pwaOfflineSyncEnabled(),
            'pwa_offline_allowed_modules' => $this->pwaOfflineAllowedModules(),
            'offline_attendance_capture_enabled' => $this->offlineAttendanceCaptureEnabled(),
            'offline_attendance_sync_enabled' => $this->offlineAttendanceSyncEnabled(),
            'recommended_env' => $this->recommendedEnvironment(),
            'demoted_flows' => $this->demotedFlows(),
            'surface_gates' => $this->surfaceGates(),
            'warnings' => $this->warnings(),
        ];
    }

    private function normalize(mixed $value): string
    {
        return str((string) $value)
            ->trim()
            ->lower()
            ->replace(['-', ' '], '_')
            ->toString();
    }
}
