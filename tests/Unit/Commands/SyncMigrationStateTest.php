<?php

namespace Tests\Unit\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SyncMigrationStateTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRATION = '2026_05_01_204102_create_school_subscriptions_table';

    private const TABLE = 'school_subscriptions';

    public function test_dry_run_reports_sync_without_writing_migration_record(): void
    {
        $initialMigrationCount = DB::table('migrations')->count();

        $this->removeMigrationRecord();

        $exitCode = Artisan::call('migration:sync', ['--dry-run' => true]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString(self::MIGRATION, $output);
        $this->assertStringContainsString('Dry run only', $output);
        $this->assertFalse(DB::table('migrations')->where('migration', self::MIGRATION)->exists());
        $this->assertSame($initialMigrationCount - 1, DB::table('migrations')->count());
    }

    public function test_force_sync_records_missing_migration_once(): void
    {
        $initialMaxBatch = (int) DB::table('migrations')->max('batch');

        $this->removeMigrationRecord();

        $exitCode = Artisan::call('migration:sync', ['--force' => true]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Migration state synchronized successfully', $output);

        $migrationRows = DB::table('migrations')
            ->where('migration', self::MIGRATION)
            ->get();

        $this->assertCount(1, $migrationRows);
        $this->assertSame($initialMaxBatch + 1, (int) $migrationRows->first()->batch);

        $migrationCountAfterSync = DB::table('migrations')->count();

        $secondExitCode = Artisan::call('migration:sync', ['--force' => true]);

        $this->assertSame(0, $secondExitCode);
        $this->assertSame(
            1,
            DB::table('migrations')->where('migration', self::MIGRATION)->count()
        );
        $this->assertSame($migrationCountAfterSync, DB::table('migrations')->count());
    }

    public function test_sync_refuses_incompatible_school_subscriptions_schema(): void
    {
        $this->replaceSchoolSubscriptionsWithStaleTable();
        $this->removeMigrationRecord();

        try {
            $exitCode = Artisan::call('migration:sync', ['--force' => true]);
            $output = Artisan::output();

            $this->assertSame(1, $exitCode);
            $this->assertStringContainsString('not schema-compatible', $output);
            $this->assertStringContainsString('missing columns', $output);
            $this->assertStringContainsString('school_id', $output);
            $this->assertFalse(DB::table('migrations')->where('migration', self::MIGRATION)->exists());
        } finally {
            $this->restoreSchoolSubscriptionsTable();
        }
    }

    private function removeMigrationRecord(): void
    {
        DB::table('migrations')
            ->where('migration', self::MIGRATION)
            ->delete();
    }

    private function replaceSchoolSubscriptionsWithStaleTable(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists(self::TABLE);

            Schema::create(self::TABLE, function ($table) {
                $table->id();
                $table->string('status', 50)->default('active');
                $table->timestamps();
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function restoreSchoolSubscriptionsTable(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists(self::TABLE);
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        Artisan::call('migrate', ['--force' => true]);
    }
}
