<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('update_packages', function (Blueprint $table) {
            $table->id();
            $table->string('version', 50);
            $table->string('channel', 50)->default('stable');
            $table->string('source', 50)->default('upload');
            $table->string('filename')->nullable();
            $table->string('path')->nullable();
            $table->string('checksum', 128)->nullable();
            $table->text('signature')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('status', 50)->default('uploaded');
            $table->json('manifest')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'channel']);
            $table->index(['version', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('update_packages');
    }
};
