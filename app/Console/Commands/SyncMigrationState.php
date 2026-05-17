<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SyncMigrationState extends Command
{
    protected $signature = 'migration:sync
        {--dry-run : Preview changes without applying}
        {--force : Skip confirmation prompt}';

    protected $description = 'Synchronize migration state for existing tables';

    private const MIGRATION = '2026_05_01_204102_create_school_subscriptions_table';

    private const TABLE = 'school_subscriptions';

    public function handle(): int
    {
        if (! Schema::hasTable('migrations')) {
            $this->error('Cannot synchronize migration state because the migrations table does not exist.');

            return self::FAILURE;
        }

        if (! File::exists(database_path('migrations/'.self::MIGRATION.'.php'))) {
            $this->error('Cannot synchronize migration state because the school_subscriptions migration file is missing.');

            return self::FAILURE;
        }

        if (! Schema::hasTable(self::TABLE) || $this->migrationRecorded()) {
            $this->info('No migration state changes needed.');

            return self::SUCCESS;
        }

        $problems = $this->compatibilityProblems();

        if ($problems !== []) {
            $this->error('Cannot synchronize '.self::MIGRATION.' because '.self::TABLE.' is not schema-compatible.');

            foreach ($problems as $problem) {
                $this->line('  - '.$problem);
            }

            $this->line('Create an additive repair migration before marking this migration as ran.');

            return self::FAILURE;
        }

        $batch = $this->nextBatch();

        $this->table(['Migration', 'Existing Table', 'Batch'], [
            [self::MIGRATION, self::TABLE, $batch],
        ]);

        if ($this->option('dry-run')) {
            $this->info('Dry run only. No migration records were written.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Synchronize 1 migration record?', false)) {
            $this->warn('Migration state synchronization cancelled.');

            return self::FAILURE;
        }

        $synced = false;

        DB::transaction(function () use ($batch, &$synced): void {
            if ($this->migrationRecorded()) {
                return;
            }

            DB::table('migrations')->insert([
                'migration' => self::MIGRATION,
                'batch' => $batch,
            ]);

            $synced = true;
        });

        if (! $synced) {
            $this->info('No migration state changes needed.');

            return self::SUCCESS;
        }

        Log::info('Migration state synchronized.', [
            'migration' => self::MIGRATION,
            'table' => self::TABLE,
            'batch' => $batch,
        ]);

        $this->info('Migration state synchronized successfully.');

        return self::SUCCESS;
    }

    private function migrationRecorded(): bool
    {
        return DB::table('migrations')
            ->where('migration', self::MIGRATION)
            ->exists();
    }

    private function nextBatch(): int
    {
        return ((int) DB::table('migrations')->max('batch')) + 1;
    }

    /**
     * @return array<int, string>
     */
    private function compatibilityProblems(): array
    {
        $problems = [];
        $missingColumns = array_values(array_filter(
            $this->requiredColumns(),
            fn (string $column): bool => ! Schema::hasColumn(self::TABLE, $column)
        ));

        if ($missingColumns !== []) {
            $problems[] = 'missing columns: '.implode(', ', $missingColumns);
        }

        $missingIndexes = array_values(array_filter(
            $this->requiredIndexes(),
            fn (string $index): bool => ! $this->hasIndex($index)
        ));

        if ($missingIndexes !== []) {
            $problems[] = 'missing indexes: '.implode(', ', $missingIndexes);
        }

        $missingForeignKeys = array_values(array_filter(
            $this->requiredForeignKeys(),
            fn (array $foreignKey): bool => ! $this->hasForeignKey($foreignKey['column'], $foreignKey['table'])
        ));

        if ($missingForeignKeys !== []) {
            $problems[] = 'missing foreign keys: '.implode(', ', array_map(
                fn (array $foreignKey): string => "{$foreignKey['column']} -> {$foreignKey['table']}.id",
                $missingForeignKeys
            ));
        }

        return $problems;
    }

    /**
     * @return array<int, string>
     */
    private function requiredColumns(): array
    {
        return [
            'id',
            'school_id',
            'subscription_plan_id',
            'status',
            'starts_at',
            'ends_at',
            'trial_ends_at',
            'grace_ends_at',
            'billing_cycle',
            'pricing_model',
            'price',
            'currency',
            'student_count',
            'amount_due',
            'amount_paid',
            'payment_status',
            'payment_reference',
            'activated_by',
            'upgraded_from_subscription_id',
            'downgraded_from_subscription_id',
            'superseded_by_subscription_id',
            'plan_name_snapshot',
            'price_snapshot',
            'billing_cycle_snapshot',
            'pricing_model_snapshot',
            'features_snapshot',
            'metadata',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function requiredIndexes(): array
    {
        return [
            'school_subscriptions_school_status_index',
            'school_subscriptions_plan_status_index',
            'school_subscriptions_period_index',
        ];
    }

    /**
     * @return array<int, array{column: string, table: string}>
     */
    private function requiredForeignKeys(): array
    {
        return [
            ['column' => 'school_id', 'table' => 'schools'],
            ['column' => 'subscription_plan_id', 'table' => 'subscription_plans'],
        ];
    }

    private function hasIndex(string $index): bool
    {
        try {
            return Schema::hasIndex(self::TABLE, $index);
        } catch (Throwable) {
            return false;
        }
    }

    private function hasForeignKey(string $column, string $referencedTable): bool
    {
        try {
            $foreignKeys = Schema::getForeignKeys(self::TABLE);
        } catch (Throwable) {
            return false;
        }

        foreach ($foreignKeys as $foreignKey) {
            if (
                in_array($column, $foreignKey['columns'] ?? [], true)
                && ($foreignKey['foreign_table'] ?? null) === $referencedTable
            ) {
                return true;
            }
        }

        return false;
    }
}
