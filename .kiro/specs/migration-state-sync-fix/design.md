# Migration State Synchronization Bugfix Design

## Overview

This design addresses a critical migration state synchronization bug in the Sanfaani Schools multi-tenant SaaS platform where database tables exist but are not recorded in Laravel's `migrations` tracking table. The primary manifestation is the `school_subscriptions` table causing `php artisan migrate --force` to fail with "Base table or view already exists" errors, blocking deployments and preventing future migrations.

The fix uses a non-destructive approach that:
1. **Detects** existing tables without migration records
2. **Synchronizes** migration state by marking migrations as completed
3. **Audits** schema drift (missing columns, indexes, foreign keys)
4. **Generates** corrective migrations for schema mismatches
5. **Preserves** all data and tenant isolation
6. **Works** on both localhost and production (Namecheap shared hosting)

The solution introduces a migration audit system that can detect all inconsistencies, not just `school_subscriptions`, ensuring long-term migration health.

## Glossary

- **Bug_Condition (C)**: Migration state is out of sync - a database table exists but its corresponding migration is not recorded in the `migrations` table
- **Property (P)**: Migrations should execute successfully without "table already exists" errors, and migration state should accurately reflect database reality
- **Preservation**: All existing data, tenant isolation (school_id columns), foreign key relationships, and normal migration behavior for fresh databases must remain unchanged
- **Migration State**: The records in the `migrations` table that track which migrations have been executed
- **Schema Drift**: Discrepancies between the actual database schema (columns, indexes, foreign keys) and what the migration files define
- **Tenant Isolation**: Multi-tenant architecture where each school's data is separated via `school_id` foreign keys
- **Corrective Migration**: A generated migration that adds missing schema elements (columns, indexes, foreign keys) without dropping tables or losing data

## Bug Details

### Bug Condition

The bug manifests when Laravel's migration system attempts to create a table that already exists in the database because the migration tracking is out of sync. The `migrate` command fails because it tries to execute a migration that has already been partially or fully applied to the database schema, but the `migrations` table has no record of it.

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input of type MigrationExecutionContext
  OUTPUT: boolean
  
  RETURN input.tableExistsInDatabase(input.migrationTableName)
         AND NOT input.migrationRecordedInMigrationsTable(input.migrationName)
         AND input.migrationAttemptedToRun()
