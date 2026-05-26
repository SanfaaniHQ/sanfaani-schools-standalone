<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

class StagingReadinessCommand extends Command
{
    protected $signature = 'staging:check-readiness {--json : Output a JSON readiness report}';

    protected $description = 'Read-only staging release candidate checks for docs, configs, commands, modes, and protected-file guardrails.';

    public function handle(): int
    {
        $checks = [];

        $add = function (string $key, string $status, string $message, array $context = []) use (&$checks): void {
            $checks[] = compact('key', 'status', 'message', 'context');
        };

        $this->checkDocs($add);
        $this->checkConfigs($add);
        $this->checkCommands($add);
        $this->checkProtectedFilesNotStaged($add);
        $this->checkEnvironmentGuidance($add);
        $this->checkCommercialConfigs($add);
        $this->checkModesFlagsAndRouteGroups($add);
        $this->checkRoadmapDocs($add);

        $summary = $this->summary($checks);

        if ($this->option('json')) {
            $this->line(json_encode([
                'checks' => $checks,
                'summary' => $summary,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            foreach ($checks as $check) {
                $this->line(sprintf('[%s] %s: %s', strtoupper($check['status']), $check['key'], $check['message']));
            }

            $this->info(sprintf(
                'Staging readiness report complete: %d pass, %d warning, %d fail. No tests were run and no files were modified.',
                $summary['pass'],
                $summary['warning'],
                $summary['fail'],
            ));
        }

        return self::SUCCESS;
    }

    private function checkDocs(callable $add): void
    {
        $missing = collect((array) config('staging.staging_required_docs', []))
            ->filter(fn (string $path): bool => ! File::exists(base_path($path)))
            ->values()
            ->all();

        $add(
            'required_staging_docs',
            $missing === [] ? 'pass' : 'fail',
            $missing === [] ? 'All required staging docs exist.' : 'Some required staging docs are missing.',
            ['missing' => $missing],
        );
    }

    private function checkConfigs(callable $add): void
    {
        foreach ((array) config('staging.required_configs', []) as $path) {
            $exists = File::exists(base_path($path));

            $add(
                'config_'.$this->key($path),
                $exists ? 'pass' : 'fail',
                $exists ? "[{$path}] exists." : "[{$path}] is missing.",
                ['path' => $path],
            );
        }
    }

    private function checkCommands(callable $add): void
    {
        $commands = Artisan::all();

        foreach ((array) config('staging.required_staging_commands', []) as $command) {
            $registered = array_key_exists($command, $commands);

            $add(
                'command_'.$this->key($command),
                $registered ? 'pass' : 'fail',
                $registered ? "[{$command}] is registered." : "[{$command}] is not registered.",
                ['command' => $command],
            );
        }
    }

    private function checkProtectedFilesNotStaged(callable $add): void
    {
        $staged = collect(explode("\n", $this->git(['diff', '--cached', '--name-only'])))
            ->map(fn (string $path): string => trim(str_replace('\\', '/', $path)))
            ->filter()
            ->values()
            ->all();

        $protected = collect((array) config('staging.protected_files', []))
            ->map(fn (string $path): string => str_replace('\\', '/', $path))
            ->values()
            ->all();

        $matches = collect($staged)
            ->filter(fn (string $path): bool => in_array($path, $protected, true))
            ->values()
            ->all();

        $add(
            'protected_files_not_staged',
            $matches === [] ? 'pass' : 'fail',
            $matches === [] ? 'Protected files are not staged.' : 'Protected files are staged and must be removed before staging release candidate approval.',
            ['staged_protected_files' => $matches],
        );
    }

    private function checkEnvironmentGuidance(callable $add): void
    {
        $env = (string) config('app.env');
        $debug = (bool) config('app.debug');

        $add(
            'app_env_guidance',
            $env === 'local' ? 'warning' : 'pass',
            $env === 'local' ? 'Staging should use production-like APP_ENV values, not local.' : "APP_ENV is [{$env}].",
            ['app_env' => $env],
        );

        $add(
            'app_debug_guidance',
            $debug ? 'warning' : 'pass',
            $debug ? 'Staging should set APP_DEBUG=false.' : 'APP_DEBUG is false.',
            ['app_debug' => $debug],
        );
    }

    private function checkCommercialConfigs(callable $add): void
    {
        foreach ([
            'installer' => 'installer.php',
            'licensing' => 'licensing.php',
            'deployment_modes' => 'deployment_modes.php',
            'features' => 'features.php',
            'branding' => 'branding.php',
            'backups' => 'backups.php',
            'updates' => 'updates.php',
            'packaging' => 'packaging.php',
        ] as $key => $file) {
            $exists = File::exists(config_path($file));

            $add(
                'commercial_config_'.$key,
                $exists ? 'pass' : 'fail',
                $exists ? "[config/{$file}] exists." : "[config/{$file}] is missing.",
            );
        }
    }

    private function checkModesFlagsAndRouteGroups(callable $add): void
    {
        $features = array_keys((array) config('features.features', []));
        $deploymentModes = array_keys((array) config('deployment_modes.modes', []));
        $licenseModes = (array) config('licensing.types', []);
        $routeGroups = array_keys((array) config('deployment_modes.route_groups', []));

        $this->checkRequiredValues($add, 'feature_flags', (array) config('staging.required_feature_flags', []), $features);
        $this->checkRequiredValues($add, 'deployment_modes', (array) config('staging.required_deployment_modes', []), $deploymentModes);
        $this->checkRequiredValues($add, 'license_modes', (array) config('staging.required_license_modes', []), $licenseModes);
        $this->checkRequiredValues($add, 'route_groups', (array) config('staging.required_route_groups', []), $routeGroups);
    }

    private function checkRoadmapDocs(callable $add): void
    {
        $missing = collect((array) config('staging.final_roadmap_docs', []))
            ->filter(fn (string $path): bool => ! File::exists(base_path($path)))
            ->values()
            ->all();

        $add(
            'final_roadmap_docs',
            $missing === [] ? 'pass' : 'fail',
            $missing === [] ? 'Final roadmap docs exist.' : 'Final roadmap docs are missing.',
            ['missing' => $missing],
        );
    }

    private function checkRequiredValues(callable $add, string $key, array $required, array $available): void
    {
        $missing = collect($required)
            ->reject(fn (string $value): bool => in_array($value, $available, true))
            ->values()
            ->all();

        $add(
            'required_'.$key,
            $missing === [] ? 'pass' : 'fail',
            $missing === [] ? "All required {$key} are present." : "Some required {$key} are missing.",
            ['missing' => $missing],
        );
    }

    private function git(array $arguments): string
    {
        try {
            $process = new Process(['git', ...$arguments], base_path());
            $process->setTimeout(5);
            $process->run();

            return trim($process->getOutput());
        } catch (Throwable) {
            return '';
        }
    }

    private function summary(array $checks): array
    {
        return [
            'pass' => collect($checks)->where('status', 'pass')->count(),
            'warning' => collect($checks)->where('status', 'warning')->count(),
            'fail' => collect($checks)->where('status', 'fail')->count(),
        ];
    }

    private function key(string $value): string
    {
        return str($value)->replace(['\\', '/', '.', '-', ':', ' '], '_')->squish()->toString();
    }
}
