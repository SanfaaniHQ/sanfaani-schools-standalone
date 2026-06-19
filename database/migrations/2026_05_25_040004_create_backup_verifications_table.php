<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backup_id')->constrained()->cascadeOnDelete();
            $table->string('status', 50)->index('backup_verifications_status_idx');
            $table->timestamp('checked_at')->nullable()->index();
            $table->boolean('checksum_valid')->nullable();
            $table->boolean('archive_readable')->nullable();
            $table->boolean('required_items_present')->nullable();
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_verifications');
    }
};
