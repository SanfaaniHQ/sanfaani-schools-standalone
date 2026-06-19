<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('subject');
            $table->string('conversation_type', 50)->default('general');
            $table->string('status', 50)->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status'], 'portal_conversations_school_status_idx');
            $table->index(['school_id', 'conversation_type'], 'portal_conversations_school_type_idx');
            $table->index(['created_by', 'status'], 'portal_conversations_creator_status_idx');
        });

        Schema::create('portal_conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_conversation_id')->constrained('portal_conversations')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('participant_role')->nullable();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamp('muted_at')->nullable();
            $table->timestamps();

            $table->unique(['portal_conversation_id', 'user_id'], 'portal_conversation_user_unique');
            $table->index(['school_id', 'user_id'], 'portal_participants_school_user_idx');
        });

        Schema::create('portal_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_conversation_id')->constrained('portal_conversations')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->string('status', 50)->default('sent');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'portal_conversation_id'], 'portal_messages_school_convo_idx');
            $table->index(['sender_user_id', 'created_at'], 'portal_messages_sender_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_messages');
        Schema::dropIfExists('portal_conversation_participants');
        Schema::dropIfExists('portal_conversations');
    }
};
