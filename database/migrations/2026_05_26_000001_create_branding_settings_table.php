<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branding_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('scope')->default('platform');
            $table->string('brand_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->string('accent_color')->nullable();
            $table->text('email_footer_text')->nullable();
            $table->string('login_heading')->nullable();
            $table->string('login_subheading')->nullable();
            $table->string('dashboard_heading')->nullable();
            $table->text('report_footer_text')->nullable();
            $table->boolean('white_label_enabled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'scope', 'is_active'], 'branding_school_scope_active_idx');
            $table->index(['scope', 'is_active'], 'branding_scope_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branding_settings');
    }
};
