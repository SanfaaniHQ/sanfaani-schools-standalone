<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_automation_enrollments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('marketing_automation_sequence_id')->constrained('marketing_automation_sequences')->cascadeOnDelete();
            $table->foreignId('lead_request_id')->nullable()->constrained('lead_requests')->nullOnDelete();
            $table->foreignId('demo_request_id')->nullable()->constrained('demo_requests')->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['marketing_automation_sequence_id', 'status'], 'marketing_enrollments_sequence_status_idx');
            $table->index(['lead_request_id', 'status'], 'marketing_enrollments_lead_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_automation_enrollments');
    }
};
