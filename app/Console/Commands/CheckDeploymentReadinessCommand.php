<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckDeploymentReadinessCommand extends Command
{
    protected $signature = 'deployment:check-readiness {--json : Output a JSON readiness report}';

    protected $description = 'Read-only deployment readiness checks for shared, VPS, cloud, managed, and marketplace hosting.';

    public function handle(): int
    {
        $checks = [];

        $add = function (string $key, string $status, string $message, array $context = []) use (&$checks): void {
            $checks[] = compact('key', 'status', 'message', 'context');
        };

        $this->checkDocs($add);
        $this->checkEnvironment($add);
        $this->checkPhpExtensions($add);
        $this->checkWritablePaths($add);
        $this->checkApplicationConfig($add);
        $this->checkPublicExposure($add);
        $this->checkSchedulerAndQueues($add);
        $this->checkCommercialFoundations($add);
        $this->checkPublicBuildZip($add);

        if ($this->option('json')) {
            $this->line(json_encode([
                'checks' => $checks,
                'summary' => $this->summary($checks),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            foreach ($checks as $check) {
                $this->line(sprintf('[%s] %s: %s', strtoupper($check['status']), $check['key'], $check['message']));
            }

            $summary = $this->summary($checks);
            $this->info(sprintf(
                'Deployment readiness report complete: %d pass, %d warning, %d fail. No files were modified.',
                $summary['pass'],
                $summary['warning'],
                $summary['fail'],
            ));
        }

        return self::SUCCESS;
    }

    private function checkDocs(callable $add): void
    {
        $missing = collect((array) config('deployment_readiness.required_docs', []))
            ->filter(fn (string $path): bool => ! File::exists(base_path($path)))
            ->values()
            ->all();

        $add(
            'required_deployment_docs',
            $missing === [] ? 'pass' : 'fail',
            $missing === [] ? 'All required deployment docs exist.' : 'Some deployment docs are missing.',
            ['missing' => $missing],
        );
    }

    private function checkEnvironment(callable $add): void
    {
        $env = (string) config('app.env');
        $debug = (bool) config('app.debug');

        $add(
            'app_env',
            $env === 'local' ? 'warning' : 'pass',
            $env === 'local' ? 'Production deployments should not use APP_ENV=local.' : "APP_ENV is [{$env}].",
            ['app_env' => $env],
        );

        $add(
            'app_debug',
            $debug ? 'warning' : 'pass',
            $debug ? 'APP_DEBUG=true is unsafe for production and must be disabled before launch.' : 'APP_DEBUG is disabled.',
            ['app_debug' => $debug],
        );

        $add(
            'env_file',
            File::exists(base_path('.env')) ? 'pass' : 'warning',
            File::exists(base_path('.env')) ? '.env exists for this installation.' : '.env is missing; create it from the buyer-safe template during deployment.',
        );

        $add(
            'app_key',
            filled(config('app.key')) ? 'pass' : 'warning',
            filled(config('app.key')) ? 'APP_KEY is configured.' : 'APP_KEY is missing; generate it during installation.',
        );
    }

    private function checkPhpExtensions(callable $add): void
    {
        foreach ((array) config('deployment_readiness.required_php_extensions', []) as $extension) {
            $add(
                'php_extension_'.$extension,
                extension_loaded($extension) ? 'pass' : 'fail',
                extension_loaded($extension) ? "PHP extension [{$extension}] is loaded." : "Required PHP extension [{$extension}] is missing.",
            );
        }

        foreach ((array) config('deployment_readiness.optional_php_extensions', []) as $extension) {
            $add(
                'php_optional_extension_'.$extension,
                extension_loaded($extension) ? 'pass' : 'warning',
                extension_loaded($extension) ? "Optional PHP extension [{$extension}] is loaded." : "Optional PHP extension [{$extension}] is not loaded; enable it if your hosting features need it.",
            );
        }
    }

    private function checkWritablePaths(callable $add): void
    {
        foreach ((array) config('deployment_readiness.writable_paths', []) as $path) {
            $absolute = base_path($path);
            $writable = File::isDirectory($absolute) && File::isWritable($absolute);

            $add(
                'writable_'.$this->key($path),
                $writable ? 'pass' : 'fail',
                $writable ? "[{$path}] is writable." : "[{$path}] must be writable by the PHP user.",
                ['path' => $path],
            );
        }
    }

    private function checkApplicationConfig(callable $add): void
    {
        $database = config('database.default');
        $databaseName = config("database.connections.{$database}.database");
        $mail = config('mail.default');
        $queue = config('queue.default');

        $add('database_config', filled($database) && filled($databaseName) ? 'pass' : 'warning', filled($databaseName) ? 'Database connection is configured.' : 'Database name is missing from configuration.', ['connection' => $database]);
        $add('mail_config', filled($mail) ? 'pass' : 'warning', filled($mail) ? "Mail driver [{$mail}] is configured." : 'Mail driver is missing.');
        $add('queue_config', filled($queue) ? 'pass' : 'warning', filled($queue) ? "Queue connection [{$queue}] is configured." : 'Queue connection is missing.');
    }

    private function checkPublicExposure(callable $add): void
    {
        foreach ((array) config('deployment_readiness.public_exposure_checks', []) as $path) {
            $publicPath = public_path($path);
            $exists = File::exists($publicPath);
            $status = $exists && $path !== 'public/build.zip' ? 'fail' : 'pass';

            if ($path === 'public/build.zip') {
                $status = 'warning';
            }

            $add(
                'public_exposure_'.$this->key($path),
                $status,
                $path === 'public/build.zip'
                    ? 'public/build.zip is not required as a runtime artifact and should not be used for deployment packaging.'
                    : ($exists ? "[{$path}] appears under public; remove or protect it." : "[{$path}] was not detected under public."),
                ['checked_path' => $path],
            );
        }
    }

    private function checkSchedulerAndQueues(callable $add): void
    {
        $add('scheduler_guidance', 'warning', 'Configure a cron entry for php artisan schedule:run every minute where hosting supports cron.');
        $add('queue_guidance', 'warning', 'Use sync/database queues on shared hosting, and Supervisor/systemd workers on VPS or cloud hosting.');
    }

    private function checkCommercialFoundations(callable $add): void
    {
        $add('installer_config', config('installer.enabled') ? 'pass' : 'warning', config('installer.enabled') ? 'Installer is enabled by configuration.' : 'Installer is disabled; enable it for fresh single-school installs.');
        $add('updates_config', config('updates.enabled') ? 'pass' : 'warning', config('updates.enabled') ? 'Update manager foundation is enabled.' : 'Update manager foundation is disabled.');
        $add('backups_config', config('backups.enabled') ? 'pass' : 'warning', config('backups.enabled') ? 'Backup manager foundation is enabled.' : 'Backup manager foundation is disabled.');
    }

    private function checkPublicBuildZip(callable $add): void
    {
        $add(
            'public_build_zip_runtime',
            'warning',
            'public/build.zip is not required by Laravel at runtime; deploy reviewed built assets instead of generated archives.',
            ['exists' => File::exists(public_path('build.zip'))],
        );
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
        return str($value)->replace(['\\', '/', '.', '-'], '_')->toString();
    }
}
