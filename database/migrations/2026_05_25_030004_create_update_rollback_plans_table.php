<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('update_rollback_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('update_package_id')->nullable()->constrained('update_packages')->nullOnDelete();
            $table->string('from_version', 50)->nullable();
            $table->string('to_version', 50)->nullable();
            $table->string('status', 50)->default('planned');
            $table->string('backup_reference')->nullable();
            $table->json('steps')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'from_version', 'to_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('update_rollback_plans');
    }
};
