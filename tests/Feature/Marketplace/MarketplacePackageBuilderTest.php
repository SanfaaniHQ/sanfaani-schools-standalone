<?php

namespace Tests\Feature\Marketplace;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MarketplacePackageBuilderTest extends TestCase
{
    private string $packageOutputPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->packageOutputPath = storage_path('framework/testing/marketplace-packages');
        File::deleteDirectory($this->packageOutputPath);
        File::ensureDirectoryExists($this->packageOutputPath);

        config(['packaging.output_path' => $this->packageOutputPath]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->packageOutputPath);

        parent::tearDown();
    }

    public function test_marketplace_package_builder_foundation(): void
    {
        $this->assertArrayHasKey('marketplace:build-package', Artisan::all());

        $technical = $this->dryRunManifest('technical');

        $this->assertContains('.env', $technical['excluded_paths']);
        $this->assertNotContains('.env', $technical['included_paths']);
        $this->assertContains('node_modules', $technical['excluded_paths']);
        $this->assertNotContains('node_modules', $technical['included_paths']);
        $this->assertContains('public/build.zip', $technical['excluded_paths']);
        $this->assertNotContains('public/build.zip', $technical['included_paths']);
        $this->assertSame('technical', $technical['profile']);
        $this->assertTrue($technical['dry_run']);
        $this->assertFileExists($technical['manifest_path']);
        $this->assertArrayHasKey('build_time', $technical);
        $this->assertArrayHasKey('branch', $technical);
        $this->assertArrayHasKey('commit_hash', $technical);

        $profiles = config('packaging.package_profiles');
        $profiles['cpanel_ready']['warnings'] = [
            'public/build/missing-manifest-for-test.json' => 'public/build/manifest.json is missing; run npm run build before releasing cpanel_ready packages.',
        ];
        config(['packaging.package_profiles' => $profiles]);

        $cpanelReady = $this->dryRunManifest('cpanel_ready', 'public/build/manifest.json is missing');

        $this->assertDirectoryExists(base_path('public/build'));
        $this->assertContains('public/build', $cpanelReady['included_paths']);
        $this->assertContains('public/build', $cpanelReady['available_include_paths']);
        $this->assertDirectoryExists(base_path('vendor'));
        $this->assertContains('vendor', $cpanelReady['included_paths']);
        $this->assertContains('vendor', $cpanelReady['available_include_paths']);

        $managedHandover = $this->dryRunManifest('managed_handover');

        foreach ([$technical, $cpanelReady, $managedHandover] as $manifest) {
            $prohibited = $manifest['prohibited_paths'];

            $this->assertSame([], array_values(array_intersect($prohibited, $manifest['included_paths'])));
            $this->assertSame([], array_values(array_intersect($prohibited, $manifest['available_include_paths'])));
        }

        $docs = $this->combinedDocs();
        $lowerDocs = strtolower($docs);

        $this->assertStringContainsString('SaaS buyers do not get code', $docs);
        $this->assertStringContainsString('Standalone buyers get a single-school package', $docs);
        $this->assertStringContainsString('non-technical buyers should buy done-for-you installation', $lowerDocs);
        $this->assertStringContainsString('SANFAANI_DEPLOYMENT_MODE=single_school', $docs);
        $this->assertStringContainsString('SANFAANI_LICENSE_MODE=annual', $docs);
        $this->assertStringContainsString('SANFAANI_INSTALLER_ENABLED=true', $docs);
        $this->assertStringContainsString('SANFAANI_INSTALLED=false', $docs);
        $this->assertStringContainsString('php artisan test', File::get(base_path('docs/marketplace/standalone-package-builder.md')));
    }

    private function dryRunManifest(string $profile, ?string $expectedOutput = null): array
    {
        $exitCode = Artisan::call('marketplace:build-package', [
            '--profile' => $profile,
            '--dry-run' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Marketplace package dry run complete', $output);

        if ($expectedOutput !== null) {
            $this->assertStringContainsString($expectedOutput, $output);
        }

        $manifestPath = $this->latestManifestPath($profile);
        $manifest = json_decode(File::get($manifestPath), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(str_replace('\\', '/', $manifestPath), str_replace('\\', '/', $manifest['manifest_path']));

        return $manifest;
    }

    private function latestManifestPath(string $profile): string
    {
        $files = collect(File::files($this->packageOutputPath))
            ->filter(fn (\SplFileInfo $file): bool => str_contains($file->getBasename(), '-'.$profile.'-'))
            ->sortByDesc(fn (\SplFileInfo $file): string => $file->getBasename())
            ->values();

        $this->assertNotEmpty($files, "No marketplace package manifest was generated for [{$profile}].");

        return $files->first()->getPathname();
    }

    private function combinedDocs(): string
    {
        return collect([
            'docs/marketplace/standalone-package-builder.md',
            'docs/marketplace/cpanel-ready-package-guide.md',
            'docs/marketplace/technical-buyer-package-guide.md',
            'docs/marketplace/done-for-you-installation-offer.md',
            'docs/installation/standalone-buyer-installation-flow.md',
        ])
            ->map(fn (string $path): string => File::get(base_path($path)))
            ->implode("\n");
    }
}
