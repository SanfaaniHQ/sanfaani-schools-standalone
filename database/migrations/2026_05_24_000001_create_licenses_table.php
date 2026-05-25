<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('license_key_hash', 128)->unique();
            $table->string('license_type', 50);
            $table->string('status', 50)->default('active');
            $table->string('issued_to_name')->nullable();
            $table->string('issued_to_email')->nullable();
            $table->string('domain')->nullable();
            $table->json('allowed_domains')->nullable();
            $table->json('features')->nullable();
            $table->json('entitlements')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamp('offline_grace_until')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status', 'license_type']);
            $table->index(['expires_at', 'offline_grace_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
