<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (! Schema::hasColumn('audit_logs', 'actor_id')) {
                    $table->foreignId('actor_id')->nullable()->after('school_id')->constrained('users')->nullOnDelete();
                }

                if (! Schema::hasColumn('audit_logs', 'actor_type')) {
                    $table->string('actor_type', 30)->default('user')->after('actor_id');
                }

                if (! Schema::hasColumn('audit_logs', 'category')) {
                    $table->string('category', 60)->nullable()->after('actor_type');
                }

                if (! Schema::hasColumn('audit_logs', 'event')) {
                    $table->string('event', 150)->nullable()->after('category');
                }

                if (! Schema::hasColumn('audit_logs', 'payload')) {
                    $table->json('payload')->nullable()->after('event');
                }
            });

            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index(['school_id', 'category', 'created_at'], 'audit_school_category_date_idx');
                $table->index(['event', 'created_at'], 'audit_event_date_idx');
                $table->index(['actor_id', 'created_at'], 'audit_actor_date_idx');
            });
        }

        if (! Schema::hasTable('support_access_logs')) {
            Schema::create('support_access_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('impersonator_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('target_school_id')->constrained('schools')->cascadeOnDelete();
                $table->string('action');
                $table->text('reason')->nullable();
                $table->string('ip', 100)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();

                $table->index(['target_school_id', 'created_at'], 'support_school_date_idx');
                $table->index(['impersonator_id', 'created_at'], 'support_actor_date_idx');
            });
        }
    }

    public function down(): void
    {
        // Zero-data-loss rollback policy: keep support access history intact.

        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                foreach (['audit_actor_date_idx', 'audit_event_date_idx', 'audit_school_category_date_idx'] as $index) {
                    try {
                        $table->dropIndex($index);
                    } catch (Throwable) {
                        //
                    }
                }

                foreach (['payload', 'event', 'category', 'actor_type'] as $column) {
                    if (Schema::hasColumn('audit_logs', $column)) {
                        $table->dropColumn($column);
                    }
                }

                if (Schema::hasColumn('audit_logs', 'actor_id')) {
                    $table->dropConstrainedForeignId('actor_id');
                }
            });
        }
    }
};
