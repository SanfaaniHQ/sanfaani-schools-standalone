<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('template_key', 120);
            $table->string('title');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('channel', 30)->default('database');
            $table->string('audience_type', 60)->default('school_admin');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'template_key'], 'school_notification_templates_school_key_unique');
            $table->index(['school_id', 'channel', 'is_active'], 'school_notification_templates_channel_idx');
        });

        Schema::create('school_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('school_notification_templates')->nullOnDelete();
            $table->string('event_type', 120);
            $table->string('channel', 30)->default('database');
            $table->string('recipient_type', 60);
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message_summary')->nullable();
            $table->string('status', 30)->default('logged');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('related_model_type', 191)->nullable();
            $table->unsignedBigInteger('related_model_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'event_type', 'created_at'], 'school_notification_logs_event_idx');
            $table->index(['school_id', 'channel', 'status'], 'school_notification_logs_channel_idx');
            $table->index(['school_id', 'recipient_type', 'recipient_id'], 'school_notification_logs_recipient_idx');
            $table->index(['related_model_type', 'related_model_id'], 'school_notification_logs_related_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_notification_logs');
        Schema::dropIfExists('school_notification_templates');
    }
};
