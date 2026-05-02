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
        Schema::create('school_result_access_policy_rules', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('school_result_access_policy_id');

            $table->foreign('school_result_access_policy_id', 'sra_policy_rules_policy_fk')
                ->references('id')
                ->on('school_result_access_policies')
                ->cascadeOnDelete();

            $table->foreignId('academic_session_id')
                ->nullable()
                ->constrained('academic_sessions')
                ->nullOnDelete();

            $table->foreignId('term_id')
                ->nullable()
                ->constrained('terms')
                ->nullOnDelete();

            $table->string('result_type', 50)->default('term_result');
            $table->string('access_scope', 50)->default('term');

            $table->unsignedInteger('max_access_per_student')->nullable();
            $table->unsignedInteger('max_access_per_card')->nullable();

            $table->boolean('requires_scratch_card')->default(true);
            $table->boolean('allows_parent_payment')->default(false);
            $table->boolean('allows_school_paid_access')->default(false);
            $table->boolean('allows_pdf_download')->default(false);

            $table->string('status', 50)->default('active');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(
                [
                    'school_result_access_policy_id',
                    'academic_session_id',
                    'term_id',
                    'result_type',
                    'access_scope',
                    'status',
                ],
                'result_access_policy_rules_main_index'
            );

            $table->index(
                ['starts_at', 'ends_at'],
                'result_access_policy_rules_period_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_result_access_policy_rules');
    }
};