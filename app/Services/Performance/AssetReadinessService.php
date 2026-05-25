<?php

namespace App\Services\Performance;

use App\Support\Performance\PerformanceCheckResult;
use Illuminate\Support\Facades\File;

class AssetReadinessService
{
    public function checks(): array
    {
        $buildExists = File::isDirectory(public_path('build'));
        $buildZipExists = File::exists(public_path('build.zip'));

        return $this->toArray([
            $buildExists
                ? PerformanceCheckResult::pass('public_build_assets', 'Built assets', 'public/build exists for runtime assets.', ['path' => 'public/build'])
                : PerformanceCheckResult::warning('public_build_assets', 'Built assets', 'public/build was not detected. Run npm build during deployment where applicable.', ['path' => 'public/build']),
            PerformanceCheckResult::warning('public_build_zip_runtime', 'Generated archive', 'public/build.zip should not be treated as a runtime package or marketplace artifact.', ['exists' => $buildZipExists, 'path' => 'public/build.zip']),
            PerformanceCheckResult::info('upload_max_filesize', 'Upload max filesize', 'PHP upload_max_filesize is ['.ini_get('upload_max_filesize').']; keep package, backup, and asset uploads below configured limits.', ['upload_max_filesize' => ini_get('upload_max_filesize')]),
            PerformanceCheckResult::info('post_max_size', 'Post max size', 'PHP post_max_size is ['.ini_get('post_max_size').']; shared hosting may reject oversized package uploads before Laravel validation.', ['post_max_size' => ini_get('post_max_size')]),
            PerformanceCheckResult::warning('excluded_heavy_paths', 'Excluded heavy paths', 'Vendor, node_modules, logs, cache, sessions, backups, private storage, .env, and public/build.zip must stay out of package and backup plans.', ['excluded_heavy_paths' => config('performance.excluded_heavy_paths', [])]),
        ]);
    }

    private function toArray(array $checks): array
    {
        return array_map(fn (PerformanceCheckResult $check): array => $check->toArray(), $checks);
    }
}
