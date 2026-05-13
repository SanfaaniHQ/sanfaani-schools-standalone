<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_threads', function (Blueprint $table) {
            if (! Schema::hasColumn('support_threads', 'creator_role')) {
                $table->string('creator_role', 50)->nullable()->after('created_by');
            }

            if (! Schema::hasColumn('support_threads', 'routed_to_role')) {
                $table->string('routed_to_role', 50)->nullable()->after('assigned_to');
            }

            if (! Schema::hasColumn('support_threads', 'escalation_level')) {
                $table->unsignedTinyInteger('escalation_level')->default(0)->after('visibility');
            }

            if (! Schema::hasColumn('support_threads', 'escalated_at')) {
                $table->timestamp('escalated_at')->nullable()->after('escalation_level');
            }

            if (! Schema::hasColumn('support_threads', 'escalated_by')) {
                $table->foreignId('escalated_by')->nullable()->after('escalated_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('support_threads', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('escalated_by');
            }

            if (! Schema::hasColumn('support_threads', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('resolved_at');
            }

            $table->index(['school_id', 'routed_to_role', 'status'], 'sth_school_route_status_idx');
            $table->index(['created_by', 'status'], 'sth_creator_status_idx');
            $table->index(['escalation_level', 'escalated_at'], 'sth_escalation_idx');
            $table->index(['assigned_to', 'status'], 'sth_assignee_status_idx');
        });

        Schema::create('support_escalation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_thread_id')->constrained('support_threads')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('escalated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_role', 50)->nullable();
            $table->string('to_role', 50)->default('super_admin');
            $table->unsignedTinyInteger('from_level')->default(0);
            $table->unsignedTinyInteger('to_level')->default(1);
            $table->text('reason')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['support_thread_id', 'escalated_at'], 'seh_thread_date_idx');
            $table->index(['school_id', 'escalated_at'], 'seh_school_date_idx');
            $table->index(['to_role', 'escalated_at'], 'seh_role_date_idx');
        });

        Schema::create('support_thread_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_thread_id')->constrained('support_threads')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_role', 50)->nullable();
            $table->string('event_type', 80);
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['support_thread_id', 'occurred_at'], 'ste_thread_date_idx');
            $table->index(['school_id', 'occurred_at'], 'ste_school_date_idx');
            $table->index(['event_type', 'occurred_at'], 'ste_type_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_thread_events');
        Schema::dropIfExists('support_escalation_histories');

        Schema::table('support_threads', function (Blueprint $table) {
            foreach ([
                'sth_school_route_status_idx',
                'sth_creator_status_idx',
                'sth_escalation_idx',
                'sth_assignee_status_idx',
            ] as $index) {
                $table->dropIndex($index);
            }

            if (Schema::hasColumn('support_threads', 'escalated_by')) {
                $table->dropConstrainedForeignId('escalated_by');
            }

            foreach ([
                'closed_at',
                'resolved_at',
                'escalated_at',
                'escalation_level',
                'routed_to_role',
                'creator_role',
            ] as $column) {
                if (Schema::hasColumn('support_threads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
