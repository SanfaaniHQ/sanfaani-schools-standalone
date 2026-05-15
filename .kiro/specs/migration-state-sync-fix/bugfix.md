# Bugfix Requirements Document

## Introduction

The Sanfaani Schools multi-tenant SaaS platform is experiencing a critical migration state synchronization issue where the `school_subscriptions` table exists in the database but is not recorded in Laravel's migration tracking table. This causes `php artisan migrate --force` to fail with error `SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'school_subscriptions' already exists`, blocking deployments and preventing future migrations from running.

This bug affects both localhost and production (Namecheap shared hosting) environments and poses a risk to data integrity, tenant isolation, and deployment reliability. The fix must safely synchronize migration state without dropping tables or losing data, while maintaining multi-tenant architecture integrity.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN `php artisan migrate --force` is executed AND the `school_subscriptions` table exists in the database BUT the migration `2026_05_01_204102_create_school_subscriptions_table` is not recorded in the `migrations` table THEN the system fails with error "SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'school_subscriptions' already exists"

1.2 WHEN `php artisan migrate:status` is executed AND migration state is out of sync THEN the system shows the `school_subscriptions` migration as "Pending" despite the table already existing in the database

1.3 WHEN migration state is inconsistent THEN deployments are blocked and future migrations cannot be applied, preventing system updates and feature releases

1.4 WHEN other migrations have similar state inconsistencies (table exists but migration not recorded) THEN those migrations also fail silently or cause cascading errors

1.5 WHEN schema drift exists (columns, indexes, or foreign keys missing or mismatched) THEN the system may exhibit undefined behavior or data integrity issues without clear error messages

### Expected Behavior (Correct)

2.1 WHEN `php artisan migrate --force` is executed AND the `school_subscriptions` table already exists in the database THEN the system SHALL detect the existing table, mark the migration as completed in the `migrations` table without attempting to recreate the table, and continue with remaining migrations successfully

2.2 WHEN `php artisan migrate:status` is executed AFTER migration state synchronization THEN the system SHALL show all migrations that correspond to existing database tables as "Ran" with correct batch numbers and timestamps

2.3 WHEN migration state is synchronized THEN deployments SHALL proceed successfully and future migrations SHALL be applied without errors

2.4 WHEN the migration audit process runs THEN the system SHALL detect ALL migration inconsistencies (not just `school_subscriptions`) and report them for correction

2.5 WHEN schema drift is detected (missing columns, indexes, or foreign keys) THEN the system SHALL generate corrective migrations to align the database schema with migration definitions without data loss

2.6 WHEN migration state is corrected THEN the system SHALL preserve all existing data, maintain tenant isolation (school_id columns), and ensure foreign key relationships remain intact

### Unchanged Behavior (Regression Prevention)

3.1 WHEN migrations are run on a fresh database (no existing tables) THEN the system SHALL CONTINUE TO create all tables from scratch as defined in migration files

3.2 WHEN existing data is present in the `school_subscriptions` table or any other affected tables THEN the system SHALL CONTINUE TO preserve all data without modification, deletion, or corruption

3.3 WHEN tenant isolation is enforced through `school_id` columns THEN the system SHALL CONTINUE TO maintain proper scoping and prevent cross-tenant data access

3.4 WHEN foreign key constraints exist between tables (e.g., `school_subscriptions.school_id` → `schools.id`) THEN the system SHALL CONTINUE TO enforce referential integrity

3.5 WHEN `php artisan migrate:rollback` is executed THEN the system SHALL CONTINUE TO roll back migrations in the correct order without affecting synchronized migration state

3.6 WHEN `php artisan migrate --pretend` is executed THEN the system SHALL CONTINUE TO show a dry-run of pending migrations without making database changes

3.7 WHEN migrations run in production on shared hosting (Namecheap) THEN the system SHALL CONTINUE TO work within shared hosting constraints (no shell access, limited permissions)

3.8 WHEN future migrations are created and run THEN the system SHALL CONTINUE TO track them correctly in the `migrations` table and apply them in the correct order