END FUNCTION
```

**Where:**
- `input.tableExistsInDatabase(tableName)` checks if the table physically exists in MySQL
- `input.migrationRecordedInMigrationsTable(migrationName)` checks if the migration is in the `migrations` table
- `input.migrationAttemptedToRun()` indicates Laravel tried to execute the migration's `up()` method

### Examples

- **Primary Case**: Migration `2026_05_01_204102_create_school_subscriptions_table` - table `school_subscriptions` exists with data, but migration not recorded. Running `php artisan migrate --force` fails with "SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'school_subscriptions' already exists"

- **Secondary Case**: Migration `2026_05_01_204034_create_subscription_plans_table` - table `subscription_plans` exists but may have incomplete schema (missing columns added in later migrations). Migration not recorded, causing similar failures.

- **Schema Drift Case**: Table `school_subscriptions` exists and migration is recorded, but missing indexes `school_subscriptions_school_status_index`, `school_subscriptions_plan_status_index`, or `school_subscriptions_period_index` due to manual schema changes or incomplete migration execution.

- **Edge Case**: Fresh database installation - no tables exist, all migrations should run normally from scratch without triggering bug condition.

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Fresh database installations must continue to create all tables from scratch as defined in migration files
- Existing data in all tables (especially `school_subscriptions`, `schools`, `subscription_plans`) must remain intact without modification or deletion
- Tenant isolation via `school_id` foreign keys must continue to enforce proper scoping
- Foreign key constraints (e.g., `school_subscriptions.school_id` → `schools.id`) must continue to enforce referential integrity
- `php artisan migrate:rollback` must continue to work correctly for migrations that are properly tracked
- `php artisan migrate --pretend` must continue to show dry-run output without making changes
- Production deployment on Namecheap shared hosting must continue to work within hosting constraints
- Future migrations must continue to be tracked correctly in the `migrations` table

**Scope:**
All migration operations that do NOT involve out-of-sync migration state should be completely unaffected by this fix. This includes:
- Normal migration execution on fresh databases
- Rollback operations on properly tracked migrations
- Pretend/dry-run operations
- Migration status checks for synchronized migrations
- Future migrations created after the fix is applied

## Hypothesized Root Cause

Based on the bug description and Laravel migration behavior, the most likely causes are:

1. **Manual Database Changes**: Someone created the `school_subscriptions` table manually via SQL or phpMyAdmin without running the migration, possibly during development or emergency production fixes. This bypassed Laravel's migration tracking.

2. **Partial Migration Execution**: The migration started executing but was interrupted (server crash, timeout, manual cancellation) after creating the table but before recording the migration in the `migrations` table. Laravel's migration system is not fully transactional for DDL operations.

3. **Migration Table Corruption**: The `migrations` table was manually modified, truncated, or restored from an old backup that didn't include recent migration records, while the actual database schema remained intact.

4. **Environment Inconsistency**: Different environments (localhost, staging, production) had migrations run in different orders or with different migration files, causing state divergence. Production may have had migrations applied that weren't properly tracked.

5. **Deployment Process Issues**: The deployment process on Namecheap shared hosting may have failed mid-migration due to timeout, permission issues, or resource constraints, leaving tables created but migration records missing.

6. **Schema Drift from Manual Fixes**: Indexes or columns were manually added/removed to fix performance or data issues without creating corresponding migrations, causing the actual schema to differ from migration definitions.

## Correctness Properties

Property 1: Bug Condition - Migration State Synchronization

_For any_ migration where the target table exists in the database but the migration is not recorded in the `migrations` table, the fixed migration system SHALL detect the existing table, skip the table creation, mark the migration as completed in the `migrations` table with the correct batch number and timestamp, and continue executing remaining migrations without errors.

**Validates: Requirements 2.1, 2.2, 2.3**

Property 2: Preservation - Fresh Database Behavior

_For any_ migration execution on a fresh database where no tables exist, the fixed migration system SHALL produce exactly the same behavior as the original system, creating all tables from scratch and tracking all migrations correctly in the `migrations` table.

**Validates: Requirements 3.1, 3.8**

Property 3: Preservation - Data Integrity

_For any_ existing data in tables affected by migration state synchronization, the fixed system SHALL preserve all data without modification, deletion, or corruption, maintaining tenant isolation via `school_id` columns and enforcing all foreign key constraints.

**Validates: Requirements 3.2, 3.3, 3.4**

Property 4: Preservation - Rollback and Pretend Operations

_For any_ rollback or pretend operation on properly tracked migrations, the fixed system SHALL produce exactly the same behavior as the original system, rolling back migrations in correct order or showing dry-run output without making changes.

**Validates: Requirements 3.5, 3.6**

Property 5: Schema Drift Detection

_For any_ table where the actual database schema differs from the migration definition (missing columns, indexes, or foreign keys), the migration audit system SHALL detect the discrepancies and report them with sufficient detail to generate corrective migrations.

**Validates: Requirements 2.4, 2.5**

## Fix Implementation

### Changes Required

The fix involves creating a comprehensive migration audit and synchronization system that operates in multiple phases:

**Phase 1: Migration State Audit Command**

**File**: `app/Console/Commands/AuditMigrationState.php` (new file)

**Purpose**: Detect all migration inconsistencies and schema drift

**Specific Changes**:
1. **Table Existence Check**: Query `information_schema.TABLES` to get all tables in the database
2. **Migration Record Check**: Query `migrations` table to get all recorded migrations
3. **Cross-Reference Analysis**: Compare migration file names with table names to identify:
   - Tables that exist but migrations not recorded (primary bug condition)
   - Migrations recorded but tables don't exist (orphaned records)
   - Migrations pending but tables already exist (same as primary bug)
4. **Schema Drift Detection**: For each table with a migration:
   - Compare actual columns with migration definition columns
   - Compare actual indexes with migration definition indexes
   - Compare actual foreign keys with migration definition foreign keys
   - Report missing or extra schema elements
5. **Report Generation**: Output detailed report with:
   - List of out-of-sync migrations
   - List of schema drift issues
   - Recommended corrective actions
   - SQL preview for manual verification

**Phase 2: Migration State Synchronization Command**

**File**: `app/Console/Commands/SyncMigrationState.php` (new file)

**Purpose**: Safely synchronize migration state without dropping tables

**Specific Changes**:
1. **Safety Checks**: Verify database connection, check for active transactions, confirm `migrations` table exists
2. **Batch Number Calculation**: Determine the next batch number by querying `MAX(batch)` from `migrations` table
3. **Timestamp Generation**: Use current timestamp for synchronized migrations
4. **Selective Synchronization**: For each out-of-sync migration:
   - Verify table exists in database
   - Verify migration file exists in `database/migrations/`
   - Insert record into `migrations` table: `(migration, batch, timestamp)`
   - Skip if migration already recorded (idempotent operation)
5. **Transaction Wrapping**: Wrap all `migrations` table inserts in a database transaction for atomicity
6. **Dry-Run Mode**: Support `--dry-run` flag to preview changes without applying them
7. **Confirmation Prompt**: Require explicit confirmation before applying changes (unless `--force` flag used)
8. **Logging**: Log all synchronization actions to Laravel log for audit trail

**Phase 3: Schema Drift Corrective Migration Generator**

**File**: `app/Console/Commands/GenerateCorrectiveMigration.php` (new file)

**Purpose**: Generate migrations to fix schema drift without data loss

**Specific Changes**:
1. **Schema Comparison**: For a specified table, compare actual schema with migration definition
2. **Missing Element Detection**: Identify missing columns, indexes, foreign keys
3. **Migration File Generation**: Create a new migration file with:
   - `up()` method that adds missing schema elements using `Schema::table()`
   - `down()` method that removes added elements for rollback
   - Proper naming convention: `YYYY_MM_DD_HHMMSS_repair_{table_name}_schema_drift.php`
4. **Safe Operations Only**: Only generate additive operations (add column, add index, add foreign key), never destructive operations (drop column, drop table)
5. **Column Defaults**: For new columns, use sensible defaults or nullable to avoid data issues
6. **Index Naming**: Use explicit index names matching migration definitions
7. **Foreign Key Constraints**: Add foreign keys with proper `onDelete` and `onUpdate` actions

**Phase 4: Migration System Enhancement**

**File**: `database/migrations/2026_05_14_000002_sync_school_subscriptions_migration_state.php` (new migration)

**Purpose**: One-time migration to fix the immediate `school_subscriptions` issue

**Specific Changes**:
1. **Check Table Existence**: Use `Schema::hasTable('school_subscriptions')` to verify table exists
2. **Check Migration Record**: Query `migrations` table for `2026_05_01_204102_create_school_subscriptions_table`
3. **Conditional Synchronization**: If table exists but migration not recorded:
   - Insert migration record with current batch number
   - Log the synchronization action
4. **Idempotent Design**: If migration already recorded, skip synchronization (no error)
5. **Schema Validation**: Optionally verify critical columns exist (`id`, `school_id`, `subscription_plan_id`, `status`)
6. **Rollback Safety**: In `down()` method, do NOT remove the migration record (preserve state)

**Phase 5: Deployment Documentation**

**File**: `docs/deployment/migration-state-sync-fix.md` (new file)

**Purpose**: Document the fix process for production deployment

**Specific Changes**:
1. **Pre-Deployment Checklist**: Backup database, verify current migration state, test on staging
2. **Deployment Steps**: Step-by-step instructions for running audit, sync, and corrective migrations
3. **Rollback Plan**: Instructions for reverting changes if issues occur
4. **Shared Hosting Considerations**: Specific notes for Namecheap deployment (timeout limits, permission requirements)
5. **Verification Steps**: How to confirm fix was successful using `migrate:status` and manual queries

### Implementation Strategy

**Step 1**: Create audit command to identify all migration inconsistencies (not just `school_subscriptions`)

**Step 2**: Create sync command with dry-run mode for safe testing

**Step 3**: Test on localhost with known out-of-sync state

**Step 4**: Create one-time migration for immediate `school_subscriptions` fix

**Step 5**: Test complete flow on localhost: audit → sync → migrate

**Step 6**: Create corrective migration generator for schema drift

**Step 7**: Document deployment process with rollback plan

**Step 8**: Deploy to production with database backup and monitoring

## Testing Strategy

### Validation Approach

The testing strategy follows a three-phase approach:
1. **Exploratory Bug Condition Checking**: Surface counterexamples on unfixed code to confirm root cause
2. **Fix Checking**: Verify the fix resolves all identified migration state issues
3. **Preservation Checking**: Verify existing behavior is unchanged for fresh databases and normal operations

### Exploratory Bug Condition Checking

**Goal**: Surface counterexamples that demonstrate the bug BEFORE implementing the fix. Confirm or refute the root cause analysis. If we refute, we will need to re-hypothesize.

**Test Plan**: Create a test database with known out-of-sync state (table exists, migration not recorded), then attempt to run migrations on the UNFIXED code to observe failures and understand the root cause.

**Test Cases**:
1. **Primary Bug Reproduction**: Create `school_subscriptions` table manually, remove migration record, run `php artisan migrate --force` (will fail with "table already exists" error on unfixed code)

2. **Multiple Table Sync Issue**: Create multiple tables manually (`school_subscriptions`, `subscription_plans`), remove migration records, run migrations (will fail on first out-of-sync table on unfixed code)

3. **Schema Drift Detection**: Create `school_subscriptions` table with missing indexes, record migration, run audit command (should detect missing indexes on unfixed code)

4. **Fresh Database Baseline**: Run migrations on completely empty database (should succeed on both unfixed and fixed code, establishing baseline behavior)

**Expected Counterexamples**:
- `SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'school_subscriptions' already exists`
- Migration status shows "Pending" for migrations where tables already exist
- Deployments blocked due to migration failures
- Possible causes: manual table creation, partial migration execution, migration table corruption

### Fix Checking

**Goal**: Verify that for all inputs where the bug condition holds (table exists, migration not recorded), the fixed system produces the expected behavior (migration marked as completed, no errors).

**Pseudocode:**
```
FOR ALL migration WHERE isBugCondition(migration) DO
  result := runMigrationWithFix(migration)
  ASSERT result.success = true
  ASSERT result.migrationRecorded = true
  ASSERT result.tableExists = true
  ASSERT result.dataPreserved = true
