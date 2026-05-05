<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_website_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('website_mode', 50)->default('result_link_only');
            $table->boolean('website_enabled')->default(false);
            $table->boolean('result_checker_enabled')->default(true);
            $table->string('preferred_domain')->nullable();
            $table->string('subdomain')->nullable();
            $table->string('custom_domain')->nullable();
            $table->string('custom_domain_status', 50)->nullable();
            $table->boolean('homepage_enabled')->default(false);
            $table->boolean('events_enabled')->default(false);
            $table->boolean('announcements_enabled')->default(false);
            $table->boolean('admissions_enabled')->default(false);
            $table->boolean('contact_page_enabled')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('school_id', 'sws_school_unq');
            $table->index(['website_mode', 'website_enabled'], 'sws_mode_enabled_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_website_settings');
    }
};
