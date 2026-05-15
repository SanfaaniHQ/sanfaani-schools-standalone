# Implementation Plan

## Phase 1: Exploration and Preservation Testing (BEFORE Fix)

- [x] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - Migration State Out of Sync Detection
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bug exists
  - **Scoped PBT Approach**: Scope the property to concrete failing cases: `school_subscriptions` table exists but migration `2026_05_01_204102_create_school_subscriptions_table` not recorded in `migrations` table
  - Test implementation details from Bug Condition in design:
    - Create test database with `school_subscriptions` table existing (with sample data)
    - Remove migration record from `migrations` table
    - Attempt to run `php artisan migrate --force`
    - Assert that command fails with "SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'school_subscriptions' already exists"
  - The test assertions should match the Expected Behavior Properties from design:
    - After fix: migration should be marked as completed without attempting to recreate table
    - After fix: `php artisan migrate:status` should show migration as "Ran"
    - After fix: deployments should proceed successfully
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bug exists)
  - Document counterexamples found to understand root cause
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 1.1, 1.2, 1.3_

- [x] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Fresh Database and Normal Migration Behavior
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-buggy inputs:
    - Fresh database installation (no existing tables)
    - Normal migration execution (all migrations pending, no tables exist)
    - Rollback operations on properly tracked migrations
    - Pretend mode operations
  - Write property-based tests capturing observed behavior patterns from Preservation Requirements:
    - **Test 2.1**: Fresh database creates all tables from scratch with correct schema
    - **Test 2.2**: All migrations tracked correctly in `migrations` table with sequential batch numbers
    - **Test 2.3**: Existing data in tables remains intact after any migration operations
    - **Test 2.4**: Tenant isolation via `school_id` foreign keys enforced correctly
    - **Test 2.5**: Foreign key constraints (e.g., `school_subscriptions.school_id` → `schools.id`) enforced
    - **Test 2.6**: `php artisan migrate:rollback` works correctly for tracked migrations
    - **Test 2.7**: `php artisan migrate --pretend` shows dry-run without making changes
    - **Test 2.8**: Future migrations tracked correctly after fix is applied
  - Property-based testing generates many test cases for stronger guarantees
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8_

## Phase 2: Migration Audit Command Implementation

