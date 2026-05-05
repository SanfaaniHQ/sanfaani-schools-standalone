<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_result_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('teacher_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->string('result_type', 50)->default('term_result');
            $table->string('status', 50)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('returned_at')->nullable();
            $table->text('return_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status'], 'trs_school_status_idx');
            $table->index(['teacher_user_id', 'status'], 'trs_teacher_status_idx');
            $table->index(['school_class_id', 'subject_id'], 'trs_class_subject_idx');
            $table->index(['academic_session_id', 'term_id'], 'trs_session_term_idx');
        });

        Schema::table('student_results', function (Blueprint $table) {
            if (! Schema::hasColumn('student_results', 'teacher_result_submission_id')) {
                $table->foreignId('teacher_result_submission_id')
                    ->nullable()
                    ->after('recorded_by')
                    ->constrained('teacher_result_submissions')
                    ->nullOnDelete();

                $table->index('teacher_result_submission_id', 'sr_teacher_submission_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_results', function (Blueprint $table) {
            if (Schema::hasColumn('student_results', 'teacher_result_submission_id')) {
                $table->dropIndex('sr_teacher_submission_idx');
                $table->dropConstrainedForeignId('teacher_result_submission_id');
            }
        });

        Schema::dropIfExists('teacher_result_submissions');
    }
};
