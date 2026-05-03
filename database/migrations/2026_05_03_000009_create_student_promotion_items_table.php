<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_promotion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_promotion_batch_id')->constrained('student_promotion_batches')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('from_school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('to_school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('from_academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('to_academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->string('action');
            $table->string('status')->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'student_id', 'from_academic_session_id'], 'promo_item_student_from_idx');
            $table->index(['school_id', 'action', 'status'], 'promo_item_action_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_promotion_items');
    }
};
