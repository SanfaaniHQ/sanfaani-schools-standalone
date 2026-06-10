<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standalone_sync_devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->nullable();
            $table->string('type', 50)->default('local_server');
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index('last_seen_at');
        });

        Schema::create('standalone_sync_outbox', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('entity_type');
            $table->string('entity_id')->nullable();
            $table->string('action', 50);
            $table->json('payload')->nullable();
            $table->string('payload_hash', 64);
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('available_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'available_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('payload_hash');
        });

        Schema::create('standalone_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('direction', 20);
            $table->string('status', 20);
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['direction', 'status']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standalone_sync_logs');
        Schema::dropIfExists('standalone_sync_outbox');
        Schema::dropIfExists('standalone_sync_devices');
    }
};
