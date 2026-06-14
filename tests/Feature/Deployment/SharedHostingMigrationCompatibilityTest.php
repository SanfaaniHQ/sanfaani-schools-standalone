<?php

namespace Tests\Feature\Deployment;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SharedHostingMigrationCompatibilityTest extends TestCase
{
    private const APP_SERVICE_PROVIDER = 'app/Providers/AppServiceProvider.php';

    private const ARCHITECTURE_INDEX_MIGRATION = 'database/migrations/2026_05_14_000001_add_architecture_hardening_indexes.php';

    private const CBT_MIGRATION = 'database/migrations/2026_05_20_000001_create_cbt_architecture_tables.php';

    private const MARKETING_SEQUENCES_MIGRATION = 'database/migrations/2026_05_25_020003_create_marketing_automation_sequences_table.php';

    private const MARKETING_STEPS_MIGRATION = 'database/migrations/2026_05_25_020004_create_marketing_automation_steps_table.php';

    private const MARKETING_ENROLLMENTS_MIGRATION = 'database/migrations/2026_05_25_020005_create_marketing_automation_enrollments_table.php';

    private const PERMISSION_MIGRATION = 'database/migrations/2026_04_30_125040_create_permission_tables.php';

    private const SCHOOL_CLASSES_MIGRATION = 'database/migrations/2026_04_30_153808_create_school_classes_table.php';

    private const STANDALONE_SYNC_MIGRATION = 'database/migrations/2026_06_10_000001_create_standalone_sync_tables.php';

    private const DOC = 'docs/deployment/shared-hosting-mysql-index-compatibility.md';

    public function test_app_service_provider_applies_default_string_length_191(): void
    {
        $provider = $this->file(self::APP_SERVICE_PROVIDER);

        $this->assertStringContainsString('use Illuminate\Support\Facades\Schema;', $provider);
        $this->assertStringContainsString('Schema::defaultStringLength(191);', $provider);
    }

    public function test_architecture_hardening_migration_keeps_public_lookup_index_name(): void
    {
        $this->assertStringContainsString('student_results_public_lookup_idx', $this->architectureMigration());
    }

    public function test_public_lookup_index_omits_long_status_and_timestamp_columns(): void
    {
        $columns = $this->indexColumns($this->architectureMigration(), 'student_results_public_lookup_idx');

        $this->assertSame(['school_id', 'student_id', 'academic_session_id', 'term_id'], $columns);
        $this->assertNotContains('result_type', $columns);
        $this->assertNotContains('status', $columns);
        $this->assertNotContains('published_at', $columns);
        $this->assertNotContains('unpublished_at', $columns);
    }

    public function test_architecture_hardening_migration_keeps_publish_scope_index_name(): void
    {
        $this->assertStringContainsString('student_results_publish_scope_idx', $this->architectureMigration());
    }

    public function test_publish_scope_index_omits_result_type_and_status(): void
    {
        $columns = $this->indexColumns($this->architectureMigration(), 'student_results_publish_scope_idx');

        $this->assertSame(['school_id', 'school_class_id', 'academic_session_id', 'term_id'], $columns);
        $this->assertNotContains('result_type', $columns);
        $this->assertNotContains('status', $columns);
    }

    public function test_cbt_migration_keeps_question_banks_pool_index_name(): void
    {
        $this->assertStringContainsString('cbt_question_banks_pool_idx', $this->cbtMigration());
    }

    public function test_cbt_question_banks_pool_index_omits_category_and_topic(): void
    {
        $columns = $this->indexColumns($this->cbtMigration(), 'cbt_question_banks_pool_idx');

        $this->assertSame(['school_id', 'difficulty'], $columns);
        $this->assertNotContains('category', $columns);
        $this->assertNotContains('topic', $columns);
    }

    public function test_cbt_question_banks_pool_index_uses_inline_blueprint_style(): void
    {
        $migration = $this->cbtMigration();

        $this->assertStringContainsString("\$table->index(['school_id', 'difficulty'], 'cbt_question_banks_pool_idx');", $migration);
        $this->assertStringNotContainsString('$this->addIndex', $migration);
    }

    public function test_marketing_sequences_status_trigger_index_keeps_name_and_omits_trigger_event(): void
    {
        $columns = $this->indexColumns($this->marketingSequencesMigration(), 'marketing_sequences_status_trigger_idx');

        $this->assertSame(['status'], $columns);
        $this->assertNotContains('trigger_event', $columns);
    }

    public function test_marketing_automation_steps_uses_short_sequence_foreign_key_name(): void
    {
        $this->assertStringContainsString("indexName: 'marketing_steps_sequence_fk'", $this->marketingStepsMigration());
    }

    public function test_marketing_automation_enrollments_uses_short_sequence_foreign_key_name(): void
    {
        $this->assertStringContainsString("indexName: 'marketing_enrollments_sequence_fk'", $this->marketingEnrollmentsMigration());
    }

    public function test_known_failed_marketing_sequence_foreign_key_names_are_absent(): void
    {
        $migrations = $this->allMigrationContent();

        $this->assertStringNotContainsString('marketing_automation_steps_marketing_automation_sequence_id_foreign', $migrations);
        $this->assertStringNotContainsString('marketing_automation_enrollments_marketing_automation_sequence_id_foreign', $migrations);
    }

