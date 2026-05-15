<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_result_access_policies', function (Blueprint $table) {
            if (! Schema::hasColumn('school_result_access_policies', 'notes')) {
                $table->text('notes')->nullable()->after('ends_at');
            }
        });

        Schema::table('lead_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('lead_requests', 'number_of_students')) {
                $table->unsignedInteger('number_of_students')->nullable()->after('role');
            }

            if (! Schema::hasColumn('lead_requests', 'school_type')) {
                $table->string('school_type')->nullable()->after('number_of_students');
            }

            if (! Schema::hasColumn('lead_requests', 'preferred_demo_time')) {
                $table->string('preferred_demo_time')->nullable()->after('school_type');
            }

            if (! Schema::hasColumn('lead_requests', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('lead_requests', 'contacted_at')) {
                $table->timestamp('contacted_at')->nullable()->after('assigned_to');
            }

            if (! Schema::hasColumn('lead_requests', 'notes')) {
                $table->text('notes')->nullable()->after('contacted_at');
            }
        });

        if (! Schema::hasTable('system_update_logs')) {
            Schema::create('system_update_logs', function (Blueprint $table) {
                $table->id();
                $table->string('from_version')->nullable();
                $table->string('to_version')->nullable();
                $table->string('update_type');
                $table->string('status')->default('uploaded');
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('package_path')->nullable();
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_update_logs');

        Schema::table('lead_requests', function (Blueprint $table) {
            foreach (['notes', 'contacted_at', 'assigned_to', 'preferred_demo_time', 'school_type', 'number_of_students'] as $column) {
                if (Schema::hasColumn('lead_requests', $column)) {
                    if ($column === 'assigned_to') {
                        $table->dropConstrainedForeignId($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });

        Schema::table('school_result_access_policies', function (Blueprint $table) {
            if (Schema::hasColumn('school_result_access_policies', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
