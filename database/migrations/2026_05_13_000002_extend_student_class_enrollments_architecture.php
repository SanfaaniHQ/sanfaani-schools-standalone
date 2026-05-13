<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('student_class_enrollments', 'stu_enroll_student_fk_idx')) {
            Schema::table('student_class_enrollments', function (Blueprint $table) {
                $table->index('student_id', 'stu_enroll_student_fk_idx');
            });
        }

        Schema::table('student_class_enrollments', function (Blueprint $table) {
            if (Schema::hasIndex('student_class_enrollments', 'stu_enroll_session_unique')) {
                $table->dropUnique('stu_enroll_session_unique');
            }

            if (! Schema::hasColumn('student_class_enrollments', 'start_term_id')) {
                $table->foreignId('start_term_id')
                    ->nullable()
                    ->after('academic_session_id')
                    ->constrained('terms')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('student_class_enrollments', 'end_term_id')) {
                $table->foreignId('end_term_id')
                    ->nullable()
                    ->after('start_term_id')
                    ->constrained('terms')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('student_class_enrollments', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('status')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasIndex('student_class_enrollments', 'stu_enroll_student_status_session_idx')) {
                $table->index(
                    ['school_id', 'student_id', 'status', 'academic_session_id'],
                    'stu_enroll_student_status_session_idx'
                );
            }

            if (! Schema::hasIndex('student_class_enrollments', 'stu_enroll_history_lookup_idx')) {
                $table->index(
                    ['school_id', 'student_id', 'school_class_id', 'academic_session_id', 'start_term_id'],
                    'stu_enroll_history_lookup_idx'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_class_enrollments', function (Blueprint $table) {
            if (Schema::hasIndex('student_class_enrollments', 'stu_enroll_student_status_session_idx')) {
                $table->dropIndex('stu_enroll_student_status_session_idx');
            }

            if (Schema::hasIndex('student_class_enrollments', 'stu_enroll_history_lookup_idx')) {
                $table->dropIndex('stu_enroll_history_lookup_idx');
            }

            if (Schema::hasColumn('student_class_enrollments', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            if (Schema::hasColumn('student_class_enrollments', 'end_term_id')) {
                $table->dropConstrainedForeignId('end_term_id');
            }

            if (Schema::hasColumn('student_class_enrollments', 'start_term_id')) {
                $table->dropConstrainedForeignId('start_term_id');
            }

            $table->unique(['student_id', 'academic_session_id'], 'stu_enroll_session_unique');

            if (Schema::hasIndex('student_class_enrollments', 'stu_enroll_student_fk_idx')) {
                $table->dropIndex('stu_enroll_student_fk_idx');
            }
        });
    }
};
