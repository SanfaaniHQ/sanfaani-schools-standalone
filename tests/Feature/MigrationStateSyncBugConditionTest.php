<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Migration State Synchronization regression tests.
 *
 * Validates the production drift condition where school_subscriptions exists
 * but Laravel's migrations table no longer records the create-table migration.
 */
class MigrationStateSyncBugConditionTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRATION = '2026_05_01_204102_create_school_subscriptions_table';

    private const TABLE = 'school_subscriptions';

    /**
     * Property 1: compatible drift is synchronized without data loss.
     */
    public function test_migration_synchronizes_existing_compatible_school_subscriptions_table(): void
    {
        $this->assertTrue(
            Schema::hasTable(self::TABLE),
            'school_subscriptions table should exist after RefreshDatabase'
        );

        $sample = $this->insertSampleData();
        $initialMigrationCount = DB::table('migrations')->count();
        $initialMaxBatch = (int) DB::table('migrations')->max('batch');
        $initialSubscriptionCount = DB::table(self::TABLE)->count();

        DB::table('migrations')
            ->where('migration', self::MIGRATION)
            ->delete();

        $this->assertFalse(
            DB::table('migrations')->where('migration', self::MIGRATION)->exists(),
            'Migration record should not exist in migrations table (simulating bug condition)'
        );

        $exitCode = Artisan::call('migrate', ['--force' => true]);

        $this->assertSame(0, $exitCode, 'Migration command should succeed after synchronization');

        $migrationRows = DB::table('migrations')
            ->where('migration', self::MIGRATION)
            ->get();

        $this->assertCount(1, $migrationRows, 'Migration should be recorded exactly once');
        $this->assertSame(
            $initialMaxBatch + 1,
            (int) $migrationRows->first()->batch,
            'Synchronized migration should use the next migration batch'
        );
        $this->assertSame(
            $initialMigrationCount,
            DB::table('migrations')->count(),
            'Synchronization should replace the missing migration record without duplicating state'
        );

        $this->assertTrue(
            Schema::hasTable(self::TABLE),
            'school_subscriptions table should still exist after synchronization'
        );
        $this->assertSame(
            $initialSubscriptionCount,
            DB::table(self::TABLE)->count(),
            'Existing subscription rows should be preserved'
        );

        $subscription = DB::table(self::TABLE)->find($sample['subscription_id']);

        $this->assertNotNull($subscription, 'The original subscription row should remain available');
        $this->assertSame($sample['school_id'], (int) $subscription->school_id);
        $this->assertSame($sample['plan_id'], (int) $subscription->subscription_plan_id);
        $this->assertSame('active', $subscription->status);
        $this->assertSame('paid', $subscription->payment_status);

        $this->assertInvalidSchoolReferenceIsRejected($sample['plan_id']);

        $statusExitCode = Artisan::call('migrate:status');
        $statusOutput = Artisan::output();

        $this->assertSame(0, $statusExitCode, 'migrate:status should succeed after synchronization');
        $this->assertStringContainsString(self::MIGRATION, $statusOutput);
        $this->assertStringContainsString('Ran', $statusOutput);

        $secondExitCode = Artisan::call('migrate', ['--force' => true]);

        $this->assertSame(0, $secondExitCode, 'A duplicate migrate attempt should be idempotent');
        $this->assertSame(
            1,
            DB::table('migrations')->where('migration', self::MIGRATION)->count(),
            'Duplicate migrate attempts should not create duplicate migration records'
        );
        $this->assertSame(
            $initialSubscriptionCount,
            DB::table(self::TABLE)->count(),
            'Duplicate migrate attempts should not mutate subscription data'
        );
    }

    /**
     * Property 2: stale or partial tables are not falsely certified as migrated.
     */
    public function test_migration_refuses_to_synchronize_incompatible_school_subscriptions_table(): void
    {
        $this->replaceSchoolSubscriptionsWithStaleTable();

        DB::table('migrations')
            ->where('migration', self::MIGRATION)
            ->delete();

        try {
            try {
                Artisan::call('migrate', ['--force' => true]);

                $this->fail('Incompatible school_subscriptions schema should not be marked as migrated');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('does not match', $exception->getMessage());
                $this->assertStringContainsString('missing columns', $exception->getMessage());
                $this->assertStringContainsString('school_id', $exception->getMessage());
                $this->assertStringContainsString('Refusing to mark an incomplete schema as migrated', $exception->getMessage());
            }

            $this->assertFalse(
                DB::table('migrations')->where('migration', self::MIGRATION)->exists(),
                'Invalid migration state should remain pending for manual repair'
            );
        } finally {
            $this->restoreSchoolSubscriptionsTable();
        }
    }

    /**
     * Insert sample data to verify data preservation after migration sync
     *
     * @return array{school_id: int, plan_id: int, subscription_id: int}
     */
    private function insertSampleData(): array
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name' => 'Test School for Migration Sync',
            'slug' => 'test-school-migration-sync',
            'status' => 'active',
            'subscription_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $planId = DB::table('subscription_plans')->insertGetId([
            'name' => 'Test Plan for Migration Sync',
            'slug' => 'test-plan-migration-sync',
            'price' => 5000.00,
            'billing_cycle' => 'term',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $subscriptionId = DB::table(self::TABLE)->insertGetId([
            'school_id' => $schoolId,
            'subscription_plan_id' => $planId,
            'status' => 'active',
            'billing_cycle' => 'term',
            'pricing_model' => 'per_student',
            'price' => 5000.00,
            'currency' => 'NGN',
            'amount_due' => 5000.00,
            'amount_paid' => 5000.00,
            'payment_status' => 'paid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'school_id' => (int) $schoolId,
            'plan_id' => (int) $planId,
            'subscription_id' => (int) $subscriptionId,
        ];
    }

    private function assertInvalidSchoolReferenceIsRejected(int $planId): void
    {
        try {
            DB::table(self::TABLE)->insert([
                'school_id' => 999999,
                'subscription_plan_id' => $planId,
                'status' => 'active',
                'billing_cycle' => 'term',
                'pricing_model' => 'per_student',
                'price' => 5000.00,
                'currency' => 'NGN',
                'amount_due' => 5000.00,
                'amount_paid' => 0.00,
                'payment_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->fail('school_subscriptions should reject subscriptions without a valid school_id');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }
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
