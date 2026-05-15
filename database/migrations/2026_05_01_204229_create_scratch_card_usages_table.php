<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scratch_card_usages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('scratch_card_id')
                ->constrained('scratch_cards')
                ->cascadeOnDelete();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->nullable()
                ->constrained('students')
                ->nullOnDelete();

            $table->foreignId('academic_session_id')
                ->nullable()
                ->constrained('academic_sessions')
                ->nullOnDelete();

            $table->foreignId('term_id')
                ->nullable()
                ->constrained('terms')
                ->nullOnDelete();

            $table->string('result_type', 50)->nullable();

            $table->timestamp('used_at')->nullable();

            $table->string('ip_address', 100)->nullable();
            $table->text('user_agent')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(
                ['scratch_card_id', 'student_id'],
                'scratch_card_usages_card_student_index'
            );

            $table->index(
                ['school_id', 'academic_session_id', 'term_id', 'result_type'],
                'scratch_card_usages_result_context_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scratch_card_usages');
    }
};
