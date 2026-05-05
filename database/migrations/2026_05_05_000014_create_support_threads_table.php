<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject');
            $table->string('category', 50)->nullable();
            $table->string('priority', 50)->default('normal');
            $table->string('status', 50)->default('open');
            $table->string('visibility', 50)->default('internal');
            $table->timestamp('last_message_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status'], 'sth_school_status_idx');
            $table->index(['status', 'priority'], 'sth_status_priority_idx');
            $table->index('last_message_at', 'sth_last_msg_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_threads');
    }
};
