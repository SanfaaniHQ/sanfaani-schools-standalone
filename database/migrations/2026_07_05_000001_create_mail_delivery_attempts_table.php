<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mail_delivery_attempts')) {
            return;
        }

        Schema::create('mail_delivery_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('initiating_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('transport', 50);
            $table->string('host')->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('encryption', 20)->nullable();
            $table->string('sender')->nullable();
            $table->string('recipient')->nullable();
            $table->string('status', 50);
            $table->string('safe_error_category', 80)->nullable();
            $table->text('sanitized_error_message')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->string('configuration', 20)->default('saved');
            $table->boolean('fallback_used')->default(false);
            $table->boolean('external_delivery_attempted')->default(false);
            $table->timestamps();

            $table->index(['school_id', 'created_at'], 'mail_attempt_school_created_idx');
            $table->index(['status', 'created_at'], 'mail_attempt_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_delivery_attempts');
    }
};
