<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            if (! Schema::hasColumn('school_classes', 'code')) {
                $table->string('code', 50)->nullable()->after('name');
                $table->index(['school_id', 'code'], 'classes_school_code_idx');
            }
        });

        Schema::table('subjects', function (Blueprint $table) {
            if (! Schema::hasColumn('subjects', 'assignment_type')) {
                $table->string('assignment_type', 50)->default('core')->after('code');
            }

            if (! Schema::hasColumn('subjects', 'is_elective')) {
                $table->boolean('is_elective')->default(false)->after('assignment_type');
            }

            $table->index(['school_id', 'assignment_type'], 'subjects_school_type_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'action_tag')) {
                $table->string('action_tag', 100)->nullable()->after('action');
                $table->index(['action_tag', 'created_at'], 'audit_tag_date_idx');
            }

            if (! Schema::hasColumn('audit_logs', 'severity')) {
                $table->string('severity', 50)->default('info')->after('action_tag');
                $table->index(['severity', 'created_at'], 'audit_severity_date_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'severity')) {
                $table->dropIndex('audit_severity_date_idx');
                $table->dropColumn('severity');
            }

            if (Schema::hasColumn('audit_logs', 'action_tag')) {
                $table->dropIndex('audit_tag_date_idx');
                $table->dropColumn('action_tag');
            }
        });

        Schema::table('subjects', function (Blueprint $table) {
            if (Schema::hasColumn('subjects', 'assignment_type')) {
                $table->dropIndex('subjects_school_type_idx');
                $table->dropColumn(['assignment_type', 'is_elective']);
            }
        });

        Schema::table('school_classes', function (Blueprint $table) {
            if (Schema::hasColumn('school_classes', 'code')) {
                $table->dropIndex('classes_school_code_idx');
                $table->dropColumn('code');
            }
        });
    }
};
