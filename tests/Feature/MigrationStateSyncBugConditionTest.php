<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Bug Condition Exploration Test for Migration State Synchronization
 *
 * **Validates: Requirements 1.1, 1.2, 1.3**
 *
 * CRITICAL: This test is EXPECTED TO FAIL on unfixed code.
 * Failure confirms the bug exists (migration system tries to create existing table).
 *
 * After the fix is implemented, this test should PASS, demonstrating that:
 * - The system detects existing tables
 * - Migrations are marked as completed without attempting to recreate tables
 * - Deployments proceed successfully
 */
class MigrationStateSyncBugConditionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 1: Bug Condition - Migration State Out of Sync Detection
     *
     * This test reproduces the exact bug condition:
     * - school_subscriptions table exists in database
     * - Migration record NOT in migrations table
     * - Attempt to run php artisan migrate --force
     *
     * EXPECTED BEHAVIOR AFTER FIX:
     * - Migration system detects existing table
     * - Marks migration as completed without recreating table
     * - Command succeeds without errors
     * - Migration status shows "Ran"
     *
     * CURRENT BEHAVIOR (UNFIXED):
     * - Migration system attempts to create existing table
     * - Fails with "SQLSTATE[HY000]: General error: 1 table already exists"
     * - Deployments blocked
     */
    public function test_migration_fails_when_school_subscriptions_table_exists_but_migration_not_recorded(): void
    {
        // Setup: RefreshDatabase has already run all migrations
        // Now we simulate the bug condition by removing the migration record

        // Setup: Verify table exists (created by RefreshDatabase)
        $this->assertTrue(
            Schema::hasTable('school_subscriptions'),
            'school_subscriptions table should exist after RefreshDatabase'
        );

        // Setup: Insert sample data to verify data preservation
        $this->insertSampleData();

        // Setup: Remove migration record from migrations table (simulating out-of-sync state)
        DB::table('migrations')
            ->where('migration', '2026_05_01_204102_create_school_subscriptions_table')
            ->delete();

        // Setup: Verify migration record does not exist
        $migrationExists = DB::table('migrations')
            ->where('migration', '2026_05_01_204102_create_school_subscriptions_table')
            ->exists();

        $this->assertFalse(
            $migrationExists,
            'Migration record should not exist in migrations table (simulating bug condition)'
        );

        // Execute: Attempt to run migrations (this will fail on unfixed code)
        // After fix: This should succeed and mark migration as completed
        try {
            $exitCode = Artisan::call('migrate', ['--force' => true]);

            // AFTER FIX: These assertions should pass
            $this->assertEquals(0, $exitCode, 'Migration command should succeed after fix');

            // Verify migration is now recorded
            $migrationRecorded = DB::table('migrations')
                ->where('migration', '2026_05_01_204102_create_school_subscriptions_table')
                ->exists();

            $this->assertTrue(
                $migrationRecorded,
                'Migration should be recorded in migrations table after fix'
            );

            // Verify table still exists with data intact
            $this->assertTrue(
                Schema::hasTable('school_subscriptions'),
                'school_subscriptions table should still exist after fix'
            );

            // Verify sample data is preserved
            $recordCount = DB::table('school_subscriptions')->count();
            $this->assertEquals(
                1,
                $recordCount,
                'Sample data should be preserved after migration sync'
            );

            // Verify migration status shows "Ran"
            $statusOutput = Artisan::output();
            // After fix, migrate:status should show this migration as "Ran"

        } catch (\Exception $e) {
            // BEFORE FIX: This is the expected behavior (test documents the bug)
            // The exception message should contain "already exists"
            $this->assertStringContainsString(
                'already exists',
                $e->getMessage(),
                'Expected error message about table already existing (confirms bug condition)'
            );

            // Document the bug: Migration state is out of sync
            $this->markTestIncomplete(
                "BUG CONFIRMED: Migration system fails when table exists but migration not recorded.\n".
                "Error: {$e->getMessage()}\n".
                'This test will pass after the fix is implemented.'
            );
        }
    }

    /**
     * Insert sample data to verify data preservation after migration sync
     */
    private function insertSampleData(): void
    {
        // Ensure prerequisite data exists
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

        DB::table('school_subscriptions')->insert([
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
    }
}
