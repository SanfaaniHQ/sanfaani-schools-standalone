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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->nullable()
                ->constrained('schools')
                ->nullOnDelete();

            $table->foreignId('student_id')
                ->nullable()
                ->constrained('students')
                ->nullOnDelete();

            $table->string('payable_type')->nullable();
            $table->unsignedBigInteger('payable_id')->nullable();

            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 10)->default('NGN');

            $table->string('payment_method', 50)->nullable();
            $table->string('payment_gateway', 50)->nullable();

            $table->string('gateway_reference')->nullable();
            $table->string('payment_reference')->nullable();

            $table->string('status', 50)->default('pending');

            $table->timestamp('paid_at')->nullable();

            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('confirmed_at')->nullable();

            $table->string('payment_proof_path')->nullable();
            $table->text('manual_payment_note')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(
                ['payable_type', 'payable_id'],
                'payment_transactions_payable_index'
            );

            $table->index(
                ['school_id', 'status'],
                'payment_transactions_school_status_index'
            );

            $table->index(
                ['payment_gateway', 'gateway_reference'],
                'payment_transactions_gateway_reference_index'
            );

            $table->index(
                ['payment_method', 'status'],
                'payment_transactions_method_status_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
