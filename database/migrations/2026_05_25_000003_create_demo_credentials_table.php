<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demo_session_id')->constrained('demo_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_name', 80);
            $table->string('label')->nullable();
            $table->string('email', 191);
            $table->text('temporary_password_encrypted')->nullable();
            $table->timestamp('password_viewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 50)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['demo_session_id', 'role_name'], 'demo_credentials_session_role_unique');
            $table->index(['email', 'status'], 'demo_credentials_email_status_idx');
            $table->index(['user_id', 'status'], 'demo_credentials_user_status_idx');
            $table->index(['status', 'expires_at'], 'demo_credentials_status_expiry_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_credentials');
    }
};
