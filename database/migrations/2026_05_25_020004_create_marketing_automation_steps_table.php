<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_automation_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('marketing_automation_sequence_id')
                ->constrained('marketing_automation_sequences', indexName: 'marketing_steps_sequence_fk')
                ->cascadeOnDelete();
            $table->string('key');
            $table->string('channel')->default('email');
            $table->string('mail_type')->nullable();
            $table->string('subject')->nullable();
            $table->unsignedInteger('delay_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['marketing_automation_sequence_id', 'key'], 'marketing_steps_sequence_key_unique');
            $table->index(['channel', 'is_active'], 'marketing_steps_channel_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_automation_steps');
    }
};
