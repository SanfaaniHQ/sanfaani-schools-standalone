<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_school_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('role_name', 50);
            $table->string('status', 50)->default('active');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'school_id', 'role_name'], 'usr_school_role_unq');
            $table->index(['user_id', 'status'], 'usr_user_status_idx');
            $table->index(['school_id', 'role_name'], 'usr_school_role_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_school_roles');
    }
};
