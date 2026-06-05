<?php

namespace App\Services\Marketplace;

use App\Support\Marketplace\StoredZipWriter;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Process\Process;
use Throwable;

class MarketplacePackageBuilder
{
    public function __construct(private readonly StoredZipWriter $zipWriter) {}

    public function build(string $profile, bool $dryRun = false): array
    {
        $profileConfig = $this->profileConfig($profile);
        $root = $this->rootPath();
        $outputDirectory = $this->outputDirectory();
        $buildTime = now()->utc();
        $packageName = (string) config('packaging.package_name', 'sanfaani-schools');
        $slug = $packageName.'-'.$profile.'-'.$buildTime->format('Ymd_His_u');
        $includePaths = $this->normalizeList($profileConfig['include_paths'] ?? config('packaging.include_paths', []));
        $excludePaths = $this->normalizeList($profileConfig['exclude_paths'] ?? config('packaging.exclude_paths', []));
        $prohibitedPaths = $this->normalizeList(config('packaging.prohibited_paths', []));

        $this->assertProhibitedPathsAreExcluded($includePaths, $excludePaths, $prohibitedPaths);

        $availableIncludePaths = $this->existingPaths($root, $includePaths);
        $missingIncludePaths = array_values(array_diff($includePaths, $availableIncludePaths));
        $warnings = $this->warnings($root, (array) ($profileConfig['warnings'] ?? []));
        $files = $dryRun ? [] : $this->collectFiles($root, $includePaths, $excludePaths);

        if (! $dryRun) {
            if ($files === []) {
                throw new RuntimeException('No files matched the selected marketplace package profile.');
            }

            $this->assertCollectedFilesAreSafe($files, $prohibitedPaths);
        }

        $zipPath = $dryRun ? null : $outputDirectory.DIRECTORY_SEPARATOR.$slug.'.zip';
        $manifestPath = $outputDirectory.DIRECTORY_SEPARATOR.$slug.($dryRun ? '.dry-run' : '').'.manifest.json';

        $manifest = [
            'schema_version' => 1,
            'package_name' => $packageName,
            'profile' => $profile,
            'description' => (string) ($profileConfig['description'] ?? ''),
            'dry_run' => $dryRun,
            'build_time' => $buildTime->toIso8601String(),
            'branch' => $this->git(['branch', '--show-current'], $root),
            'commit_hash' => $this->git(['rev-parse', 'HEAD'], $root),
            'root_path' => $root,
            'zip_path' => $zipPath,
            'manifest_path' => $manifestPath,
            'included_paths' => $includePaths,
            'available_include_paths' => $availableIncludePaths,
            'missing_include_paths' => $missingIncludePaths,
            'excluded_paths' => $excludePaths,
            'prohibited_paths' => $prohibitedPaths,
            'warnings' => $warnings,
            'file_count' => $dryRun ? null : count($files),
            'included_files_sample' => $dryRun ? [] : array_slice(array_column($files, 'relative_path'), 0, 50),
            'zip_sha256' => null,
            'zip_size' => null,
        ];

        if (! is_dir($outputDirectory) && ! mkdir($outputDirectory, 0755, true) && ! is_dir($outputDirectory)) {
            throw new RuntimeException("Unable to create package output directory [{$outputDirectory}].");
        }

        if (! $dryRun) {
            $this->zipWriter->write($zipPath, $files);
            $manifest['zip_sha256'] = hash_file('sha256', $zipPath) ?: null;
            $manifest['zip_size'] = filesize($zipPath) ?: null;
        }

        $encoded = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($encoded === false || file_put_contents($manifestPath, $encoded.PHP_EOL) === false) {
            throw new RuntimeException("Unable to write package manifest [{$manifestPath}].");
        }

        return $manifest;
    }

    private function profileConfig(string $profile): array
    {
        $profiles = (array) config('packaging.package_profiles', []);

        if (! array_key_exists($profile, $profiles)) {
            throw new InvalidArgumentException("Unknown marketplace package profile [{$profile}].");
        }

        return (array) $profiles[$profile];
    }

    private function rootPath(): string
    {
        $configured = config('packaging.builder_root');

        return $this->absolutePath($configured ? (string) $configured : base_path());
    }

    private function outputDirectory(): string
    {
        return $this->absolutePath((string) config('packaging.output_path', 'storage/app/marketplace-packages'), base_path());
    }

