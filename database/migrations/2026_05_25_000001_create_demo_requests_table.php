<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email', 191);
            $table->string('phone')->nullable();
            $table->string('school_name')->nullable();
            $table->string('role_interest')->nullable();
            $table->string('source')->nullable();
            $table->string('status', 50)->default('requested');
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['email', 'status'], 'demo_requests_email_status_idx');
            $table->index(['status', 'created_at'], 'demo_requests_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_requests');
    }
};