- [ ] 3. Create migration state audit command

  - [x] 3.1 Create `AuditMigrationState` command class
    - Create file `app/Console/Commands/AuditMigrationState.php`
    - Extend `Illuminate\Console\Command`
    - Set signature: `migration:audit`
    - Set description: "Audit migration state and detect inconsistencies"
    - _Requirements: 2.4_

  - [ ] 3.2 Implement table existence detection
    - Query `information_schema.TABLES` to get all tables in current database
    - Filter out system tables (`migrations`, `password_resets`, `personal_access_tokens`, etc.)
    - Store table names in array for comparison
    - _Requirements: 2.4_

  - [ ] 3.3 Implement migration record detection
    - Query `migrations` table to get all recorded migration names
    - Parse migration file names from `database/migrations/` directory
    - Cross-reference migration files with migration records
    - _Requirements: 2.4_

  - [ ] 3.4 Implement out-of-sync detection logic
    - Identify tables that exist but migrations not recorded (primary bug condition)
    - Identify migrations recorded but tables don't exist (orphaned records)
    - Identify migrations pending but tables already exist
    - Store findings in structured array
    - _Bug_Condition: isBugCondition(input) where input.tableExistsInDatabase(input.migrationTableName) AND NOT input.migrationRecordedInMigrationsTable(input.migrationName)_
    - _Requirements: 1.1, 1.2, 2.4_

  - [ ] 3.5 Implement schema drift detection
    - For each table with a migration file:
      - Parse migration file to extract expected schema (columns, indexes, foreign keys)
      - Query `information_schema.COLUMNS` for actual columns
      - Query `information_schema.STATISTICS` for actual indexes
      - Query `information_schema.KEY_COLUMN_USAGE` for actual foreign keys
      - Compare expected vs actual schema
      - Identify missing or extra schema elements
    - _Requirements: 2.5_

  - [ ] 3.6 Implement report generation
    - Format findings into readable report with sections:
      - Out-of-sync migrations (table exists, migration not recorded)
      - Orphaned migration records (migration recorded, table doesn't exist)
      - Schema drift issues (missing columns, indexes, foreign keys)
      - Recommended corrective actions
    - Include SQL preview for manual verification
    - Support `--json` flag for machine-readable output
    - _Requirements: 2.4, 2.5_

  - [ ] 3.7 Add command to kernel
    - Register command in `app/Console/Kernel.php` commands array
    - Test command execution: `php artisan migration:audit`
    - Verify output format and accuracy
    - _Requirements: 2.4_

## Phase 3: Migration Sync Command Implementation

- [ ] 4. Create migration state synchronization command

  - [ ] 4.1 Create `SyncMigrationState` command class
    - Create file `app/Console/Commands/SyncMigrationState.php`
    - Extend `Illuminate\Console\Command`
    - Set signature: `migration:sync {--dry-run : Preview changes without applying} {--force : Skip confirmation prompt}`
    - Set description: "Synchronize migration state for existing tables"
    - _Requirements: 2.1, 2.2_

  - [ ] 4.2 Implement safety checks
    - Verify database connection is active
    - Check that `migrations` table exists
    - Verify no active transactions
    - Validate migration files exist in `database/migrations/`
    - _Requirements: 2.1_

  - [ ] 4.3 Implement batch number calculation
    - Query `SELECT MAX(batch) FROM migrations` to get current max batch
    - Calculate next batch number: `maxBatch + 1`
    - Handle edge case: empty `migrations` table (batch = 1)
    - _Requirements: 2.2_

  - [ ] 4.4 Implement selective synchronization logic
    - For each out-of-sync migration identified by audit:
      - Verify table exists in database using `Schema::hasTable()`
      - Verify migration file exists in `database/migrations/`
      - Check if migration already recorded (idempotent check)
      - Prepare insert statement: `INSERT INTO migrations (migration, batch) VALUES (?, ?)`
      - Skip if migration already recorded
    - _Bug_Condition: isBugCondition(input) where input.tableExistsInDatabase(input.migrationTableName) AND NOT input.migrationRecordedInMigrationsTable(input.migrationName)_
    - _Expected_Behavior: expectedBehavior(result) where result.migrationRecorded = true AND result.tableExists = true AND result.noErrors = true_
    - _Requirements: 2.1, 2.2_

  - [ ] 4.5 Implement transaction wrapping
    - Wrap all `migrations` table inserts in `DB::transaction()`
    - Ensure atomicity: all inserts succeed or all rollback
    - Handle transaction errors gracefully with rollback
    - _Requirements: 2.1_

  - [ ] 4.6 Implement dry-run mode
    - When `--dry-run` flag present, preview changes without applying
    - Display migrations that would be synchronized
    - Show batch numbers and timestamps that would be used
    - Do not modify database in dry-run mode
    - _Requirements: 2.1_

  - [ ] 4.7 Implement confirmation prompt
    - Display summary of changes to be made
    - Prompt user: "Synchronize N migrations? (yes/no)"
    - Skip prompt if `--force` flag present
    - Abort if user declines
    - _Requirements: 2.1_

  - [ ] 4.8 Implement logging
    - Log all synchronization actions to Laravel log
    - Include: migration name, batch number, timestamp, success/failure
    - Log dry-run operations separately
    - Create audit trail for compliance
    - _Requirements: 2.1, 2.2_

  - [ ] 4.9 Add command to kernel and test
    - Register command in `app/Console/Kernel.php`
    - Test dry-run mode: `php artisan migration:sync --dry-run`
    - Test force mode: `php artisan migration:sync --force`
    - Test interactive mode: `php artisan migration:sync`
    - Verify idempotent behavior (run twice, no duplicates)
    - _Requirements: 2.1, 2.2_

## Phase 4: Schema Drift Corrective Generator

- [ ] 5. Create schema drift corrective migration generator

  - [ ] 5.1 Create `GenerateCorrectiveMigration` command class
    - Create file `app/Console/Commands/GenerateCorrectiveMigration.php`
    - Extend `Illuminate\Console\Command`
    - Set signature: `migration:generate-corrective {table : Table name to generate corrective migration for}`
    - Set description: "Generate corrective migration for schema drift"
    - _Requirements: 2.5_

  - [ ] 5.2 Implement schema comparison logic
    - Parse migration file for specified table to extract expected schema
    - Query actual database schema using `information_schema`
    - Compare columns: name, type, nullable, default, length
    - Compare indexes: name, columns, unique flag
    - Compare foreign keys: name, columns, referenced table, referenced columns, onDelete, onUpdate
    - Identify missing elements (in migration but not in database)
    - _Requirements: 2.5_

  - [ ] 5.3 Implement migration file generation
    - Create migration file with naming: `YYYY_MM_DD_HHMMSS_repair_{table_name}_schema_drift.php`
    - Generate `up()` method with additive operations only:
      - `Schema::table()` wrapper
      - `$table->addColumn()` for missing columns
      - `$table->index()` for missing indexes
      - `$table->foreign()` for missing foreign keys
    - Generate `down()` method with rollback operations:
      - `$table->dropColumn()` for added columns
      - `$table->dropIndex()` for added indexes
      - `$table->dropForeign()` for added foreign keys
    - _Requirements: 2.5_

  - [ ] 5.4 Implement safe operation validation
    - Only allow additive operations (add column, add index, add foreign key)
    - Reject destructive operations (drop column, drop table, modify column type)
    - For new columns, use sensible defaults or nullable to avoid data issues
    - Validate index names match migration definitions
    - Validate foreign key constraints have proper `onDelete` and `onUpdate` actions
    - _Preservation: All existing data must remain intact_
    - _Requirements: 2.5, 2.6, 3.2_

  - [ ] 5.5 Implement migration file writing
    - Write generated migration to `database/migrations/` directory
    - Use proper file permissions (0644)
    - Display success message with file path
    - Display preview of generated migration code
    - _Requirements: 2.5_

  - [ ] 5.6 Add command to kernel and test
    - Register command in `app/Console/Kernel.php`
    - Test with table missing indexes: `php artisan migration:generate-corrective school_subscriptions`
    - Verify generated migration file is valid PHP
    - Verify generated migration can be run successfully
    - Verify schema drift is corrected after running generated migration
    - _Requirements: 2.5_

## Phase 5: One-Time Fix Migration for school_subscriptions

- [ ] 6. Create one-time migration to fix school_subscriptions state

  - [ ] 6.1 Create migration file
    - Create file `database/migrations/2026_05_14_000002_sync_school_subscriptions_migration_state.php`
    - Use timestamp that sorts after existing migrations
    - _Requirements: 2.1_

  - [ ] 6.2 Implement up() method
    - Check if `school_subscriptions` table exists using `Schema::hasTable('school_subscriptions')`
    - Check if migration `2026_05_01_204102_create_school_subscriptions_table` is recorded in `migrations` table
    - If table exists but migration not recorded:
      - Calculate next batch number: `DB::table('migrations')->max('batch') + 1`
      - Insert migration record: `DB::table('migrations')->insert(['migration' => '2026_05_01_204102_create_school_subscriptions_table', 'batch' => $batch])`
      - Log synchronization action
    - If migration already recorded, skip (idempotent)
    - _Bug_Condition: isBugCondition(input) where input.tableExistsInDatabase('school_subscriptions') AND NOT input.migrationRecordedInMigrationsTable('2026_05_01_204102_create_school_subscriptions_table')_
    - _Expected_Behavior: expectedBehavior(result) where result.migrationRecorded = true AND result.tableExists = true AND result.dataPreserved = true_
    - _Requirements: 2.1, 2.2_

  - [ ] 6.3 Implement schema validation (optional)
    - Verify critical columns exist: `id`, `school_id`, `subscription_plan_id`, `status`, `start_date`, `end_date`
    - Use `Schema::hasColumn('school_subscriptions', 'column_name')`
    - Log warning if critical columns missing (don't fail migration)
    - _Requirements: 2.6_

  - [ ] 6.4 Implement down() method
    - Add comment: "Do NOT remove migration record in rollback - preserve state"
    - Leave method empty or add logging only
    - Rationale: Removing migration record would recreate the bug
    - _Preservation: Migration state should remain synchronized even after rollback_
    - _Requirements: 3.5_

  - [ ] 6.5 Test migration on localhost
    - Create test database with `school_subscriptions` table and sample data
    - Remove migration record from `migrations` table
    - Run migration: `php artisan migrate --force`
    - Verify migration record inserted correctly
    - Verify table and data intact
    - Verify subsequent migrations run successfully
    - _Requirements: 2.1, 2.2, 2.3_

## Phase 6: Fix Validation Tests

- [ ] 7. Verify bug condition exploration test now passes

  - [ ] 7.1 Re-run bug condition exploration test from Phase 1
    - **Property 1: Expected Behavior** - Migration State Synchronization Works
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Run bug condition exploration test from step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - Test should now pass with fixed code:
      - `school_subscriptions` table exists
      - Migration record not present initially
      - Run `php artisan migrate --force`
      - Assert command succeeds without "table already exists" error
      - Assert migration record now exists in `migrations` table
      - Assert table and data intact
    - _Requirements: 2.1, 2.2, 2.3_

  - [ ] 7.2 Verify preservation tests still pass
    - **Property 2: Preservation** - Fresh Database and Normal Migration Behavior
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Confirm all preservation tests still pass after fix:
      - Fresh database installation works correctly
      - Normal migration execution unchanged
      - Rollback operations work correctly
      - Pretend mode unchanged
      - Data preservation verified
      - Tenant isolation maintained
      - Foreign key constraints enforced
      - Future migrations tracked correctly
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8_

## Phase 7: Comprehensive Test Suite

- [ ] 8. Create unit tests for audit command

  - [ ] 8.1 Create test file `tests/Unit/Commands/AuditMigrationStateTest.php`
    - Test detection of out-of-sync migrations (table exists, migration not recorded)
    - Test detection of orphaned migration records (migration recorded, table doesn't exist)
    - Test schema drift detection (missing columns, indexes, foreign keys)
    - Test report generation with correct formatting
    - Test edge cases (empty database, no migrations, all synchronized)
    - _Requirements: 2.4, 2.5_

- [ ] 9. Create unit tests for sync command

  - [ ] 9.1 Create test file `tests/Unit/Commands/SyncMigrationStateTest.php`
    - Test batch number calculation (next batch after existing migrations)
    - Test migration record insertion with correct structure
    - Test idempotent behavior (running sync twice doesn't duplicate records)
    - Test dry-run mode (no database changes)
    - Test transaction rollback on error
    - _Requirements: 2.1, 2.2_

- [ ] 10. Create unit tests for corrective generator

  - [ ] 10.1 Create test file `tests/Unit/Commands/GenerateCorrectiveMigrationTest.php`
    - Test migration file generation with correct naming
    - Test `up()` method contains correct schema additions
    - Test `down()` method contains correct rollback logic
    - Test handling of missing columns, indexes, foreign keys
    - Test safe operation validation (no destructive operations)
    - _Requirements: 2.5_

- [ ] 11. Create property-based tests

  - [ ] 11.1 Create test file `tests/Property/MigrationStateSyncPropertyTest.php`
    - **Property 1**: Sync preserves data (generate random table data, sync, assert data identical)
    - **Property 2**: Sync idempotence (sync N times, assert final state identical)
    - **Property 3**: Fresh database equivalence (run migrations with/without fix, assert schema identical)
    - **Property 4**: Batch number monotonicity (sync new migration, assert batch number > max existing)
    - **Property 5**: Schema drift detection completeness (generate random schema modifications, assert all detected)
    - _Requirements: 2.1, 2.2, 2.5, 3.1, 3.2_

- [ ] 12. Create integration tests

  - [ ] 12.1 Create test file `tests/Integration/MigrationStateSyncIntegrationTest.php`
    - **Test 1**: End-to-end sync flow (audit → sync → migrate)
    - **Test 2**: Production deployment simulation (backup → audit → sync → migrate → verify)
    - **Test 3**: Multi-tenant data isolation (multiple schools, verify tenant isolation after sync)
    - **Test 4**: Shared hosting compatibility (simulate Namecheap constraints, verify completion)
    - **Test 5**: Schema drift correction flow (audit → generate corrective → run corrective → verify)
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 3.2, 3.3, 3.4, 3.7_

## Phase 8: Deployment Documentation

- [ ] 13. Create deployment documentation

  - [ ] 13.1 Create documentation file
    - Create file `docs/deployment/migration-state-sync-fix.md`
    - _Requirements: 2.3_

  - [ ] 13.2 Document pre-deployment checklist
    - Backup database (mysqldump or hosting control panel)
    - Verify current migration state: `php artisan migrate:status`
    - Test on staging environment first
    - Verify disk space available for backups
    - Document current table counts and row counts for verification
    - _Requirements: 2.3, 3.7_

  - [ ] 13.3 Document deployment steps
    - Step 1: Pull latest code from repository
    - Step 2: Run audit command: `php artisan migration:audit`
    - Step 3: Review audit report and identify issues
    - Step 4: Run sync command in dry-run mode: `php artisan migration:sync --dry-run`
    - Step 5: Review dry-run output
    - Step 6: Run sync command: `php artisan migration:sync --force`
    - Step 7: Run migrations: `php artisan migrate --force`
    - Step 8: Verify migration status: `php artisan migrate:status`
    - Step 9: Test application functionality (login, view subscriptions, create data)
    - _Requirements: 2.1, 2.2, 2.3_

  - [ ] 13.4 Document rollback plan
    - If sync fails: Restore database from backup
    - If migrations fail: Run `php artisan migrate:rollback` to undo changes
    - If application breaks: Restore database and revert code deployment
    - Document rollback commands and verification steps
    - _Requirements: 2.3, 3.5_

  - [ ] 13.5 Document shared hosting considerations
    - Namecheap timeout limits (30-60 seconds for web requests)
    - Run commands via SSH if available, or via web-based terminal
    - If timeout occurs, run commands in smaller batches
    - Verify file permissions for migration files (0644)
    - Document hosting-specific constraints and workarounds
    - _Requirements: 3.7_

  - [ ] 13.6 Document verification steps
    - Verify all migrations show "Ran" status: `php artisan migrate:status`
    - Verify table counts match expected: `SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()`
    - Verify row counts in critical tables: `SELECT COUNT(*) FROM school_subscriptions`
    - Test application functionality: login, view subscriptions, create school, create subscription
    - Verify tenant isolation: query school 1's subscriptions, verify only school 1 data returned
    - Verify foreign key constraints: attempt invalid insert, verify rejection
    - _Requirements: 2.2, 2.3, 3.3, 3.4_

## Phase 9: Final Validation

- [ ] 14. Checkpoint - Ensure all tests pass
  - Run full test suite: `php artisan test`
  - Verify all unit tests pass
  - Verify all property-based tests pass
  - Verify all integration tests pass
  - Verify bug condition exploration test passes (confirms fix works)
  - Verify preservation tests pass (confirms no regressions)
  - Review test coverage report
  - Address any failing tests before deployment
  - _Requirements: All requirements validated_

- [ ] 15. Final deployment readiness check
  - Confirm all code changes committed to version control
  - Confirm deployment documentation complete and reviewed
  - Confirm backup strategy in place for production
  - Confirm rollback plan tested on staging
  - Confirm stakeholders notified of deployment window
  - Confirm monitoring in place to detect issues post-deployment
  - Ask user if any questions or concerns before proceeding to production deployment
  - _Requirements: 2.3, 3.7_
