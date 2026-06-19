<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_card_snapshots', function (Blueprint $table) {
            $table->id();
            $table->uuid('snapshot_uuid')->unique();
            $table->foreignId('school_id')->constrained('schools')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->restrictOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->restrictOnDelete();
            $table->foreignId('term_id')->constrained('terms')->restrictOnDelete();
            $table->foreignId('result_publication_id')->nullable()->constrained('result_publications')->restrictOnDelete();
            $table->foreignId('result_verification_id')->nullable()->constrained('result_verifications')->restrictOnDelete();
            $table->unsignedInteger('snapshot_version')->default(1);
            $table->string('snapshot_type', 50)->default('term_report');
            $table->string('payload_schema_version', 50)->default('report_card_snapshot_v1');
            $table->string('result_type', 50)->default('term_result');
            $table->string('source_status', 50)->default('published');
            $table->string('status', 50)->default('active');
            $table->string('student_name');
            $table->string('admission_number', 100);
            $table->unsignedInteger('result_count')->default(0);
            $table->decimal('total_score', 8, 2)->default(0);
            $table->decimal('average_score', 5, 2)->default(0);
            $table->json('student_snapshot');
            $table->json('school_snapshot');
            $table->json('academic_snapshot');
            $table->json('result_snapshot');
            $table->json('grading_snapshot')->nullable();
            $table->json('settings_snapshot')->nullable();
            $table->json('comments_snapshot')->nullable();
            $table->json('access_snapshot')->nullable();
            $table->char('snapshot_hash', 64)->unique();
            $table->string('verification_code', 120)->nullable();
            $table->string('pdf_disk')->nullable();
            $table->string('pdf_path')->nullable();
            $table->char('pdf_hash', 64)->nullable();
            $table->timestamp('pdf_generated_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(
                ['school_id', 'student_id', 'academic_session_id', 'term_id', 'result_type', 'snapshot_version'],
                'report_snapshot_context_version_unique'
            );
            $table->index(
                ['school_id', 'school_class_id', 'academic_session_id', 'term_id', 'result_type', 'status'],
                'report_snapshot_context_idx'
            );
            $table->index(['result_publication_id', 'student_id'], 'report_snapshot_publication_student_idx');
            $table->index(['result_verification_id'], 'report_snapshot_verification_idx');
            $table->index(['verification_code'], 'report_snapshot_verification_code_idx');
            $table->index(['school_id', 'admission_number'], 'report_snapshot_admission_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_snapshots');
    }
};
