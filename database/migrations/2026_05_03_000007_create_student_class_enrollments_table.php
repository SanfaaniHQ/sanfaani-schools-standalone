<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->string('status', 50)->default('active');
            $table->timestamp('enrolled_at')->nullable();
            $table->unsignedBigInteger('promoted_from_enrollment_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('promoted_from_enrollment_id', 'stu_enroll_from_fk')
                ->references('id')
                ->on('student_class_enrollments')
                ->nullOnDelete();

            $table->index(['school_id', 'student_id', 'academic_session_id'], 'stu_enroll_student_session_idx');
            $table->index(['school_id', 'school_class_id', 'academic_session_id', 'status'], 'stu_enroll_class_status_idx');
            $table->unique(['student_id', 'academic_session_id'], 'stu_enroll_session_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_class_enrollments');
    }
};
