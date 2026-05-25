<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_event_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('demo_session_id')->nullable()->constrained('demo_sessions')->nullOnDelete();
            $table->string('event', 100);
            $table->text('description')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'event']);
            $table->index(['user_id', 'event']);
            $table->index(['demo_session_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_event_logs');
    }
};
