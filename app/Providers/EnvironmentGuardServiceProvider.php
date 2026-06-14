<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class EnvironmentGuardServiceProvider extends ServiceProvider
{
    private const BLOCKED_COMMANDS = [
        'db:seed',
        'db:wipe',
        'migrate:fresh',
        'migrate:refresh',
    ];

    public function boot(): void
    {
        $this->guardDatabaseTarget();
        $this->guardArtisanCommands();
    }

    private function guardDatabaseTarget(): void
    {
        if (! (bool) config('sanfaani.database.name_guard.enabled', false)) {
            return;
        }

        if (config('database.default') !== 'mysql') {
            return;
        }

        $db = (string) config('database.connections.mysql.database');
        $requiredFragment = (string) config('sanfaani.database.name_guard.required_fragment', 'sanfaani_schools');

        if ($requiredFragment === '' || ! str_contains($db, $requiredFragment)) {
            logger()->critical('FATAL: Wrong database target blocked.', [
                'database' => $db,
                'required_database_fragment' => $requiredFragment,
                'running_in_console' => app()->runningInConsole(),
                'url' => app()->runningInConsole() ? null : request()->url(),
            ]);

            if (app()->runningInConsole()) {
                throw new RuntimeException('Database configuration error: DB_DATABASE must contain '.$requiredFragment.'. Current value: '.$db);
            }

            abort(500, 'Database configuration error. Contact administrator immediately.');
        }
    }

    private function guardArtisanCommands(): void
    {
        if (! app()->runningInConsole()) {
            return;
        }

        $this->app['events']->listen(CommandStarting::class, function (CommandStarting $event): void {
            if (in_array($event->command, self::BLOCKED_COMMANDS, true)) {
                if ($this->isIsolatedTestingDatabase()) {
                    return;
                }

                logger()->critical('Blocked destructive Artisan command.', [
                    'command' => $event->command,
                ]);

                throw new RuntimeException('Blocked destructive Artisan command: '.$event->command.'. Use additive migrations and deployment verification only.');
            }
        });
    }

    private function isIsolatedTestingDatabase(): bool
    {
        $environment = env('APP_ENV') ?: app()->environment();
        $connection = env('DB_CONNECTION') ?: config('database.default');
        $database = env('DB_DATABASE') ?: config('database.connections.sqlite.database');

        return $environment === 'testing'
            && $connection === 'sqlite'
            && $database === ':memory:';
    }
}
