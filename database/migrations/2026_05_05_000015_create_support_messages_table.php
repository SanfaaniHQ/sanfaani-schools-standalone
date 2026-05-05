<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_thread_id')->constrained('support_threads')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('sender_role', 50)->nullable();
            $table->text('message');
            $table->boolean('is_internal_note')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['support_thread_id', 'created_at'], 'smsg_thread_date_idx');
            $table->index(['school_id', 'created_at'], 'smsg_school_date_idx');
            $table->index(['sender_id', 'created_at'], 'smsg_sender_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
    }
};
