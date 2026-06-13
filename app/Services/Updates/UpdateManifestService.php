<?php

namespace App\Services\Updates;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use JsonException;

class UpdateManifestService
{
    public function requiredFields(): array
    {
        return [
            'version',
            'channel',
            'release_date',
            'minimum_php',
            'minimum_laravel',
            'requires_backup',
            'requires_migration',
            'migration_notes',
            'files_changed',
            'database_changes',
            'checksum',
            'signature',
            'rollback_supported',
            'release_notes',
            'entitlements_required',
        ];
    }

    public function parseJson(?string $json): array
    {
        if (! filled($json)) {
            return [];
        }

        try {
            $manifest = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        return is_array($manifest) ? $this->normalize($manifest) : [];
    }

    public function parseUploadedJson(UploadedFile $file): array
    {
        $contents = file_get_contents($file->getRealPath());

        return $this->parseJson($contents === false ? null : $contents);
    }

    public function validate(array $manifest): array
    {
        $errors = [];

        foreach ($this->requiredFields() as $field) {
            if (! array_key_exists($field, $manifest)) {
                $errors[] = "Manifest is missing {$field}.";
            }
        }

        $channel = $this->normalizeString($manifest['channel'] ?? null);
        if ($channel !== '' && ! in_array($channel, config('updates.channels', []), true)) {
            $errors[] = 'Manifest channel must be stable, beta, or security.';
        }

        if (filled($manifest['version'] ?? null) && ! preg_match('/^\d+\.\d+\.\d+(?:[-+][A-Za-z0-9._-]+)?$/', (string) $manifest['version'])) {
            $errors[] = 'Manifest version must use semantic version format.';
        }

        if (filled($manifest['release_date'] ?? null) && strtotime((string) $manifest['release_date']) === false) {
            $errors[] = 'Manifest release_date must be a valid date.';
        }

        foreach (['minimum_php', 'minimum_laravel'] as $field) {
            if (filled($manifest[$field] ?? null) && ! preg_match('/^\d+\.\d+(?:\.\d+)?/', (string) $manifest[$field])) {
                $errors[] = "Manifest {$field} must start with a version number.";
            }
        }

        if (filled($manifest['checksum'] ?? null) && ! preg_match('/^[a-f0-9]{64}$/i', (string) $manifest['checksum'])) {
            $errors[] = 'Manifest checksum must be a SHA-256 hash.';
        }

        foreach (['files_changed', 'database_changes', 'entitlements_required'] as $field) {
            if (array_key_exists($field, $manifest) && ! is_array($manifest[$field])) {
                $errors[] = "Manifest {$field} must be an array.";
            }
        }

        foreach (['required_extensions', 'deployment_modes'] as $field) {
            if (array_key_exists($field, $manifest) && ! is_array($manifest[$field])) {
                $errors[] = "Manifest {$field} must be an array.";
            }
        }

        foreach (['minimum_current_version', 'maximum_current_version', 'min_version', 'max_version', 'target_version', 'to_version'] as $field) {
            if (filled($manifest[$field] ?? null) && ! preg_match('/^\d+\.\d+(?:\.\d+)?/', (string) $manifest[$field])) {
                $errors[] = "Manifest {$field} must start with a version number.";
            }
        }

        foreach ((array) ($manifest['required_extensions'] ?? []) as $extension) {
            if (! is_string($extension) || ! preg_match('/^[A-Za-z0-9_ -]+$/', $extension)) {
                $errors[] = 'Manifest required_extensions must contain extension names only.';
            }
        }

        $targetVersion = $manifest['target_version'] ?? $manifest['to_version'] ?? null;
        if (filled($targetVersion) && filled($manifest['version'] ?? null) && (string) $targetVersion !== (string) $manifest['version']) {
            $errors[] = 'Manifest target_version must match the package version.';
        }

        foreach ($this->unsafePathIssues($manifest) as $issue) {
            $errors[] = $issue;
        }

        foreach ($this->compatibility($manifest)['errors'] as $error) {
            $errors[] = $error;
        }

        return $errors;
    }

    public function normalize(array $manifest): array
    {
        $manifest['channel'] = $this->normalizeString($manifest['channel'] ?? config('updates.channel', 'stable'));
        $manifest['requires_backup'] = (bool) ($manifest['requires_backup'] ?? false);
        $manifest['requires_migration'] = (bool) ($manifest['requires_migration'] ?? false);
        $manifest['rollback_supported'] = (bool) ($manifest['rollback_supported'] ?? false);

        foreach (['files_changed', 'database_changes', 'entitlements_required'] as $field) {
            $manifest[$field] = array_values((array) ($manifest[$field] ?? []));
        }

        foreach (['required_extensions', 'deployment_modes'] as $field) {
            if (array_key_exists($field, $manifest)) {
                $manifest[$field] = array_values((array) $manifest[$field]);
            }
        }

        return $manifest;
    }

    public function compatibility(array $manifest, ?string $currentVersion = null): array
    {
        $currentVersion ??= (string) config('version.version', '1.0.0');
        $errors = [];
        $warnings = [];
        $requirements = [];

        $product = $manifest['target_product']
            ?? $manifest['product_name']
            ?? $manifest['product']
            ?? null;

        if (filled($product) && ! $this->matchesAllowedProduct((string) $product)) {
            $errors[] = 'Manifest target product does not match this installation.';
        }

        $edition = $manifest['target_edition']
            ?? $manifest['product_edition']
            ?? null;

        if (filled($edition) && ! $this->matchesEdition((string) $edition)) {
            $errors[] = 'Manifest target edition does not match this installation.';
        }

        $deploymentModes = collect((array) ($manifest['deployment_modes'] ?? []))
            ->map(fn (mixed $mode): string => $this->normalizeString($mode))
            ->filter()
            ->values();

        if ($deploymentModes->isNotEmpty()) {
            $currentMode = $this->normalizeString(config('sanfaani.deployment.mode', 'saas'));

            if (! $deploymentModes->contains($currentMode)) {
                $errors[] = 'Manifest deployment mode does not match this installation.';
            }
        }

        $minimum = $manifest['minimum_current_version'] ?? $manifest['min_version'] ?? null;
        if (filled($minimum) && version_compare($currentVersion, (string) $minimum, '<')) {
            $errors[] = 'Current application version is below the manifest minimum version.';
        }

        $maximum = $manifest['maximum_current_version'] ?? $manifest['max_version'] ?? null;
        if (filled($maximum) && version_compare($currentVersion, (string) $maximum, '>')) {
            $errors[] = 'Current application version is above the manifest maximum version.';
        }

        $targetVersion = $manifest['target_version'] ?? $manifest['to_version'] ?? $manifest['version'] ?? null;
        if (filled($targetVersion)) {
            if (version_compare((string) $targetVersion, $currentVersion, '<')) {
                $errors[] = 'Manifest target version is below the current application version.';
            } elseif (version_compare((string) $targetVersion, $currentVersion, '=')) {
                $warnings[] = 'Manifest target version matches the current application version; support should confirm this is intentional.';
            }
        }

        $minimumPhp = $manifest['minimum_php'] ?? null;
        if (filled($minimumPhp) && version_compare(PHP_VERSION, (string) $minimumPhp, '<')) {
            $errors[] = 'Current PHP version does not satisfy the manifest requirement.';
        }

        $minimumLaravel = $manifest['minimum_laravel'] ?? null;
        if (filled($minimumLaravel) && version_compare(app()->version(), (string) $minimumLaravel, '<')) {
            $errors[] = 'Current Laravel version does not satisfy the manifest requirement.';
        }

        $requiredExtensions = collect((array) ($manifest['required_extensions'] ?? []))
            ->map(fn (mixed $extension): string => $this->normalizeExtension((string) $extension))
            ->filter()
            ->unique()
            ->values();

        $missingExtensions = $requiredExtensions
            ->reject(fn (string $extension): bool => extension_loaded($extension))
            ->values();

        if ($missingExtensions->isNotEmpty()) {
            $errors[] = 'Required PHP extensions are missing: '.$missingExtensions->implode(', ').'.';
        }

        $requirements = [
            'minimum_php' => $minimumPhp,
            'current_php' => PHP_VERSION,
            'minimum_laravel' => $minimumLaravel,
            'current_laravel' => app()->version(),
            'required_extensions' => $requiredExtensions->all(),
            'missing_extensions' => $missingExtensions->all(),
        ];

        if (! filled($product)) {
            $warnings[] = 'Manifest does not declare a target product; support should confirm package provenance manually.';
        }

        if (! filled($edition)) {
            $warnings[] = 'Manifest does not declare a target edition; support should confirm standalone compatibility manually.';
        }

        return [
            'status' => $errors === [] ? ($warnings === [] ? 'compatible' : 'review') : 'incompatible',
            'current_version' => $currentVersion,
            'target_version' => $targetVersion,
            'target_product' => $product,
            'target_edition' => $edition,
            'deployment_modes' => $deploymentModes->all(),
            'requirements' => $requirements,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    public function unsafePathIssues(array $manifest): array
    {
        $paths = collect((array) data_get($manifest, 'files_changed', []))
            ->merge((array) data_get($manifest, 'files', []))
            ->map(fn (mixed $path): string => is_array($path) ? (string) data_get($path, 'path', '') : (string) $path)
            ->filter(fn (string $path): bool => trim($path) !== '')
            ->values();

        return $paths
            ->flatMap(fn (string $path): array => $this->pathIssues($path))
            ->unique()
            ->values()
            ->all();
    }

    public function pathIssues(string $path, string $source = 'Manifest path'): array
    {
        $normalized = $this->normalizePath($path);
        $issues = [];

        if ($normalized === '' || str_contains($path, "\0")) {
            return ["{$source} is empty or invalid."];
        }

        if (str_starts_with($path, '/') || str_starts_with($path, '\\') || preg_match('/^[A-Za-z]:[\/\\\\]/', $path)) {
            $issues[] = "{$source} {$normalized} is absolute and cannot be reviewed safely.";
        }

        if (collect(explode('/', $normalized))->contains('..')) {
            $issues[] = "{$source} {$normalized} contains traversal segments.";
        }

        foreach ($this->protectedPaths() as $protected) {
            if ($normalized === $protected) {
                $issues[] = "{$source} {$normalized} targets a protected file.";
            }
        }

        if ($this->targetsEnvironmentFile($normalized)) {
            $issues[] = "{$source} {$normalized} targets environment configuration.";
        }

        return $issues;
    }

    public function sample(): array
    {
        return [
            'target_product' => config('version.product_name', 'Sanfaani Schools'),
            'target_edition' => config('standalone.product_edition', 'standalone'),
            'deployment_modes' => [config('sanfaani.deployment.mode', 'single_school')],
            'version' => '1.0.1',
            'channel' => 'stable',
            'release_date' => now()->toDateString(),
            'minimum_current_version' => config('version.version', '1.0.0'),
            'maximum_current_version' => null,
            'minimum_php' => config('updates.php_minimum', '8.2.0'),
            'minimum_laravel' => app()->version(),
            'required_extensions' => ['zip'],
            'requires_backup' => true,
            'requires_migration' => false,
            'migration_notes' => 'No automatic migration will run from the web wizard.',
            'files_changed' => ['app/Services/ExampleService.php'],
            'database_changes' => [],
            'checksum' => str_repeat('0', 64),
            'signature' => 'planned-signature-placeholder',
            'rollback_supported' => true,
            'release_notes' => 'Describe the safe manual update steps here.',
            'entitlements_required' => ['update_manager'],
        ];
    }

    private function normalizeString(mixed $value): string
    {
        return str((string) $value)
            ->trim()
            ->lower()
            ->replace([' ', '-'], '_')
            ->toString();
    }

    public function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = preg_replace('#/+#', '/', $path) ?? $path;

        while (Str::startsWith($path, './')) {
            $path = substr($path, 2);
        }

        return $path;
    }

    public function protectedPaths(): array
    {
        return collect(array_merge([
            '.env',
            '.env.local',
            'public/build.zip',
            'database/migrations/2026_05_01_173857_create_result_publications_table.php',
        ], (array) config('updates.protected_paths', [])))
            ->map(fn (mixed $path): string => $this->normalizePath((string) $path))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function matchesAllowedProduct(string $product): bool
    {
        $candidate = $this->normalizeProductName($product);

        return collect((array) config('updates.allowed_product_names', [
            config('version.product_name', 'Sanfaani Schools'),
            config('sanfaani.platform_name', 'Sanfaani Schools'),
            'Sanfaani Schools Standalone',
        ]))
            ->map(fn (mixed $value): string => $this->normalizeProductName((string) $value))
            ->contains($candidate);
    }

    private function matchesEdition(string $edition): bool
    {
        $candidate = $this->normalizeString($edition);

        return collect(array_merge([
            config('standalone.product_edition', 'standalone'),
            config('sanfaani.deployment.mode', 'single_school'),
        ], (array) config('updates.allowed_editions', [
            'standalone',
            'single_school',
            'saas',
            'platform',
            'managed',
        ])))
            ->map(fn (mixed $value): string => $this->normalizeString($value))
            ->contains($candidate);
    }

    private function normalizeProductName(string $value): string
    {
        return str($value)
            ->trim()
            ->lower()
            ->squish()
            ->toString();
    }

    private function normalizeExtension(string $extension): string
    {
        return str($extension)
            ->trim()
            ->lower()
            ->replace(['ext-', 'php-', ' '], ['', '', '_'])
            ->toString();
    }

    private function targetsEnvironmentFile(string $normalized): bool
    {
        $segments = collect(explode('/', $normalized));

        return $segments->contains(fn (string $segment): bool => in_array($segment, ['.env', '.env.local'], true)
            || Str::startsWith($segment, '.env.'));
    }
}
