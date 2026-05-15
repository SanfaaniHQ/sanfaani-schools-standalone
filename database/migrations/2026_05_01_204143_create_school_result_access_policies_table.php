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
        Schema::create('school_result_access_policies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->foreignId('school_subscription_id')
                ->nullable()
                ->constrained('school_subscriptions')
                ->nullOnDelete();

            $table->string('name');
            $table->string('access_mode', 50)->default('scratch_card');

            $table->string('status', 50)->default('active');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(
                ['school_id', 'access_mode', 'status'],
                'result_access_policies_school_mode_status_index'
            );

            $table->index(
                ['starts_at', 'ends_at'],
                'result_access_policies_period_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_result_access_policies');
    }
};
