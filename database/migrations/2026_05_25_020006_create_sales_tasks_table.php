<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lead_request_id')->nullable()->constrained('lead_requests')->nullOnDelete();
            $table->foreignId('demo_request_id')->nullable()->constrained('demo_requests')->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('open');
            $table->string('priority')->default('normal');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_at'], 'sales_tasks_status_due_idx');
            $table->index(['lead_request_id', 'status'], 'sales_tasks_lead_status_idx');
            $table->index(['school_id', 'status'], 'sales_tasks_school_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_tasks');
    }
};
