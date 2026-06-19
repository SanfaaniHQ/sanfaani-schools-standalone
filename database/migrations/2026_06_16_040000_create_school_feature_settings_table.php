<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('school_feature_settings')) {
            return;
        }

        Schema::create('school_feature_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('role_name', 80);
            $table->string('feature_key', 100);
            $table->boolean('enabled')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'role_name', 'feature_key'], 'school_feature_role_unique');
            $table->index(['school_id', 'role_name'], 'school_features_school_role_idx');
            $table->index(['feature_key', 'enabled'], 'school_features_key_enabled_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_feature_settings');
    }
};
