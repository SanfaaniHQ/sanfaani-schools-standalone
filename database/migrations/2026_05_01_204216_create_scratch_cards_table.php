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
        Schema::create('scratch_cards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('scratch_card_batch_id')
                ->constrained('scratch_card_batches')
                ->cascadeOnDelete();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignId('school_class_id')
                ->nullable()
                ->constrained('school_classes')
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

            $table->string('serial_number', 100)->unique();

            $table->text('pin_code');
            $table->string('pin_hash')->nullable()->index();

            $table->unsignedInteger('max_uses')->default(1);
            $table->unsignedInteger('used_count')->default(0);

            $table->string('status', 50)->default('unused');

            $table->foreignId('used_by_student_id')
                ->nullable()
                ->constrained('students')
                ->nullOnDelete();

            $table->timestamp('first_used_at')->nullable();
            $table->timestamp('last_used_at')->nullable();

            $table->timestamp('revoked_at')->nullable();

            $table->foreignId('revoked_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('revoke_reason')->nullable();

            $table->timestamp('expires_at')->nullable();

            $table->foreignId('generated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(
                [
                    'school_id',
                    'academic_session_id',
                    'term_id',
                    'result_type',
                    'status',
                ],
                'scratch_cards_main_index'
            );

            $table->index(
                ['serial_number', 'status'],
                'scratch_cards_serial_status_index'
            );

            $table->index(
                ['school_id', 'used_by_student_id'],
                'scratch_cards_school_student_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scratch_cards');
    }
};
