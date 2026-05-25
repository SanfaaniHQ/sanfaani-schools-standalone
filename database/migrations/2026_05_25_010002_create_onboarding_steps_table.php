<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onboarding_checklist_id')->constrained('onboarding_checklists')->cascadeOnDelete();
            $table->string('key');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('action_label')->nullable();
            $table->string('action_url')->nullable();
            $table->string('route_name')->nullable();
            $table->string('feature_key')->nullable();
            $table->json('deployment_modes')->nullable();
            $table->json('license_modes')->nullable();
            $table->boolean('required')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['onboarding_checklist_id', 'key'], 'onboarding_steps_checklist_key_unq');
            $table->index(['feature_key', 'sort_order']);
            $table->index(['sort_order', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_steps');
    }
};
