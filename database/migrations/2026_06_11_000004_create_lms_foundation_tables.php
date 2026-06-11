<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 50)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status'], 'lms_classrooms_school_status_idx');
            $table->index(['school_id', 'school_class_id'], 'lms_classrooms_school_class_idx');
            $table->index(['school_id', 'subject_id'], 'lms_classrooms_school_subject_idx');
            $table->unique(
                ['school_id', 'school_class_id', 'subject_id', 'academic_session_id', 'term_id'],
                'lms_classrooms_scope_unique'
            );
        });

        Schema::create('lms_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('lms_classroom_id')->constrained('lms_classrooms')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 50)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'lms_classroom_id'], 'lms_topics_school_classroom_idx');
            $table->index(['lms_classroom_id', 'sort_order'], 'lms_topics_classroom_sort_idx');
        });

        Schema::create('lms_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('lms_classroom_id')->constrained('lms_classrooms')->cascadeOnDelete();
            $table->foreignId('lms_topic_id')->nullable()->constrained('lms_topics')->nullOnDelete();
            $table->foreignId('teacher_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->longText('body')->nullable();
            $table->string('type', 50)->default('lesson');
            $table->string('status', 50)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('visible_from')->nullable();
            $table->timestamp('visible_until')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status'], 'lms_materials_school_status_idx');
            $table->index(['school_id', 'lms_classroom_id', 'status'], 'lms_materials_classroom_status_idx');
            $table->index(['school_id', 'teacher_user_id'], 'lms_materials_school_teacher_idx');
            $table->index(['lms_classroom_id', 'lms_topic_id'], 'lms_materials_topic_idx');
        });

        Schema::create('lms_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('lms_material_id')->constrained('lms_materials')->cascadeOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('checksum', 128)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50)->default('active');
            $table->timestamps();

            $table->index(['school_id', 'lms_material_id'], 'lms_resources_school_material_idx');
            $table->index(['school_id', 'status'], 'lms_resources_school_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_resources');
        Schema::dropIfExists('lms_materials');
        Schema::dropIfExists('lms_topics');
        Schema::dropIfExists('lms_classrooms');
    }
};
