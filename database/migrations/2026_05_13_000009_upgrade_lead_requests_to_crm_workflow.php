<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('lead_requests', 'next_follow_up_at')) {
                $table->timestamp('next_follow_up_at')->nullable()->after('contacted_at');
            }

            if (! Schema::hasColumn('lead_requests', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('next_follow_up_at');
            }

            if (! Schema::hasColumn('lead_requests', 'converted_at')) {
                $table->timestamp('converted_at')->nullable()->after('last_activity_at');
            }

            if (! Schema::hasColumn('lead_requests', 'converted_by')) {
                $table->foreignId('converted_by')->nullable()->after('converted_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('lead_requests', 'converted_school_id')) {
                $table->foreignId('converted_school_id')->nullable()->after('converted_by')->constrained('schools')->nullOnDelete();
            }

            if (! Schema::hasColumn('lead_requests', 'lost_reason')) {
                $table->text('lost_reason')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('lead_requests', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('lost_reason');
            }

            if (! Schema::hasColumn('lead_requests', 'deleted_at')) {
                $table->softDeletes();
            }

            $table->index(['status', 'next_follow_up_at'], 'lead_crm_status_followup_idx');
            $table->index(['assigned_to', 'next_follow_up_at'], 'lead_crm_assignee_followup_idx');
            $table->index(['converted_school_id', 'converted_at'], 'lead_crm_conversion_idx');
            $table->index(['created_at', 'status'], 'lead_crm_created_status_idx');
        });

        Schema::create('lead_ownership_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_request_id')->constrained('lead_requests')->cascadeOnDelete();
            $table->foreignId('old_assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('new_assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['lead_request_id', 'changed_at'], 'lead_owner_history_lead_date_idx');
            $table->index(['new_assigned_to', 'changed_at'], 'lead_owner_history_new_owner_idx');
        });

        Schema::create('lead_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_request_id')->constrained('lead_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note_type', 50)->default('internal');
            $table->text('body');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lead_request_id', 'created_at'], 'lead_notes_lead_date_idx');
            $table->index(['user_id', 'created_at'], 'lead_notes_user_date_idx');
        });

        Schema::create('lead_communication_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_request_id')->constrained('lead_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('communication_log_id')->nullable()->constrained('communication_logs')->nullOnDelete();
            $table->string('channel', 50)->default('email');
            $table->string('direction', 30)->default('outbound');
            $table->string('recipient')->nullable();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('status', 50)->default('recorded');
            $table->timestamp('communicated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lead_request_id', 'communicated_at'], 'lead_comm_lead_date_idx');
            $table->index(['communication_log_id'], 'lead_comm_log_idx');
            $table->index(['channel', 'status'], 'lead_comm_channel_status_idx');
        });

        Schema::create('lead_timeline_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_request_id')->constrained('lead_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 80);
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['lead_request_id', 'occurred_at'], 'lead_timeline_lead_date_idx');
            $table->index(['event_type', 'occurred_at'], 'lead_timeline_type_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_timeline_events');
        Schema::dropIfExists('lead_communication_records');
        Schema::dropIfExists('lead_notes');
        Schema::dropIfExists('lead_ownership_histories');

        Schema::table('lead_requests', function (Blueprint $table) {
            foreach ([
                'lead_crm_status_followup_idx',
                'lead_crm_assignee_followup_idx',
                'lead_crm_conversion_idx',
                'lead_crm_created_status_idx',
            ] as $index) {
                $table->dropIndex($index);
            }

            foreach (['converted_school_id', 'converted_by'] as $column) {
                if (Schema::hasColumn('lead_requests', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach ([
                'deleted_at',
                'archived_at',
                'lost_reason',
                'converted_at',
                'last_activity_at',
                'next_follow_up_at',
            ] as $column) {
                if (Schema::hasColumn('lead_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
