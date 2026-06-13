<?php

namespace App\Services\Installer;

use App\Services\Security\SecretRedactionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class InstallerDatabaseService
{
    public function status(): array
    {
        $status = [
            'connection' => (string) config('database.default'),
            'database_configured' => filled($this->databaseName()),
            'database_name' => filled($this->databaseName()) ? 'Configured' : 'Not configured',
            'connected' => false,
            'migrations_table_exists' => false,
            'pending_migrations_count' => null,
            'error' => null,
        ];

        try {
            DB::connection()->getPdo();

            $status['connected'] = true;
            $status['migrations_table_exists'] = Schema::hasTable('migrations');
            $status['pending_migrations_count'] = $this->pendingMigrationsCount($status['migrations_table_exists']);
        } catch (Throwable $exception) {
            $status['error'] = $this->sanitizeError($exception->getMessage());
        }

        return $status;
    }

    private function pendingMigrationsCount(bool $repositoryExists): ?int
    {
        try {
            $migrator = app('migrator');
            $files = $migrator->getMigrationFiles(database_path('migrations'));

            if (! $repositoryExists) {
                return count($files);
            }

            return count(array_diff(array_keys($files), $migrator->getRepository()->getRan()));
        } catch (Throwable) {
            return null;
        }
    }

    private function databaseName(): ?string
    {
        $connection = (string) config('database.default');
        $database = config("database.connections.{$connection}.database");

        return filled($database) ? (string) $database : null;
    }

    private function sanitizeError(string $message): string
    {
        foreach ([
            (string) config('database.connections.'.config('database.default').'.password'),
            (string) config('database.connections.'.config('database.default').'.username'),
        ] as $secret) {
            if (filled($secret)) {
                $message = str_replace($secret, '[hidden]', $message);
            }
        }

        return app(SecretRedactionService::class)->redact($message) ?: 'Database connection error.';
    }
}
