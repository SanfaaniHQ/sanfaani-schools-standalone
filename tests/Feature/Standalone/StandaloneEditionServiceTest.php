<?php

namespace Tests\Feature\Standalone;

use App\Services\Standalone\StandaloneEditionService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StandaloneEditionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        File::delete(storage_path('app/installed.lock'));

        config([
            'standalone.product_edition' => 'standalone',
            'standalone.deployment_mode' => 'single_school',
            'standalone.installer_enabled' => true,
            'standalone.installed' => false,
            'standalone.license_mode' => 'annual',
            'standalone.offline_mode' => 'local_first',
            'standalone.sync.enabled' => false,
            'standalone.sync.endpoint' => '',
            'standalone.sync.token' => '',
            'standalone.sync.backup_enabled' => false,
            'installer.enabled' => true,
            'sanfaani.deployment.mode' => 'single_school',
            'sanfaani.deployment.license_mode' => 'annual',
        ]);
    }

    protected function tearDown(): void
    {
        File::delete(storage_path('app/installed.lock'));

        parent::tearDown();
    }

    public function test_standalone_config_exists_and_exposes_safe_defaults(): void
    {
        $service = app(StandaloneEditionService::class);

        $this->assertIsArray(config('standalone'));
        $this->assertSame('standalone', $service->productEdition());
        $this->assertSame('Standalone School', $service->productEditionLabel());
        $this->assertSame('single_school', $service->defaultDeploymentMode());
        $this->assertTrue($service->installerShouldBeEnabled());
        $this->assertSame('annual', $service->defaultLicenseMode());
        $this->assertSame('local_first', $service->offlineMode());
        $this->assertTrue($service->localFirstOfflineEnabled());
        $this->assertFalse($service->cloudSyncEnabled());
        $this->assertNull($service->syncEndpoint());
        $this->assertFalse($service->backupSyncEnabled());
        $this->assertSame('single_school', $service->recommendedEnvironment()['SANFAANI_DEPLOYMENT_MODE']);
        $this->assertTrue($service->standaloneNavigationEnabled());
        $this->assertTrue($service->privateHomepageEnabled());
        $this->assertTrue($service->hidesSaasSurfaces());
        $this->assertTrue($service->hidesMarketplaceSurfaces());
        $this->assertTrue($service->hidesDemoSurfaces());
        $this->assertTrue($service->hidesPlatformMarketingSurfaces());
    }

    public function test_service_warns_when_saas_or_demo_modes_are_enabled_for_standalone(): void
    {
        config([
            'sanfaani.deployment.mode' => 'saas',
            'sanfaani.deployment.license_mode' => 'subscription',
            'demo.enabled' => true,
            'demo.marketplace.enabled' => true,
        ]);

        $warnings = implode(' ', app(StandaloneEditionService::class)->warnings());

        $this->assertStringContainsString('SaaS deployment mode', $warnings);
        $this->assertStringContainsString('subscription license mode', $warnings);
        $this->assertStringContainsString('Demo/customer acquisition mode is enabled', $warnings);
        $this->assertStringContainsString('Marketplace live demo is enabled', $warnings);
    }

    public function test_standalone_docs_exist_and_state_product_boundaries(): void
    {
        $docs = [
            'docs/standalone/standalone-edition-overview.md',
            'docs/standalone/local-first-offline-use.md',
            'docs/standalone/sync-architecture.md',
            'docs/standalone/installer-and-license-flow.md',
            'docs/standalone/standalone-vs-saas-vs-marketplace.md',
            'docs/standalone/roadmap.md',
        ];

        foreach ($docs as $doc) {
            $this->assertFileExists(base_path($doc));
        }

        $contents = strtolower(collect($docs)->map(fn (string $doc): string => File::get(base_path($doc)))->implode("\n"));

        $this->assertStringContainsString('full browser offline/pwa is not complete yet', $contents);
        $this->assertStringContainsString('local database is the source of truth', $contents);
        $this->assertStringContainsString('saas billing', $contents);
        $this->assertStringContainsString('not the main standalone flow', $contents);
        $this->assertStringContainsString('done-for-you', $contents);
    }
}
