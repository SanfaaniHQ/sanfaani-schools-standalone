<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->string('assignment_type', 50)->default('core');
            $table->boolean('is_elective')->default(false);
            $table->boolean('is_required')->default(true);
            $table->string('status', 50)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status'], 'csa_school_status_idx');
            $table->index(['school_class_id', 'subject_id'], 'csa_class_subject_idx');
            $table->index(['subject_id', 'status'], 'csa_subject_status_idx');
            $table->index(['academic_session_id', 'term_id'], 'csa_session_term_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_subject_assignments');
    }
};
