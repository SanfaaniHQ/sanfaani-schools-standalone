<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('activation_fingerprint', 128);
            $table->string('domain')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('activated_at');
            $table->timestamp('last_seen_at')->nullable();
            $table->string('status', 50)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['license_id', 'status']);
            $table->index(['school_id', 'status']);
            $table->unique(['license_id', 'activation_fingerprint'], 'license_activation_fingerprint_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_activations');
    }
};
