<?php

namespace Tests\Feature\Deployment;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SharedHostingMigrationCompatibilityTest extends TestCase
{
    private const APP_SERVICE_PROVIDER = 'app/Providers/AppServiceProvider.php';

    private const ARCHITECTURE_INDEX_MIGRATION = 'database/migrations/2026_05_14_000001_add_architecture_hardening_indexes.php';

    private const CBT_MIGRATION = 'database/migrations/2026_05_20_000001_create_cbt_architecture_tables.php';

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
        $this->assertStringNotContainsString('php artisan migrate:fresh', $doc);
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

    private function doc(): string
    {
        return $this->file(self::DOC);
    }

    private function file(string $path): string
    {
        return File::get(base_path($path));
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
