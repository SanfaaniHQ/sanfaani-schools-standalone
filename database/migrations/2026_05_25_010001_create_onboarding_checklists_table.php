<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique('onboarding_checklists_key_unique');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('role_name', 80)->nullable();
            $table->json('deployment_modes')->nullable();
            $table->json('license_modes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['role_name', 'is_active'], 'onboarding_checklists_role_active_idx');
            $table->index(['sort_order', 'id'], 'onboarding_checklists_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_checklists');
    }
};
