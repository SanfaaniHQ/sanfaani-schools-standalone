<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('context', 50);
            $table->string('step_key', 100);
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'user_id', 'context', 'step_key'], 'onboard_step_unq');
            $table->index(['school_id', 'context'], 'onboard_school_context_idx');
            $table->index(['user_id', 'context'], 'onboard_user_context_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_progress');
    }
};