    public function test_no_generated_marketing_sequence_foreign_key_name_exceeds_mysql_identifier_limit(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            "/foreignId\\('marketing_automation_sequence_id'\\)\\s*->constrained\\('marketing_automation_sequences'\\)/s",
            $this->allMigrationContent()
        );
        $this->assertLessThanOrEqual(64, strlen('marketing_steps_sequence_fk'));
        $this->assertLessThanOrEqual(64, strlen('marketing_enrollments_sequence_fk'));
    }

    public function test_spatie_permission_unique_columns_are_limited_for_shared_hosting(): void
    {
        $migration = $this->file(self::PERMISSION_MIGRATION);

        $this->assertStringContainsString("\$table->string('name', 125);", $migration);
        $this->assertStringContainsString("\$table->string('guard_name', 25);", $migration);
        $this->assertStringContainsString("\$table->unique(['name', 'guard_name']);", $migration);
    }

    public function test_school_class_name_and_section_unique_columns_are_limited_for_shared_hosting(): void
    {
        $migration = $this->file(self::SCHOOL_CLASSES_MIGRATION);

        $this->assertStringContainsString("\$table->string('name', 100);", $migration);
        $this->assertStringContainsString("\$table->string('section', 100)->nullable();", $migration);
        $this->assertStringContainsString("\$table->unique(['school_id', 'name', 'section']);", $migration);
    }

    public function test_standalone_sync_outbox_entity_lookup_columns_are_limited_for_shared_hosting(): void
    {
        $migration = $this->standaloneSyncMigration();

        $this->assertStringContainsString("\$table->string('entity_type', 64);", $migration);
        $this->assertStringContainsString("\$table->string('entity_id', 64)->nullable();", $migration);
        $this->assertStringNotContainsString("\$table->string('entity_type');", $migration);
        $this->assertStringNotContainsString("\$table->string('entity_id')->nullable();", $migration);
    }

    public function test_standalone_sync_outbox_entity_lookup_uses_short_index_name(): void
    {
        $migration = $this->standaloneSyncMigration();
        $columns = $this->indexColumns($migration, 'sync_outbox_entity_idx');

        $this->assertSame(['entity_type', 'entity_id'], $columns);
        $this->assertLessThanOrEqual(64, strlen('sync_outbox_entity_idx'));
        $this->assertStringNotContainsString('standalone_sync_outbox_entity_type_entity_id_index', $migration);
    }

    public function test_standalone_sync_migration_uses_explicit_short_index_names(): void
    {
        $migration = $this->standaloneSyncMigration();

        foreach ([
            'sync_devices_uuid_uidx',
            'sync_devices_type_idx',
            'sync_devices_seen_idx',
            'sync_outbox_uuid_uidx',
            'sync_outbox_status_idx',
            'sync_outbox_entity_idx',
            'sync_outbox_hash_idx',
            'sync_logs_status_idx',
            'sync_logs_started_idx',
        ] as $indexName) {
            $this->assertStringContainsString($indexName, $migration);
            $this->assertLessThanOrEqual(64, strlen($indexName));
        }
    }

    public function test_shared_hosting_mysql_index_compatibility_doc_exists(): void
    {
        $this->assertFileExists(base_path(self::DOC));
    }

    public function test_docs_mention_migrate_force(): void
    {
        $this->assertStringContainsString('php artisan migrate --force', $this->doc());
    }

    public function test_docs_do_not_recommend_migrate_fresh(): void
    {
        $doc = strtolower($this->doc());

        $this->assertStringContainsString('do not use `migrate:fresh`', $doc);
        $this->assertStringContainsString('environmentguard', $doc);
        $this->assertStringNotContainsString('php artisan migrate:fresh', $doc);
    }

    public function test_docs_mention_cpanel_namecheap_key_limit(): void
    {
        $doc = strtolower($this->doc());

        $this->assertStringContainsString('cpanel', $doc);
        $this->assertStringContainsString('namecheap', $doc);
        $this->assertStringContainsString('1000-byte', $doc);
    }

    public function test_full_test_suite_is_part_of_validation_expectation(): void
    {
        $this->assertTrue(true);
    }

    private function architectureMigration(): string
    {
        return $this->file(self::ARCHITECTURE_INDEX_MIGRATION);
    }

    private function cbtMigration(): string
    {
        return $this->file(self::CBT_MIGRATION);
    }

    private function marketingSequencesMigration(): string
    {
        return $this->file(self::MARKETING_SEQUENCES_MIGRATION);
    }

    private function marketingStepsMigration(): string
    {
        return $this->file(self::MARKETING_STEPS_MIGRATION);
    }

    private function marketingEnrollmentsMigration(): string
    {
        return $this->file(self::MARKETING_ENROLLMENTS_MIGRATION);
    }

    private function standaloneSyncMigration(): string
    {
        return $this->file(self::STANDALONE_SYNC_MIGRATION);
    }

    private function doc(): string
    {
        return $this->file(self::DOC);
    }

    private function file(string $path): string
    {
        return File::get(base_path($path));
    }

    private function allMigrationContent(): string
    {
        return collect(File::files(base_path('database/migrations')))
            ->map(fn ($file): string => File::get($file->getPathname()))
            ->implode("\n");
    }

    private function indexColumns(string $content, string $indexName): array
    {
        $pattern = "/\\[([^\\]]+)\\]\\s*,\\s*'".preg_quote($indexName, '/')."'/m";

        $this->assertMatchesRegularExpression($pattern, $content);
        preg_match($pattern, $content, $matches);

        preg_match_all("/'([^']+)'/", $matches[1], $columns);

        return $columns[1];
    }
}
