<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_results', function (Blueprint $table) {
            $table->timestamp('published_at')
                ->nullable()
                ->after('status');

            $table->foreignId('published_by')
                ->nullable()
                ->after('published_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('unpublished_at')
                ->nullable()
                ->after('published_by');

            $table->foreignId('unpublished_by')
                ->nullable()
                ->after('unpublished_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->text('unpublish_reason')
                ->nullable()
                ->after('unpublished_by');

            $table->index(
                ['school_id', 'school_class_id', 'academic_session_id', 'term_id', 'status'],
                'student_results_publish_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_results', function (Blueprint $table) {
            $table->dropIndex('student_results_publish_index');

            $table->dropConstrainedForeignId('unpublished_by');
            $table->dropColumn('unpublished_at');
            $table->dropColumn('unpublish_reason');

            $table->dropConstrainedForeignId('published_by');
            $table->dropColumn('published_at');
        });
    }
};