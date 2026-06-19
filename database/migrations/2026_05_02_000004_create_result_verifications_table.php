<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('result_verifications')) {
            return;
        }

        Schema::create('result_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->string('result_type', 50)->default('term_result');
            $table->string('verification_code', 120)->unique('result_verifications_code_unique');
            $table->string('status', 50)->default('active');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(
                ['school_id', 'student_id', 'academic_session_id', 'term_id', 'result_type'],
                'result_verifications_context_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_verifications');
    }
};
