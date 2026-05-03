<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_promotion_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('from_academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('to_academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('from_school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('to_school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->string('promotion_type');
            $table->string('status')->default('completed');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'from_academic_session_id', 'from_school_class_id'], 'promo_batch_from_idx');
            $table->index(['school_id', 'to_academic_session_id', 'to_school_class_id'], 'promo_batch_to_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_promotion_batches');
    }
};
