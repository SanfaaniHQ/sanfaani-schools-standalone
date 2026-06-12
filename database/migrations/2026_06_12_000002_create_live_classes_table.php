<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->foreignId('lms_classroom_id')->nullable()->constrained('lms_classrooms')->nullOnDelete();
            $table->foreignId('lms_material_id')->nullable()->constrained('lms_materials')->nullOnDelete();
            $table->foreignId('teacher_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('provider', 50)->default('manual');
            $table->string('meeting_url', 2048);
            $table->string('meeting_password')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('timezone', 80)->nullable();
            $table->string('status', 50)->default('scheduled');
            $table->string('recording_url', 2048)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'starts_at'], 'live_classes_school_starts_idx');
            $table->index(['school_id', 'status'], 'live_classes_school_status_idx');
            $table->index(['school_id', 'school_class_id', 'starts_at'], 'live_classes_school_class_starts_idx');
            $table->index(['school_id', 'teacher_user_id', 'starts_at'], 'live_classes_school_teacher_starts_idx');
            $table->index(['school_id', 'lms_classroom_id'], 'live_classes_school_lms_classroom_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_classes');
    }
};
