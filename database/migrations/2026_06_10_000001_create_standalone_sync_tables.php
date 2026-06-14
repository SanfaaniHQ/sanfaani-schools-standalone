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
            $table->uuid('uuid');
            $table->string('name')->nullable();
            $table->string('type', 50)->default('local_server');
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('uuid', 'sync_devices_uuid_uidx');
            $table->index(['type', 'is_active'], 'sync_devices_type_idx');
            $table->index('last_seen_at', 'sync_devices_seen_idx');
        });

        Schema::create('standalone_sync_outbox', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('entity_type', 64);
            $table->string('entity_id', 64)->nullable();
            $table->string('action', 50);
            $table->json('payload')->nullable();
            $table->string('payload_hash', 64);
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('available_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('uuid', 'sync_outbox_uuid_uidx');
            $table->index(['status', 'available_at'], 'sync_outbox_status_idx');
            $table->index(['entity_type', 'entity_id'], 'sync_outbox_entity_idx');
            $table->index('payload_hash', 'sync_outbox_hash_idx');
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

            $table->index(['direction', 'status'], 'sync_logs_status_idx');
            $table->index('started_at', 'sync_logs_started_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standalone_sync_logs');
        Schema::dropIfExists('standalone_sync_outbox');
        Schema::dropIfExists('standalone_sync_devices');
    }
};
