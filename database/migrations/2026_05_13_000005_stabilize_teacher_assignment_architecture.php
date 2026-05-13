<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_class_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('teacher_class_assignments', 'starts_at')) {
                $table->date('starts_at')->nullable()->after('term_id');
            }

            if (! Schema::hasColumn('teacher_class_assignments', 'ends_at')) {
                $table->date('ends_at')->nullable()->after('starts_at');
            }

            $table->index(['school_id', 'teacher_user_id', 'status', 'school_class_id'], 'tca_school_teacher_class_idx');
            $table->index(['school_id', 'school_class_id', 'role_type', 'status'], 'tca_school_class_role_idx');
            $table->index(['academic_session_id', 'term_id', 'status'], 'tca_context_status_idx');
        });

        Schema::table('teacher_subject_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('teacher_subject_assignments', 'role_type')) {
                $table->string('role_type', 50)->default('subject_teacher')->after('term_id');
            }

            if (! Schema::hasColumn('teacher_subject_assignments', 'starts_at')) {
                $table->date('starts_at')->nullable()->after('role_type');
            }

            if (! Schema::hasColumn('teacher_subject_assignments', 'ends_at')) {
                $table->date('ends_at')->nullable()->after('starts_at');
            }

            $table->index(['school_id', 'teacher_user_id', 'status', 'subject_id'], 'tsa_school_teacher_subject_idx');
            $table->index(['school_id', 'subject_id', 'school_class_id', 'status'], 'tsa_school_subject_class_idx');
            $table->index(['academic_session_id', 'term_id', 'status'], 'tsa_context_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_subject_assignments', function (Blueprint $table) {
            $table->dropIndex('tsa_context_status_idx');
            $table->dropIndex('tsa_school_subject_class_idx');
            $table->dropIndex('tsa_school_teacher_subject_idx');

            if (Schema::hasColumn('teacher_subject_assignments', 'ends_at')) {
                $table->dropColumn('ends_at');
            }

            if (Schema::hasColumn('teacher_subject_assignments', 'starts_at')) {
                $table->dropColumn('starts_at');
            }

            if (Schema::hasColumn('teacher_subject_assignments', 'role_type')) {
                $table->dropColumn('role_type');
            }
        });

        Schema::table('teacher_class_assignments', function (Blueprint $table) {
            $table->dropIndex('tca_context_status_idx');
            $table->dropIndex('tca_school_class_role_idx');
            $table->dropIndex('tca_school_teacher_class_idx');

            if (Schema::hasColumn('teacher_class_assignments', 'ends_at')) {
                $table->dropColumn('ends_at');
            }

            if (Schema::hasColumn('teacher_class_assignments', 'starts_at')) {
                $table->dropColumn('starts_at');
            }
        });
    }
};
