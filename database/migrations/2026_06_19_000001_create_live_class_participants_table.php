<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('live_class_participants')) {
            return;
        }

        Schema::create('live_class_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('live_class_id')->constrained('live_classes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('audience_type', 50)->default('class');
            $table->string('role_context', 50)->nullable();
            $table->string('status', 50)->default('invited');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('reminder_due_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['live_class_id', 'user_id'], 'lcp_unique_user_class');
            $table->index(['school_id', 'live_class_id'], 'lcp_school_class_idx');
            $table->index(['user_id', 'status'], 'lcp_user_status_idx');
            $table->index(['reminder_due_at', 'reminder_sent_at'], 'lcp_reminder_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_class_participants');
    }
};
