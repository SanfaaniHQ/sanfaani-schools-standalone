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
        Schema::create('school_role_feature_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('role_name', 50);
            $table->string('feature_key', 100);
            $table->boolean('is_enabled')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('role_name');
            $table->index('feature_key');
            $table->unique(['school_id', 'role_name', 'feature_key'], 'srfs_school_role_feature_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_role_feature_settings');
    }
};