END FOR
```

**Test Plan**: Create various out-of-sync scenarios, run the fix (audit + sync commands), then verify migration state is corrected.

**Test Cases**:

1. **Single Table Sync Test**:
   - Setup: Create `school_subscriptions` table manually with sample data, remove migration record
   - Execute: Run `php artisan migration:audit` then `php artisan migration:sync --force`
   - Assert: Migration record exists in `migrations` table with correct batch number
   - Assert: Table still exists with all data intact
   - Assert: `php artisan migrate --force` runs successfully without errors

2. **Multiple Table Sync Test**:
   - Setup: Create 3 tables manually (`school_subscriptions`, `subscription_plans`, `plan_features`), remove migration records
   - Execute: Run sync command
   - Assert: All 3 migration records inserted correctly
   - Assert: All tables and data preserved
   - Assert: Subsequent migrations run successfully

3. **Schema Drift Correction Test**:
   - Setup: Create `school_subscriptions` table missing indexes, record migration
   - Execute: Run `php artisan migration:audit` to detect drift, then `php artisan migration:generate-corrective school_subscriptions`
   - Assert: Corrective migration file created with missing indexes
   - Execute: Run corrective migration
   - Assert: Indexes now exist in database
   - Assert: All data preserved

4. **Idempotent Sync Test**:
   - Setup: Properly synchronized migration state
   - Execute: Run sync command again
   - Assert: No duplicate migration records created
   - Assert: No errors or warnings
   - Assert: Migration state unchanged

5. **Batch Number Continuity Test**:
   - Setup: Existing migrations with batch numbers 1-5, create out-of-sync table
   - Execute: Run sync command
   - Assert: Synchronized migration gets batch number 6 (next in sequence)
   - Assert: Future migrations get batch number 7, 8, etc.

### Preservation Checking

**Goal**: Verify that for all inputs where the bug condition does NOT hold (fresh database, normal migration flow), the fixed system produces the same result as the original system.

**Pseudocode:**
```
FOR ALL migrationContext WHERE NOT isBugCondition(migrationContext) DO
  ASSERT runMigrationOriginal(migrationContext) = runMigrationFixed(migrationContext)
