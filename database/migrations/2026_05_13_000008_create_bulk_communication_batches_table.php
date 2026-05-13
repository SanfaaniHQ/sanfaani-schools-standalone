<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_communication_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_uuid')->unique();
            $table->foreignId('school_id')->constrained('schools')->restrictOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('audience', 50);
            $table->json('channels');
            $table->string('type', 50);
            $table->string('subject');
            $table->text('body');
            $table->string('status', 50)->default('pending');
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('duplicate_count')->default(0);
            $table->unsignedSmallInteger('chunk_size')->default(25);
            $table->char('request_fingerprint', 64)->nullable();
            $table->json('filters')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status', 'created_at'], 'bulk_comm_batch_school_status_idx');
            $table->index(['school_id', 'audience', 'created_at'], 'bulk_comm_batch_audience_idx');
            $table->index(['school_id', 'sender_id', 'request_fingerprint'], 'bulk_comm_batch_dedupe_idx');
        });

        Schema::create('bulk_communication_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_communication_batch_id');
            $table->foreignId('school_id')->constrained('schools')->restrictOnDelete();
            $table->string('channel', 30)->default('email');
            $table->string('recipient_type', 30);
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_address')->nullable();
            $table->string('status', 50)->default('pending');
            $table->foreignId('communication_log_id')->nullable()->constrained('communication_logs')->nullOnDelete();
            $table->text('failure_reason')->nullable();
            $table->char('fingerprint', 64);
            $table->timestamp('attempted_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('bulk_communication_batch_id', 'bulk_comm_rec_batch_fk')
                ->references('id')
                ->on('bulk_communication_batches')
                ->cascadeOnDelete();
            $table->unique(
                ['bulk_communication_batch_id', 'channel', 'fingerprint'],
                'bulk_comm_recipient_unique'
            );
            $table->index(
                ['bulk_communication_batch_id', 'status', 'id'],
                'bulk_comm_recipient_status_idx'
            );
            $table->index(['school_id', 'recipient_type', 'recipient_id'], 'bulk_comm_recipient_target_idx');
            $table->index(['communication_log_id'], 'bulk_comm_recipient_log_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_communication_recipients');
        Schema::dropIfExists('bulk_communication_batches');
    }
};
