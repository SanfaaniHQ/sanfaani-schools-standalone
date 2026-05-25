<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_lead_scores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lead_request_id')->nullable()->constrained('lead_requests')->nullOnDelete();
            $table->foreignId('demo_request_id')->nullable()->constrained('demo_requests')->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->integer('score')->default(0);
            $table->string('segment')->nullable();
            $table->json('factors')->nullable();
            $table->timestamp('last_scored_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['lead_request_id', 'score'], 'marketing_scores_lead_score_idx');
            $table->index(['demo_request_id', 'score'], 'marketing_scores_demo_score_idx');
            $table->index(['school_id', 'score'], 'marketing_scores_school_score_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_lead_scores');
    }
};
