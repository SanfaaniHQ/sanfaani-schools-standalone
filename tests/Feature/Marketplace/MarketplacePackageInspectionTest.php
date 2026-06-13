<?php

namespace Tests\Feature\Marketplace;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class MarketplacePackageInspectionTest extends TestCase
{
    private string $inspectionPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inspectionPath = storage_path('framework/testing/marketplace-package-inspection');
        File::deleteDirectory($this->inspectionPath);
        File::ensureDirectoryExists($this->inspectionPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->inspectionPath);

        parent::tearDown();
    }

    public function test_inspect_command_exists(): void
    {
        $this->assertArrayHasKey('marketplace:inspect-package', Artisan::all());
    }

    public function test_inspection_fails_package_containing_env(): void
    {
        $result = $this->inspectPackage($this->packageWith(['.env' => 'APP_KEY=secret']));

        $this->assertSame(1, $result['exit']);
        $this->assertStringContainsString('[FAIL] env_excluded', $result['output']);
        $this->assertStringContainsString('Package inspection failed.', $result['output']);
    }

    public function test_inspection_fails_package_containing_env_local(): void
    {
        $result = $this->inspectPackage($this->packageWith(['.env.local' => 'APP_KEY=secret']));

        $this->assertSame(1, $result['exit']);
        $this->assertStringContainsString('[FAIL] env_local_excluded', $result['output']);
        $this->assertStringContainsString('Package inspection failed.', $result['output']);
    }

    public function test_inspection_fails_package_containing_public_build_zip(): void
    {
        $result = $this->inspectPackage($this->packageWith(['public/build.zip' => 'nested zip']));

        $this->assertSame(1, $result['exit']);
        $this->assertStringContainsString('[FAIL] public_build_zip_excluded', $result['output']);
    }

    public function test_inspection_fails_package_containing_git(): void
    {
        $result = $this->inspectPackage($this->packageWith(['.git/config' => '[core]']));

        $this->assertSame(1, $result['exit']);
        $this->assertStringContainsString('[FAIL] git_excluded', $result['output']);
    }

    public function test_inspection_fails_package_containing_node_modules(): void
    {
        $result = $this->inspectPackage($this->packageWith(['node_modules/example/index.js' => 'module.exports = {};']));

        $this->assertSame(1, $result['exit']);
        $this->assertStringContainsString('[FAIL] node_modules_excluded', $result['output']);
    }

    public function test_inspection_warns_when_vendor_missing(): void
    {
        $entries = $this->safeEntries();
        unset($entries['vendor/autoload.php']);

        $result = $this->inspectPackage($this->packageWith($entries, includeSafeEntries: false, includeBuilderManifest: true));

        $this->assertSame(0, $result['exit'], $result['output']);
        $this->assertStringContainsString('[WARN] vendor_present', $result['output']);
        $this->assertStringContainsString('Package inspection passed.', $result['output']);
    }

    public function test_inspection_warns_when_public_build_manifest_missing(): void
    {
        $entries = $this->safeEntries();
        unset($entries['public/build/manifest.json']);

        $result = $this->inspectPackage($this->packageWith($entries, includeSafeEntries: false, includeBuilderManifest: true));

        $this->assertSame(0, $result['exit'], $result['output']);
        $this->assertStringContainsString('[WARN] public_build_manifest_present', $result['output']);
    }

    public function test_inspection_passes_safe_minimal_package(): void
    {
        $result = $this->inspectPackage($this->packageWith([], includeBuilderManifest: true));

        $this->assertSame(0, $result['exit'], $result['output']);
        $this->assertStringContainsString('[PASS] env_excluded', $result['output']);
        $this->assertStringContainsString('[PASS] env_local_excluded', $result['output']);
        $this->assertStringContainsString('[PASS] public_build_zip_excluded', $result['output']);
        $this->assertStringContainsString('[PASS] git_excluded', $result['output']);
        $this->assertStringContainsString('[PASS] node_modules_excluded', $result['output']);
        $this->assertStringContainsString('[PASS] builder_manifest_present', $result['output']);
        $this->assertStringContainsString('Package inspection passed.', $result['output']);
    }

    public function test_docs_include_standalone_qa_checklist(): void
    {
        $doc = $this->doc('docs/marketplace/standalone-package-qa-checklist.md');

        $this->assertStringContainsString('php artisan marketplace:build-package --profile=cpanel_ready', $doc);
        $this->assertStringContainsString('php artisan marketplace:inspect-package', $doc);
        $this->assertStringContainsString('.env', $doc);
        $this->assertStringContainsString('public/build.zip', $doc);
        $this->assertStringContainsString('/public', $doc);
    }

    public function test_docs_include_cpanel_ready_acceptance_test(): void
    {
        $doc = $this->doc('docs/marketplace/cpanel-ready-package-acceptance-test.md');

        $this->assertStringContainsString('vendor/', $doc);
        $this->assertStringContainsString('public/build/manifest.json', $doc);
        $this->assertStringContainsString('/install', $doc);
        $this->assertStringContainsString('DB_DATABASE', $doc);
        $this->assertStringContainsString('SANFAANI_DEPLOYMENT_MODE=single_school', $doc);
        $this->assertStringContainsString('SANFAANI_LICENSE_MODE=annual', $doc);
    }

    public function test_docs_explain_done_for_you_support(): void
    {
        $doc = $this->doc('docs/support/standalone-buyer-support-playbook.md');

        $this->assertStringContainsString('done-for-you', strtolower($doc));
        $this->assertStringContainsString('document root to `/public`', $doc);
        $this->assertStringContainsString('Do not upload `.env`', $doc);
        $this->assertStringContainsString('does not mean every host can be configured automatically', $doc);
    }

    public function test_docs_include_full_suite_validation_step(): void
    {
        $doc = $this->doc('docs/marketplace/standalone-package-qa-checklist.md');

        $this->assertStringContainsString('php artisan test', $doc);
        $this->assertStringContainsString('php artisan route:list', $doc);
        $this->assertStringContainsString('git diff --check', $doc);
    }

    /**
     * @param  array<string, string>  $entries
     */
    private function packageWith(array $entries, bool $includeSafeEntries = true, bool $includeBuilderManifest = false): string
    {
        $zipPath = $this->inspectionPath.DIRECTORY_SEPARATOR.uniqid('package-', true).'.zip';
        $zip = new ZipArchive;

        $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $this->assertTrue($opened === true, 'Unable to create test ZIP package.');

        $entries = $includeSafeEntries ? array_merge($this->safeEntries(), $entries) : $entries;

        foreach ($entries as $path => $contents) {
            $zip->addFromString($path, $contents);
        }

        $zip->close();

        if ($includeBuilderManifest) {
            File::put(
                $this->manifestPathFor($zipPath),
                json_encode([
                    'schema_version' => 1,
                    'package_name' => 'sanfaani-schools',
                    'profile' => 'cpanel_ready',
                    'zip_path' => $zipPath,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
            );
        }

        return $zipPath;
    }

    /**
     * @return array<string, string>
     */
    private function safeEntries(): array
    {
        return [
            'artisan' => '<?php',
            '.env.marketplace.example' => "SANFAANI_DEPLOYMENT_MODE=single_school\nSANFAANI_LICENSE_MODE=annual\n",
            'composer.json' => '{"name":"sanfaani/schools"}',
            'vendor/autoload.php' => '<?php',
            'public/build/manifest.json' => '{}',
            'docs/marketplace/standalone-package-qa-checklist.md' => 'Standalone Package QA Checklist',
            'docs/marketplace/cpanel-ready-package-acceptance-test.md' => 'cPanel Ready Package Acceptance Test',
            'docs/installation/standalone-buyer-installation-flow.md' => 'Standalone Buyer Installation Flow',
            'docs/installation/standalone-installer-acceptance-test.md' => 'Standalone Installer Acceptance Test',
            'docs/support/standalone-buyer-support-playbook.md' => 'Standalone Buyer Support Playbook',
        ];
    }

    /**
     * @return array{exit: int, output: string}
     */
    private function inspectPackage(string $zipPath): array
    {
        $exitCode = Artisan::call('marketplace:inspect-package', [
            'path' => $zipPath,
        ]);

        return [
            'exit' => $exitCode,
            'output' => Artisan::output(),
        ];
    }

    private function manifestPathFor(string $zipPath): string
    {
        return dirname($zipPath).DIRECTORY_SEPARATOR.pathinfo($zipPath, PATHINFO_FILENAME).'.manifest.json';
    }

    private function doc(string $path): string
    {
        $absolutePath = base_path($path);

        $this->assertFileExists($absolutePath);

        return File::get($absolutePath);
    }
}
