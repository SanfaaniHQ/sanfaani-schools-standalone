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
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subscription_plan_id')
                ->constrained('subscription_plans')
                ->cascadeOnDelete();

            $table->string('feature_key', 100);
            $table->string('feature_name')->nullable();

            $table->boolean('is_enabled')->default(true);

            $table->unsignedInteger('limit_value')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique(
                ['subscription_plan_id', 'feature_key'],
                'plan_features_plan_key_unique'
            );

            $table->index(
                ['feature_key', 'is_enabled'],
                'plan_features_key_enabled_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};