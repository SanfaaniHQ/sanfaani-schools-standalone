<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ValidateMarketplacePackageCommand extends Command
{
    protected $signature = 'marketplace:validate-package {--json : Output a JSON readiness report}';

    protected $description = 'Validate marketplace package readiness without creating archives or copying files.';

    public function handle(): int
    {
        $checks = [];

        $add = function (string $key, bool $passed, string $message, array $context = []) use (&$checks): void {
            $checks[] = compact('key', 'passed', 'message', 'context');
        };

        $this->checkRequiredDocs($add);
        $this->checkMarketplaceEnv($add);
        $this->checkExclusions($add);
        $this->checkPublicBuildZip($add);

        $failed = collect($checks)->where('passed', false)->values();

        if ($this->option('json')) {
            $this->line(json_encode([
                'passed' => $failed->isEmpty(),
                'checks' => $checks,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            foreach ($checks as $check) {
                $this->line(($check['passed'] ? '[PASS] ' : '[FAIL] ').$check['key'].': '.$check['message']);
            }
        }

        if ($failed->isNotEmpty()) {
            $this->error('Marketplace package validation failed.');

            return self::FAILURE;
        }

        $this->info('Marketplace package validation passed. No ZIP was created and no files were copied.');

        return self::SUCCESS;
    }

    private function checkRequiredDocs(callable $add): void
    {
        $missing = collect((array) config('packaging.required_docs', []))
            ->filter(fn (string $path): bool => ! File::exists(base_path($path)))
            ->values()
            ->all();

        $add(
            'required_docs_exist',
            $missing === [],
            $missing === [] ? 'All required marketplace and foundation docs exist.' : 'Missing required docs.',
            ['missing' => $missing],
        );
    }

    private function checkMarketplaceEnv(callable $add): void
    {
        $path = base_path('.env.marketplace.example');

        if (! File::exists($path)) {
            $add('marketplace_env_template_safe', false, '.env.marketplace.example is missing.');

            return;
        }

        $contents = File::get($path);
        $patterns = [
            '/APP_KEY\s*=\s*base64:/i',
            '/sanfaani\.net/i',
            '/sanfaanisaas/i',
            '/localhost\/Users|C:\\\\|\/home\/[^\\s]+/i',
            '/(PAYSTACK_SECRET|FLUTTERWAVE_SECRET|AWS_SECRET_ACCESS_KEY)\s*=\s*\S+/i',
        ];

        $matches = collect($patterns)
            ->filter(fn (string $pattern): bool => preg_match($pattern, $contents) === 1)
            ->values()
            ->all();

        $add(
            'marketplace_env_template_safe',
            $matches === [],
            $matches === [] ? 'Marketplace env template exists and contains no obvious real secrets.' : 'Marketplace env template may contain real secrets or production values.',
            ['matched_patterns' => $matches],
        );
    }

    private function checkExclusions(callable $add): void
    {
        $exclusions = $this->normalizedConfigList('packaging.exclude_paths');
        $missing = collect((array) config('packaging.prohibited_paths', []))
            ->map(fn (string $path): string => $this->normalizePath($path))
            ->reject(fn (string $path): bool => in_array($path, $exclusions, true))
            ->values()
            ->all();

        $add(
            'prohibited_paths_excluded',
            $missing === [],
            $missing === [] ? 'All prohibited paths are listed in package exclusions.' : 'Some prohibited paths are missing from exclusions.',
            ['missing' => $missing],
        );
    }

    private function checkPublicBuildZip(callable $add): void
    {
        $includes = $this->normalizedConfigList('packaging.include_paths');
        $exclusions = $this->normalizedConfigList('packaging.exclude_paths');
        $zip = 'public/build.zip';

        $add(
            'public_build_zip_excluded',
            ! in_array($zip, $includes, true) && in_array($zip, $exclusions, true),
            'public/build.zip is excluded and is not an explicit package include.',
            ['included' => in_array($zip, $includes, true), 'excluded' => in_array($zip, $exclusions, true)],
        );
    }

    private function normalizedConfigList(string $key): array
    {
        return collect((array) config($key, []))
            ->map(fn (mixed $path): string => $this->normalizePath((string) $path))
            ->values()
            ->all();
    }

    private function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');

        return preg_replace('#/+#', '/', $path) ?: '';
    }
}
