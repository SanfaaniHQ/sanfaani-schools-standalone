<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demo_session_id')->constrained('demo_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 100);
            $table->text('description')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['demo_session_id', 'created_at']);
            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_activities');
    }
};
