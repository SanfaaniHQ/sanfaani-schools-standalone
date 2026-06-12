<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_cbt_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('lms_classroom_id')->constrained('lms_classrooms')->cascadeOnDelete();
            $table->foreignId('lms_material_id')->nullable()->constrained('lms_materials')->cascadeOnDelete();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->string('target_type', 40);
            $table->unsignedBigInteger('target_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 40)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'target_type', 'target_id', 'cbt_exam_id'], 'lms_cbt_activities_target_exam_unique');
            $table->index(['school_id', 'lms_classroom_id'], 'lms_cbt_activities_school_classroom_idx');
            $table->index(['school_id', 'lms_material_id'], 'lms_cbt_activities_school_material_idx');
            $table->index(['school_id', 'cbt_exam_id'], 'lms_cbt_activities_school_exam_idx');
            $table->index(['school_id', 'status'], 'lms_cbt_activities_school_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_cbt_activities');
    }
};
