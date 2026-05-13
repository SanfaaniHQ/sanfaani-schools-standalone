<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_promotion_items', function (Blueprint $table) {
            if (! Schema::hasColumn('student_promotion_items', 'from_student_class_enrollment_id')) {
                $table->foreignId('from_student_class_enrollment_id')
                    ->nullable()
                    ->after('to_academic_session_id')
                    ->constrained('student_class_enrollments')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('student_promotion_items', 'to_student_class_enrollment_id')) {
                $table->foreignId('to_student_class_enrollment_id')
                    ->nullable()
                    ->after('from_student_class_enrollment_id')
                    ->constrained('student_class_enrollments')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('student_promotion_items', 'metadata')) {
                $table->json('metadata')->nullable()->after('notes');
            }

            $table->index(['school_id', 'student_id', 'action', 'status'], 'promo_item_lifecycle_idx');
            $table->index(['from_student_class_enrollment_id', 'to_student_class_enrollment_id'], 'promo_item_enrollment_lineage_idx');
        });

        Schema::table('student_class_enrollments', function (Blueprint $table) {
            $table->index(['school_id', 'student_id', 'status', 'end_term_id'], 'stu_enroll_current_lookup_idx');
            $table->index(['promoted_from_enrollment_id'], 'stu_enroll_lineage_idx');
        });

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'deleted_at')) {
                $table->index(['school_id', 'status', 'deleted_at'], 'students_school_status_archive_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasIndex('students', 'students_school_status_archive_idx')) {
                $table->dropIndex('students_school_status_archive_idx');
            }
        });

        Schema::table('student_class_enrollments', function (Blueprint $table) {
            $table->dropIndex('stu_enroll_lineage_idx');
            $table->dropIndex('stu_enroll_current_lookup_idx');
        });

        Schema::table('student_promotion_items', function (Blueprint $table) {
            $table->dropIndex('promo_item_enrollment_lineage_idx');
            $table->dropIndex('promo_item_lifecycle_idx');

            if (Schema::hasColumn('student_promotion_items', 'metadata')) {
                $table->dropColumn('metadata');
            }

            if (Schema::hasColumn('student_promotion_items', 'to_student_class_enrollment_id')) {
                $table->dropConstrainedForeignId('to_student_class_enrollment_id');
            }

            if (Schema::hasColumn('student_promotion_items', 'from_student_class_enrollment_id')) {
                $table->dropConstrainedForeignId('from_student_class_enrollment_id');
            }
        });
    }
};
