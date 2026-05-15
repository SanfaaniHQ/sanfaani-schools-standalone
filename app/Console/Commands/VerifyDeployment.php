<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class VerifyDeployment extends Command
{
    protected $signature = 'sanfaani:deployment-verify';

    protected $description = 'Verify database target, migration safety, and critical table integrity before deployment.';

    private const REQUIRED_DATABASE = 'sanfaani_schools';

    private const CRITICAL_TABLES = [
        'schools',
        'users',
        'students',
        'student_results',
        'scratch_card_batches',
        'scratch_cards',
        'scratch_card_usages',
        'audit_logs',
    ];

    private const FORBIDDEN_MIGRATION_TOKENS = [
        'Schema::dropIfExists',
        'dropColumn(',
        'dropConstrainedForeignId(',
        'truncate(',
        '->delete(',
        'DB::statement("DROP',
        "DB::statement('DROP",
        'DROP TABLE',
        'TRUNCATE',
        'ALTER TABLE',
        ' DROP ',
    ];

    public function handle(): int
    {
        $this->info('Sanfaani deployment verification');

        if (! $this->verifyDatabaseTarget()) {
            return self::FAILURE;
        }

        if (! $this->verifyCriticalTables()) {
            return self::FAILURE;
        }

        if (! $this->verifyPendingMigrationSafety()) {
            return self::FAILURE;
        }

        $this->info('Deployment verification passed.');

        return self::SUCCESS;
    }

    private function verifyDatabaseTarget(): bool
    {
        $configured = config('database.connections.mysql.database');

        try {
            $connected = DB::connection()->getDatabaseName();
        } catch (Throwable $exception) {
            $this->error('Unable to connect to the configured database: '.$exception->getMessage());

            return false;
        }

        $this->line('Configured database: '.$configured);
        $this->line('Connected database: '.$connected);

        if ($configured !== self::REQUIRED_DATABASE || $connected !== self::REQUIRED_DATABASE) {
            $this->error('Deployment aborted: DB_DATABASE must remain '.self::REQUIRED_DATABASE.'.');

            return false;
        }

        return true;
    }

    private function verifyCriticalTables(): bool
    {
        foreach (self::CRITICAL_TABLES as $table) {
            if (! Schema::hasTable($table)) {
                $this->error('Missing critical table: '.$table);

                return false;
            }

            $count = DB::table($table)->count();
            $this->line($table.': '.$count.' rows');
        }

        return true;
    }

    private function verifyPendingMigrationSafety(): bool
    {
        $ran = DB::table('migrations')->pluck('migration')->all();
        $ran = array_flip(array_map('strval', $ran));
        $pending = [];

        foreach (File::files(database_path('migrations')) as $file) {
            $name = Str::before($file->getFilename(), '.php');

            if (! isset($ran[$name])) {
                $pending[] = $file->getPathname();
            }
        }

        $this->line('Pending migrations: '.count($pending));

        foreach ($pending as $path) {
            $contents = File::get($path);

            foreach (self::FORBIDDEN_MIGRATION_TOKENS as $token) {
                if (str_contains($contents, $token)) {
                    $this->error('Unsafe pending migration token "'.$token.'" found in '.$path);

                    return false;
                }
            }
        }

        return true;
    }
}
