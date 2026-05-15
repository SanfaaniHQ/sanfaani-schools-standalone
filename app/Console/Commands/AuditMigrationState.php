<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class AuditMigrationState extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:audit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit migration state and detect inconsistencies';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting migration state audit...');
        $this->newLine();

        // Get all tables from database
        $tables = $this->getDatabaseTables();

        // Get all migration records
        $recordedMigrations = $this->getRecordedMigrations();

        // Get all migration files
        $migrationFiles = $this->getMigrationFiles();

        // Detect inconsistencies
        $inconsistencies = $this->detectInconsistencies($tables, $recordedMigrations, $migrationFiles);

        // Display results
        $this->displayAuditResults($inconsistencies);

        return self::SUCCESS;
    }

    /**
     * Get all tables from the database
     */
    protected function getDatabaseTables(): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite uses sqlite_master table
            $tables = DB::select(
                "SELECT name as TABLE_NAME 
                 FROM sqlite_master 
                 WHERE type = 'table' 
                 AND name != 'migrations' 
                 AND name NOT LIKE 'sqlite_%'"
            );
        } else {
            // MySQL/MariaDB uses information_schema
            $databaseName = DB::getDatabaseName();
            $tables = DB::select(
                "SELECT TABLE_NAME 
                 FROM information_schema.TABLES 
                 WHERE TABLE_SCHEMA = ? 
                 AND TABLE_TYPE = 'BASE TABLE'
                 AND TABLE_NAME != 'migrations'",
                [$databaseName]
            );
        }

        return array_map(fn ($table) => $table->TABLE_NAME, $tables);
    }

    /**
     * Get all recorded migrations from migrations table
     */
    protected function getRecordedMigrations(): array
    {
        if (! Schema::hasTable('migrations')) {
            $this->warn('Migrations table does not exist!');

            return [];
        }

        return DB::table('migrations')
            ->orderBy('batch')
            ->orderBy('migration')
            ->get()
            ->pluck('migration')
            ->toArray();
    }

    /**
     * Get all migration files from database/migrations directory
     */
    protected function getMigrationFiles(): array
    {
        $migrationPath = database_path('migrations');

        if (! File::exists($migrationPath)) {
            $this->error("Migration directory not found: {$migrationPath}");

            return [];
        }

        $files = File::files($migrationPath);

        return array_map(function ($file) {
            return pathinfo($file->getFilename(), PATHINFO_FILENAME);
        }, $files);
    }

    /**
     * Detect migration inconsistencies
     */
    protected function detectInconsistencies(array $tables, array $recordedMigrations, array $migrationFiles): array
    {
        $inconsistencies = [
            'tables_without_migrations' => [],
            'orphaned_migration_records' => [],
            'pending_migrations_with_existing_tables' => [],
        ];

        // Find tables that exist but migrations not recorded
        foreach ($tables as $table) {
            $matchingMigration = $this->findMigrationForTable($table, $migrationFiles);

            if ($matchingMigration && ! in_array($matchingMigration, $recordedMigrations)) {
                $inconsistencies['tables_without_migrations'][] = [
                    'table' => $table,
                    'migration' => $matchingMigration,
                ];
            }
        }

        // Find migration records without corresponding tables
        foreach ($recordedMigrations as $migration) {
            $tableName = $this->extractTableNameFromMigration($migration);

            if ($tableName && ! in_array($tableName, $tables)) {
                $inconsistencies['orphaned_migration_records'][] = [
                    'migration' => $migration,
                    'expected_table' => $tableName,
                ];
            }
        }

        // Find pending migrations where tables already exist
        $pendingMigrations = array_diff($migrationFiles, $recordedMigrations);

        foreach ($pendingMigrations as $migration) {
            $tableName = $this->extractTableNameFromMigration($migration);

            if ($tableName && in_array($tableName, $tables)) {
                $inconsistencies['pending_migrations_with_existing_tables'][] = [
                    'migration' => $migration,
                    'table' => $tableName,
                ];
            }
        }

        return $inconsistencies;
    }

    /**
     * Find migration file for a given table name
     */
    protected function findMigrationForTable(string $table, array $migrationFiles): ?string
    {
        foreach ($migrationFiles as $migration) {
            if (str_contains($migration, "create_{$table}_table")) {
                return $migration;
            }
        }

        return null;
    }

    /**
     * Extract table name from migration filename
     */
    protected function extractTableNameFromMigration(string $migration): ?string
    {
        // Match patterns like: create_table_name_table, add_column_to_table_name, etc.
        if (preg_match('/create_(.+)_table$/', $migration, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Display audit results
     */
    protected function displayAuditResults(array $inconsistencies): void
    {
        $hasIssues = false;

        // Tables without migration records
        if (! empty($inconsistencies['tables_without_migrations'])) {
            $hasIssues = true;
            $this->error('⚠ Tables exist but migrations not recorded:');
            $this->newLine();

            $rows = array_map(fn ($item) => [$item['table'], $item['migration']],
                $inconsistencies['tables_without_migrations']);

            $this->table(['Table Name', 'Migration File'], $rows);
            $this->newLine();
        }

        // Orphaned migration records
        if (! empty($inconsistencies['orphaned_migration_records'])) {
            $hasIssues = true;
            $this->warn('⚠ Migration records exist but tables do not:');
            $this->newLine();

            $rows = array_map(fn ($item) => [$item['migration'], $item['expected_table']],
                $inconsistencies['orphaned_migration_records']);

            $this->table(['Migration Name', 'Expected Table'], $rows);
            $this->newLine();
        }

        // Pending migrations with existing tables
        if (! empty($inconsistencies['pending_migrations_with_existing_tables'])) {
            $hasIssues = true;
            $this->error('⚠ Pending migrations but tables already exist:');
            $this->newLine();

            $rows = array_map(fn ($item) => [$item['migration'], $item['table']],
                $inconsistencies['pending_migrations_with_existing_tables']);

            $this->table(['Migration File', 'Existing Table'], $rows);
            $this->newLine();
        }

        if (! $hasIssues) {
            $this->info('✓ No migration state inconsistencies detected!');
            $this->info('All migrations are properly synchronized with the database.');
        } else {
            $this->newLine();
            $this->info('Recommended Actions:');
            $this->line('  1. Review the inconsistencies above');
            $this->line('  2. Run "php artisan migration:sync" to synchronize migration state');
            $this->line('  3. Verify the changes with "php artisan migrate:status"');
        }
    }
}
