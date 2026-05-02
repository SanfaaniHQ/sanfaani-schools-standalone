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
        Schema::create('school_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignId('subscription_plan_id')
                ->constrained('subscription_plans')
                ->cascadeOnDelete();

            $table->string('status', 50)->default('active');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();

            $table->string('billing_cycle', 50)->default('term');
            $table->string('pricing_model', 50)->default('per_student');

            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 10)->default('NGN');

            $table->unsignedInteger('student_count')->nullable();

            $table->decimal('amount_due', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);

            $table->string('payment_status', 50)->default('pending');
            $table->string('payment_reference')->nullable();

            $table->foreignId('activated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('upgraded_from_subscription_id')
                ->nullable()
                ->constrained('school_subscriptions')
                ->nullOnDelete();

            $table->foreignId('downgraded_from_subscription_id')
                ->nullable()
                ->constrained('school_subscriptions')
                ->nullOnDelete();

            $table->foreignId('superseded_by_subscription_id')
                ->nullable()
                ->constrained('school_subscriptions')
                ->nullOnDelete();

            $table->string('plan_name_snapshot')->nullable();
            $table->decimal('price_snapshot', 12, 2)->nullable();
            $table->string('billing_cycle_snapshot', 50)->nullable();
            $table->string('pricing_model_snapshot', 50)->nullable();

            $table->json('features_snapshot')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(
                ['school_id', 'status'],
                'school_subscriptions_school_status_index'
            );

            $table->index(
                ['subscription_plan_id', 'status'],
                'school_subscriptions_plan_status_index'
            );

            $table->index(
                ['starts_at', 'ends_at'],
                'school_subscriptions_period_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_subscriptions');
    }
};