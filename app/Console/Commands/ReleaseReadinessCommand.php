<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

class ReleaseReadinessCommand extends Command
{
    protected $signature = 'release:check-readiness {--json : Output a JSON readiness report}';

    protected $description = 'Read-only enterprise release readiness checks for tests, docs, commands, and packaging guardrails.';

    public function handle(): int
    {
        $checks = [];

        $add = function (string $key, string $status, string $message, array $context = []) use (&$checks): void {
            $checks[] = compact('key', 'status', 'message', 'context');
        };

        $this->checkGit($add);
        $this->checkRequiredDocs($add);
        $this->checkRequiredFiles($add);
        $this->checkCommands($add);
        $this->checkReleaseConfig($add);
        $this->checkMarketplaceAndCommercialDocs($add);
        $this->checkValidationInstructions($add);

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
                'Release readiness report complete: %d pass, %d warning, %d fail. No tests were run and no files were modified.',
                $summary['pass'],
                $summary['warning'],
                $summary['fail'],
            ));
        }

        return self::SUCCESS;
    }

    private function checkGit(callable $add): void
    {
        $branch = $this->git(['branch', '--show-current']);
        $status = $this->git(['status', '--short']);
        $dirty = collect(explode("\n", trim($status)))
            ->filter()
            ->values()
            ->all();

        $protectedDirty = collect((array) config('release.prohibited_dirty_files', []))
            ->filter(fn (string $path): bool => collect($dirty)->contains(fn (string $line): bool => str_contains($line, $path)))
            ->values()
            ->all();

        $add(
            'git_branch',
            filled($branch) ? 'pass' : 'warning',
            filled($branch) ? "Current branch is [{$branch}]." : 'Current branch could not be detected.',
            ['branch' => $branch],
        );

        $add(
            'dirty_working_tree',
            $dirty === [] ? 'pass' : 'warning',
            $dirty === [] ? 'Working tree is clean.' : 'Working tree has local changes; review before release.',
            ['dirty_count' => count($dirty)],
        );

        $add(
            'protected_dirty_files',
            $protectedDirty === [] ? 'pass' : 'warning',
            $protectedDirty === [] ? 'No protected dirty files detected.' : 'Protected dirty files are present and must not be included unintentionally.',
            ['protected_dirty_files' => $protectedDirty],
        );
    }

    private function checkRequiredDocs(callable $add): void
    {
        $missing = collect((array) config('release.required_docs', []))
            ->filter(fn (string $path): bool => ! File::exists(base_path($path)))
            ->values()
            ->all();

        $add(
            'required_release_docs',
            $missing === [] ? 'pass' : 'fail',
            $missing === [] ? 'All required release docs exist.' : 'Some required release docs are missing.',
            ['missing' => $missing],
        );
    }

    private function checkRequiredFiles(callable $add): void
    {
        foreach ([
            '.env.example',
            '.env.marketplace.example',
            'phpunit.xml',
            'composer.json',
            'package.json',
            'config/release.php',
            'config/packaging.php',
            'config/deployment_readiness.php',
            'config/performance.php',
            'config/security.php',
            'config/branding.php',
            'config/updates.php',
            'config/backups.php',
        ] as $path) {
            $add(
                'file_'.$this->key($path),
                File::exists(base_path($path)) ? 'pass' : 'fail',
                File::exists(base_path($path)) ? "[{$path}] exists." : "[{$path}] is missing.",
                ['path' => $path],
            );
        }
    }

    private function checkCommands(callable $add): void
    {
        $commands = Artisan::all();

        foreach ([
            'deployment:check-readiness',
            'performance:audit',
            'security:audit',
            'marketplace:validate-package',
            'release:check-readiness',
        ] as $command) {
            $add(
                'command_'.$this->key($command),
                array_key_exists($command, $commands) ? 'pass' : 'fail',
                array_key_exists($command, $commands) ? "[{$command}] is registered." : "[{$command}] is not registered.",
                ['command' => $command],
            );
        }
    }

    private function checkReleaseConfig(callable $add): void
    {
        $channels = (array) config('release.release_channels', []);
        $requiredCommands = (array) config('release.required_commands', []);
        $protected = (array) config('release.prohibited_dirty_files', []);

        $add('release_channels', $channels === [] ? 'fail' : 'pass', $channels === [] ? 'No release channels are configured.' : 'Release channels are configured.', ['channels' => $channels]);
        $add('default_channel', in_array(config('release.default_channel'), $channels, true) ? 'pass' : 'fail', 'Default release channel is configured.', ['default_channel' => config('release.default_channel')]);
        $add('versioning_pattern', filled(config('release.versioning_pattern')) ? 'pass' : 'fail', 'Versioning pattern is configured.');

        foreach ([
            'php artisan test',
            'php artisan route:list',
            'git diff --check',
            'deployment:check-readiness',
            'performance:audit',
            'security:audit',
            'marketplace:validate-package',
        ] as $needle) {
            $present = collect($requiredCommands)->contains(fn (string $command): bool => str_contains($command, $needle));
            $add('required_command_reference_'.$this->key($needle), $present ? 'pass' : 'fail', $present ? "Required commands reference [{$needle}]." : "Required commands do not reference [{$needle}].");
        }

        foreach ([
            'public/build.zip',
            'database/migrations/2026_05_01_173857_create_result_publications_table.php',
        ] as $path) {
            $add(
                'prohibited_dirty_file_'.$this->key($path),
                in_array($path, $protected, true) ? 'pass' : 'fail',
                in_array($path, $protected, true) ? "[{$path}] is listed as protected." : "[{$path}] is missing from protected dirty files.",
            );
        }
    }

    private function checkMarketplaceAndCommercialDocs(callable $add): void
    {
        foreach ([
            'docs/updates/update-system-plan.md',
            'docs/backups/backup-system-plan.md',
            'docs/marketplace/package-validation-checklist.md',
            'docs/deployment/deployment-troubleshooting.md',
            'docs/security/production-security-hardening.md',
            'docs/white-label/white-label-readiness.md',
        ] as $path) {
            $add(
                'commercial_doc_'.$this->key($path),
                File::exists(base_path($path)) ? 'pass' : 'fail',
                File::exists(base_path($path)) ? "[{$path}] exists." : "[{$path}] is missing.",
            );
        }
    }

    private function checkValidationInstructions(callable $add): void
    {
        $add(
            'validation_commands_documented',
            count((array) config('release.required_commands', [])) >= 10 ? 'pass' : 'fail',
            'Release config lists focused filters, full suite, route list, readiness commands, and diff check.',
            ['commands' => config('release.required_commands', [])],
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
