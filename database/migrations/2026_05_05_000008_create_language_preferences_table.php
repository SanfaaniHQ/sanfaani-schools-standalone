<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('language_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->string('scope_type', 50);
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('language_code', 10);
            $table->boolean('is_default')->default(false);
            $table->string('status', 50)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'scope_type'], 'lang_school_scope_idx');
            $table->index(['scope_type', 'scope_id'], 'lang_scope_id_idx');
            $table->index(['language_code', 'status'], 'lang_code_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('language_preferences');
    }
};
