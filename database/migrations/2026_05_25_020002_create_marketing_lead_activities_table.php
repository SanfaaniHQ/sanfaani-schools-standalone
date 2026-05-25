<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_lead_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lead_request_id')->nullable()->constrained('lead_requests')->nullOnDelete();
            $table->foreignId('demo_request_id')->nullable()->constrained('demo_requests')->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->text('description')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['lead_request_id', 'event'], 'marketing_activities_lead_event_idx');
            $table->index(['demo_request_id', 'event'], 'marketing_activities_demo_event_idx');
            $table->index(['school_id', 'event'], 'marketing_activities_school_event_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_lead_activities');
    }
};