    /**
     * @param  array<int, string>  $paths
     * @return array<int, string>
     */
    private function normalizeList(array $paths): array
    {
        return collect($paths)
            ->map(fn (mixed $path): string => $this->normalizePath((string) $path))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $includePaths
     * @return array<int, string>
     */
    private function existingPaths(string $root, array $includePaths): array
    {
        return collect($includePaths)
            ->filter(fn (string $path): bool => file_exists($this->join($root, $path)))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string>  $warningPaths
     * @return array<int, array{path: string, message: string}>
     */
    private function warnings(string $root, array $warningPaths): array
    {
        $warnings = [];

        foreach ($warningPaths as $path => $message) {
            $normalized = $this->normalizePath((string) $path);

            if (! file_exists($this->join($root, $normalized))) {
                $warnings[] = [
                    'path' => $normalized,
                    'message' => (string) $message,
                ];
            }
        }

        return $warnings;
    }

    /**
     * @param  array<int, string>  $includePaths
     * @param  array<int, string>  $excludePaths
     * @param  array<int, string>  $prohibitedPaths
     */
    private function assertProhibitedPathsAreExcluded(array $includePaths, array $excludePaths, array $prohibitedPaths): void
    {
        $violations = collect($prohibitedPaths)
            ->filter(fn (string $path): bool => $this->wouldBeIncludedByConfig($path, $includePaths) && ! $this->pathMatchesAny($path, $excludePaths))
            ->values()
            ->all();

        if ($violations === []) {
            return;
        }

        if (in_array('.env', $violations, true)) {
            throw new RuntimeException('.env would be included by the selected package profile.');
        }

        if (in_array('public/build.zip', $violations, true)) {
            throw new RuntimeException('public/build.zip would be included by the selected package profile.');
        }

        throw new RuntimeException('Prohibited paths would be included by the selected package profile: '.implode(', ', $violations));
    }

    /**
     * @param  array<int, array{absolute_path: string, relative_path: string}>  $files
     * @param  array<int, string>  $prohibitedPaths
     */
    private function assertCollectedFilesAreSafe(array $files, array $prohibitedPaths): void
    {
        $violations = collect($files)
            ->pluck('relative_path')
            ->filter(fn (string $path): bool => $this->pathMatchesAny($path, $prohibitedPaths))
            ->values()
            ->all();

        if ($violations !== []) {
            throw new RuntimeException('Prohibited paths would be included in the package: '.implode(', ', array_slice($violations, 0, 20)));
        }
    }

    /**
     * @param  array<int, string>  $includePaths
     * @param  array<int, string>  $excludePaths
     * @return array<int, array{absolute_path: string, relative_path: string}>
     */
    private function collectFiles(string $root, array $includePaths, array $excludePaths): array
    {
        $files = [];

        foreach ($includePaths as $includePath) {
            $absolutePath = $this->join($root, $includePath);

            if (is_file($absolutePath)) {
                $this->addFile($files, $absolutePath, $includePath, $excludePaths);

                continue;
            }

            if (! is_dir($absolutePath)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($absolutePath, RecursiveDirectoryIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                if (! $file instanceof SplFileInfo || ! $file->isFile() || $file->isLink()) {
                    continue;
                }

                $relativePath = $this->relativePath($root, $file->getPathname());
                $this->addFile($files, $file->getPathname(), $relativePath, $excludePaths);
            }
        }

        ksort($files);

        return array_values($files);
    }

    /**
     * @param  array<string, array{absolute_path: string, relative_path: string}>  $files
     * @param  array<int, string>  $excludePaths
     */
    private function addFile(array &$files, string $absolutePath, string $relativePath, array $excludePaths): void
    {
        $relativePath = $this->normalizePath($relativePath);

        if ($this->pathMatchesAny($relativePath, $excludePaths)) {
            return;
        }

        $files[$relativePath] = [
            'absolute_path' => $absolutePath,
            'relative_path' => $relativePath,
        ];
    }

    /**
     * @param  array<int, string>  $includePaths
     */
    private function wouldBeIncludedByConfig(string $path, array $includePaths): bool
    {
        foreach ($includePaths as $includePath) {
            if ($includePath === '.' || $includePath === $path || str_starts_with($path, $includePath.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $patterns
     */
    private function pathMatchesAny(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($this->pathMatches($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function pathMatches(string $path, string $pattern): bool
    {
        $path = $this->normalizePath($path);
        $pattern = $this->normalizePath($pattern);

        if ($path === '' || $pattern === '') {
            return false;
        }

        if (! str_contains($pattern, '*')) {
            return $path === $pattern || str_starts_with($path, $pattern.'/');
        }

        $regex = '#^'.str_replace('\*', '[^/]*', preg_quote($pattern, '#')).'$#i';

        if (preg_match($regex, $path) === 1) {
            return true;
        }

        return ! str_contains($pattern, '/') && preg_match($regex, basename($path)) === 1;
    }

    private function relativePath(string $root, string $absolutePath): string
    {
        $root = rtrim(str_replace('\\', '/', $root), '/').'/';
        $absolutePath = str_replace('\\', '/', $absolutePath);

        return $this->normalizePath(str_starts_with($absolutePath, $root) ? substr($absolutePath, strlen($root)) : $absolutePath);
    }

    private function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = preg_replace('#/+#', '/', $path) ?: '';

        return trim($path, '/');
    }

    private function join(string $root, string $path): string
    {
        return rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function absolutePath(string $path, ?string $base = null): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        if ($this->isAbsolutePath($path)) {
            return rtrim($path, DIRECTORY_SEPARATOR);
        }

        return rtrim($base ?: base_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$path;
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1 || str_starts_with($path, DIRECTORY_SEPARATOR);
    }

    /**
     * @param  array<int, string>  $arguments
     */
    private function git(array $arguments, string $cwd): ?string
    {
        try {
            $process = new Process(['git', ...$arguments], $cwd);
            $process->setTimeout(5);
            $process->run();

            if (! $process->isSuccessful()) {
                return null;
            }

            return trim($process->getOutput()) ?: null;
        } catch (Throwable) {
            return null;
        }
    }
}
