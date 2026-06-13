<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use JsonException;
use ZipArchive;

class InspectMarketplacePackageCommand extends Command
{
    protected $signature = 'marketplace:inspect-package {path : Path to a generated marketplace package ZIP}';

    protected $description = 'Inspect a marketplace package ZIP without extracting, modifying, deploying, or touching databases.';

    /**
     * @var array<int, string>
     */
    private array $failures = [];

    public function handle(): int
    {
        $this->failures = [];

        $zipPath = $this->absolutePath((string) $this->argument('path'));

        if (! File::exists($zipPath) || ! is_file($zipPath)) {
            $this->line('[FAIL] package_exists: Package ZIP was not found at '.$zipPath);

            return self::FAILURE;
        }

        if (! class_exists(ZipArchive::class)) {
            $this->line('[FAIL] zip_extension_available: PHP zip extension is required to inspect package contents.');

            return self::FAILURE;
        }

        $zip = new ZipArchive;
        $opened = $zip->open($zipPath, ZipArchive::RDONLY);

        if ($opened !== true) {
            $this->line('[FAIL] zip_readable: Package could not be opened as a ZIP archive.');

            return self::FAILURE;
        }

        try {
            $entries = $this->zipEntries($zip);
        } finally {
            $zip->close();
        }

        $this->packageInfo($zipPath, $entries);
        $this->inspectProhibitedEntries($entries);
        $this->inspectOptionalCpanelEntries($entries);
        $this->inspectInstallerDocs($entries);
        $this->inspectBuilderManifest($zipPath);

        if ($this->failures !== []) {
            $this->line('Package inspection failed.');

            return self::FAILURE;
        }

        $this->line('Package inspection passed.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function zipEntries(ZipArchive $zip): array
    {
        $entries = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);

            if (! is_string($name)) {
                continue;
            }

            $normalized = $this->normalizePath($name);

            if ($normalized !== '') {
                $entries[] = $normalized;
            }
        }

        sort($entries);

        return array_values(array_unique($entries));
    }

    /**
     * @param  array<int, string>  $entries
     */
    private function packageInfo(string $zipPath, array $entries): void
    {
        $hash = hash_file('sha256', $zipPath) ?: 'unavailable';
        $size = filesize($zipPath);

        $this->line('Package: '.$zipPath);
        $this->line('Size: '.($size === false ? 'unavailable' : $size.' bytes'));
        $this->line('SHA-256: '.$hash);
        $this->line('Entries: '.count($entries));
        $this->line('Files: '.count($entries));
    }

    /**
     * @param  array<int, string>  $entries
     */
    private function inspectProhibitedEntries(array $entries): void
    {
        $this->assertAbsent(
            'env_excluded',
            ! $this->hasSegment($entries, '.env'),
            '.env is not present.',
            '.env is present in the package.',
        );

        $this->assertAbsent(
            'env_local_excluded',
            ! $this->hasSegment($entries, '.env.local'),
            '.env.local is not present.',
            '.env.local is present in the package.',
        );

        $this->assertAbsent(
            'public_build_zip_excluded',
            ! $this->hasPath($entries, 'public/build.zip'),
            'public/build.zip is not present.',
            'public/build.zip is present in the package.',
        );

        $this->assertAbsent(
            'git_excluded',
            ! $this->hasSegment($entries, '.git'),
            '.git is not present.',
            '.git is present in the package.',
        );

        $this->assertAbsent(
            'node_modules_excluded',
            ! $this->hasSegment($entries, 'node_modules'),
            'node_modules is not present.',
            'node_modules is present in the package.',
        );
    }

    /**
     * @param  array<int, string>  $entries
     */
    private function inspectOptionalCpanelEntries(array $entries): void
    {
        if ($this->hasSegment($entries, 'vendor')) {
            $this->line('[PASS] vendor_present: vendor/ is present.');
        } else {
            $this->line('[WARN] vendor_present: vendor/ is missing; cpanel_ready packages should include it when Composer dependencies are available.');
        }

        if ($this->hasPath($entries, 'public/build/manifest.json')) {
            $this->line('[PASS] public_build_manifest_present: public/build/manifest.json is present.');
        } else {
            $this->line('[WARN] public_build_manifest_present: public/build/manifest.json is missing; cpanel_ready packages should include built assets when available.');
        }
    }

    /**
     * @param  array<int, string>  $entries
     */
    private function inspectInstallerDocs(array $entries): void
    {
        $requiredDocs = [
            'docs/marketplace/standalone-package-qa-checklist.md',
            'docs/marketplace/cpanel-ready-package-acceptance-test.md',
            'docs/installation/standalone-buyer-installation-flow.md',
            'docs/installation/standalone-installer-acceptance-test.md',
            'docs/support/standalone-buyer-support-playbook.md',
        ];

        $missing = collect($requiredDocs)
            ->reject(fn (string $path): bool => $this->hasPath($entries, $path))
            ->values()
            ->all();

        if ($missing === []) {
            $this->line('[PASS] installer_docs_present: Standalone buyer QA and installer docs are present.');

            return;
        }

        $this->recordFailure('installer_docs_present', 'Missing installer acceptance docs: '.implode(', ', $missing));
    }

    private function inspectBuilderManifest(string $zipPath): void
    {
        $manifestPath = $this->builderManifestPath($zipPath);

        if (! File::exists($manifestPath)) {
            $this->line('[WARN] builder_manifest_present: No sibling builder manifest was found; release packages generated by marketplace:build-package should keep the manifest beside the ZIP.');

            return;
        }

        try {
            $manifest = json_decode(File::get($manifestPath), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->recordFailure('builder_manifest_valid', 'Sibling builder manifest exists but is not valid JSON.');

            return;
        }

        $profile = is_array($manifest) ? ($manifest['profile'] ?? 'unknown') : 'unknown';
        $packageName = is_array($manifest) ? ($manifest['package_name'] ?? 'unknown') : 'unknown';

        $this->line('[PASS] builder_manifest_present: Sibling builder manifest is present.');
        $this->line('Manifest package: '.$packageName);
        $this->line('Manifest profile: '.$profile);
    }

    private function assertAbsent(string $key, bool $passed, string $passMessage, string $failMessage): void
    {
        if ($passed) {
            $this->line('[PASS] '.$key.': '.$passMessage);

            return;
        }

        $this->recordFailure($key, $failMessage);
    }

    private function recordFailure(string $key, string $message): void
    {
        $this->failures[] = $key;
        $this->line('[FAIL] '.$key.': '.$message);
    }

    /**
     * @param  array<int, string>  $entries
     */
    private function hasSegment(array $entries, string $segment): bool
    {
        foreach ($entries as $entry) {
            if (in_array($segment, explode('/', $entry), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $entries
     */
    private function hasPath(array $entries, string $path): bool
    {
        $path = $this->normalizePath($path);

        foreach ($entries as $entry) {
            if ($entry === $path || str_ends_with($entry, '/'.$path)) {
                return true;
            }
        }

        return false;
    }

    private function builderManifestPath(string $zipPath): string
    {
        return dirname($zipPath).DIRECTORY_SEPARATOR.pathinfo($zipPath, PATHINFO_FILENAME).'.manifest.json';
    }

    private function absolutePath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        if (preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1 || str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }

    private function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = preg_replace('#/+#', '/', $path) ?: '';

        return trim($path, '/');
    }
}
