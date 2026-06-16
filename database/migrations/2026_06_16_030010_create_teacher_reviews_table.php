<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('moderation_note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'teacher_user_id']);
            $table->index(['reviewer_user_id', 'status']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_reviews');
    }
};
