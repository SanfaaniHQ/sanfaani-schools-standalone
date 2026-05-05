<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_public_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(false);
            $table->boolean('result_checker_enabled')->default(true);
            $table->boolean('scratch_card_purchase_enabled')->default(false);
            $table->string('title')->nullable();
            $table->string('headline')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('address')->nullable();
            $table->json('upcoming_events')->nullable();
            $table->json('extra_content')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'is_active'], 'spp_school_active_idx');
            $table->index(['slug', 'is_active'], 'spp_slug_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_public_pages');
    }
};
