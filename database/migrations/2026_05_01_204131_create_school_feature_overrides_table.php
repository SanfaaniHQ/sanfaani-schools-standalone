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
        Schema::create('school_feature_overrides', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();

            $table->string('feature_key', 100);

            $table->boolean('is_enabled')->default(true);

            $table->unsignedInteger('limit_value')->nullable();

            $table->text('reason')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique(
                ['school_id', 'feature_key'],
                'school_feature_overrides_school_key_unique'
            );

            $table->index(
                ['feature_key', 'is_enabled'],
                'school_feature_overrides_key_enabled_index'
            );

            $table->index(
                ['starts_at', 'ends_at'],
                'school_feature_overrides_period_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_feature_overrides');
    }
};