END FOR
```

**Testing Approach**: Property-based testing is recommended for preservation checking because:
- It generates many test cases automatically across the input domain (fresh databases, various migration orders)
- It catches edge cases that manual unit tests might miss (unusual batch numbers, timestamp edge cases)
- It provides strong guarantees that behavior is unchanged for all non-buggy inputs

**Test Plan**: Observe behavior on UNFIXED code first for fresh database installations and normal migration operations, then write property-based tests capturing that behavior.

**Test Cases**:

1. **Fresh Database Installation Preservation**:
   - Observe: Run all migrations on empty database with unfixed code, record final state
   - Test: Run all migrations on empty database with fixed code
   - Assert: Final database schema identical (all tables, columns, indexes, foreign keys)
   - Assert: Migration records identical (same batch numbers, migration names)
   - Assert: Data seeding works identically

2. **Rollback Preservation**:
   - Observe: Run migrations to batch 5, rollback to batch 3 with unfixed code
   - Test: Run migrations to batch 5, rollback to batch 3 with fixed code
   - Assert: Same tables dropped in same order
   - Assert: Same migration records removed from `migrations` table
   - Assert: Database state identical after rollback

3. **Pretend Mode Preservation**:
   - Observe: Run `php artisan migrate --pretend` with unfixed code, capture output
   - Test: Run `php artisan migrate --pretend` with fixed code
   - Assert: Output identical (same SQL statements shown)
   - Assert: No database changes in either case
   - Assert: Migration state unchanged in both cases

4. **Tenant Isolation Preservation**:
   - Setup: Create multiple schools with `school_subscriptions` data
   - Execute: Run sync command
   - Assert: All `school_id` foreign keys intact
   - Assert: No cross-tenant data leakage (query school 1's subscriptions, verify only school 1 data returned)
   - Assert: Foreign key constraints still enforced (attempt to insert subscription with invalid `school_id`, verify rejection)

5. **Foreign Key Constraint Preservation**:
   - Setup: Existing `school_subscriptions` with foreign keys to `schools` and `subscription_plans`
   - Execute: Run sync command
   - Assert: Foreign key constraints still exist (`SHOW CREATE TABLE school_subscriptions` shows constraints)
   - Assert: Cascade delete still works (delete school, verify subscriptions deleted)
   - Assert: Referential integrity enforced (attempt to insert subscription with non-existent `school_id`, verify rejection)

6. **Future Migration Tracking Preservation**:
   - Setup: Synchronized migration state
   - Execute: Create new migration, run `php artisan migrate`
   - Assert: New migration tracked correctly in `migrations` table
   - Assert: Batch number increments correctly
   - Assert: Migration status shows "Ran" for new migration

### Unit Tests

**File**: `tests/Unit/Commands/AuditMigrationStateTest.php`
- Test detection of out-of-sync migrations (table exists, migration not recorded)
- Test detection of orphaned migration records (migration recorded, table doesn't exist)
- Test schema drift detection (missing columns, indexes, foreign keys)
- Test report generation with correct formatting
- Test edge cases (empty database, no migrations, all synchronized)

**File**: `tests/Unit/Commands/SyncMigrationStateTest.php`
- Test batch number calculation (next batch after existing migrations)
- Test migration record insertion with correct structure
- Test idempotent behavior (running sync twice doesn't duplicate records)
- Test dry-run mode (no database changes)
- Test transaction rollback on error

**File**: `tests/Unit/Commands/GenerateCorrectiveMigrationTest.php`
- Test migration file generation with correct naming
- Test `up()` method contains correct schema additions
- Test `down()` method contains correct rollback logic
- Test handling of missing columns, indexes, foreign keys
- Test safe operation validation (no destructive operations)

### Property-Based Tests

**File**: `tests/Property/MigrationStateSyncPropertyTest.php`

**Property 1: Sync Preserves Data**
- Generate: Random table data (various row counts, column values)
- Execute: Sync migration state
- Assert: All data identical before and after sync (row count, column values, foreign keys)

**Property 2: Sync Idempotence**
- Generate: Random out-of-sync migration states
- Execute: Sync command N times (N = random 1-10)
- Assert: Final state identical regardless of N (same migration records, no duplicates)

**Property 3: Fresh Database Equivalence**
- Generate: Random migration subsets (run first N migrations, N = random)
- Execute: Run on fresh database with and without fix
- Assert: Final schema identical (tables, columns, indexes, foreign keys)

**Property 4: Batch Number Monotonicity**
- Generate: Random existing batch numbers (1-100)
- Execute: Sync new migration
- Assert: New batch number > MAX(existing batch numbers)

**Property 5: Schema Drift Detection Completeness**
- Generate: Random schema modifications (remove random indexes, columns)
- Execute: Audit command
- Assert: All modifications detected in audit report

### Integration Tests

**File**: `tests/Integration/MigrationStateSyncIntegrationTest.php`

**Test 1: End-to-End Sync Flow**
- Setup: Fresh database, run migrations, manually create out-of-sync state
- Execute: Audit → Sync → Migrate (full flow)
- Assert: All migrations show "Ran" in status
- Assert: All tables exist with correct schema
- Assert: Sample data operations work correctly (create school, create subscription)

**Test 2: Production Deployment Simulation**
- Setup: Database state matching production (out-of-sync `school_subscriptions`)
- Execute: Deployment script (backup → audit → sync → migrate → verify)
- Assert: Deployment succeeds without errors
- Assert: Application functionality works (login, view subscriptions, create data)
- Assert: Rollback script works if needed

**Test 3: Multi-Tenant Data Isolation**
- Setup: Multiple schools with subscriptions, out-of-sync state
- Execute: Sync command
- Assert: Each school can only access their own subscriptions
- Assert: Foreign key constraints prevent cross-tenant references
- Assert: Cascade deletes work correctly per tenant

**Test 4: Shared Hosting Compatibility**
- Setup: Simulate Namecheap constraints (limited permissions, timeout limits)
- Execute: Sync command with timeout simulation
- Assert: Command completes within timeout limits
- Assert: No operations require elevated permissions
- Assert: Error handling works for timeout scenarios

**Test 5: Schema Drift Correction Flow**
- Setup: Table with missing indexes and columns
- Execute: Audit → Generate Corrective → Run Corrective → Verify
- Assert: All missing schema elements added
- Assert: No data loss during correction
- Assert: Application queries work correctly with new indexes
