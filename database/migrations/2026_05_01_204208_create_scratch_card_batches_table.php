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
        Schema::create('scratch_card_batches', function (Blueprint $table) {
            $table->id();

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

            $table->foreignId('school_result_access_policy_id')
                ->nullable()
                ->constrained('school_result_access_policies')
                ->nullOnDelete();

            $table->string('title')->nullable();

            $table->unsignedInteger('quantity');

            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 10)->default('NGN');

            $table->string('payment_status', 50)->default('pending');
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference')->nullable();

            $table->timestamp('payment_confirmed_at')->nullable();

            $table->foreignId('payment_confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('status', 50)->default('pending_payment');

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
                    'payment_status',
                    'status',
                ],
                'scratch_card_batches_main_index'
            );

            $table->index(
                ['school_id', 'school_class_id'],
                'scratch_card_batches_school_class_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scratch_card_batches');
    }
};