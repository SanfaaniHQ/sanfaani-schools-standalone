<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('student_results', [
            'school_id',
            'student_id',
            'academic_session_id',
            'term_id',
            'result_type',
            'status',
            'published_at',
            'unpublished_at',
        ], 'student_results_public_lookup_idx');

        $this->addIndex('student_results', [
            'school_id',
            'school_class_id',
            'academic_session_id',
            'term_id',
            'result_type',
            'status',
        ], 'student_results_publish_scope_idx');

        $this->addIndex('scratch_card_usages', [
            'scratch_card_id',
            'academic_session_id',
            'term_id',
            'result_type',
        ], 'scratch_usage_card_context_idx');

        $this->addIndex('scratch_card_usages', [
            'school_id',
            'student_id',
            'academic_session_id',
            'term_id',
            'result_type',
        ], 'scratch_usage_student_context_idx');

        $this->addIndex('scratch_cards', [
            'scratch_card_batch_id',
            'status',
        ], 'scratch_cards_batch_status_idx');

        $this->addIndex('audit_logs', [
            'school_id',
            'created_at',
        ], 'audit_logs_school_date_idx');
    }

    public function down(): void
    {
        $this->dropIndex('audit_logs', 'audit_logs_school_date_idx');
        $this->dropIndex('scratch_cards', 'scratch_cards_batch_status_idx');
        $this->dropIndex('scratch_card_usages', 'scratch_usage_student_context_idx');
        $this->dropIndex('scratch_card_usages', 'scratch_usage_card_context_idx');
        $this->dropIndex('student_results', 'student_results_publish_scope_idx');
        $this->dropIndex('student_results', 'student_results_public_lookup_idx');
    }

    private function addIndex(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || Schema::hasIndex($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    private function dropIndex(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasIndex($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }
};
