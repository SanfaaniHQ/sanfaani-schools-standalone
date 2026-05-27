<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_automation_sequences', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('trigger_event')->nullable();
            $table->string('audience')->nullable();
            $table->string('status')->default('paused');
            $table->json('filters')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status'], 'marketing_sequences_status_trigger_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_automation_sequences');
    }
};
