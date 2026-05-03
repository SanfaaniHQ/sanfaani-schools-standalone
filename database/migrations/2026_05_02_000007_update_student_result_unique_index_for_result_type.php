<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('student_results', 'result_type')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE student_results DROP INDEX unique_student_subject_term_result');
        } catch (Throwable) {
            //
        }

        try {
            DB::statement(
                'ALTER TABLE student_results ADD UNIQUE unique_student_subject_term_type_result ' .
                '(school_id, student_id, subject_id, academic_session_id, term_id, result_type)'
            );
        } catch (Throwable) {
            //
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE student_results DROP INDEX unique_student_subject_term_type_result');
        } catch (Throwable) {
            //
        }

        try {
            DB::statement(
                'ALTER TABLE student_results ADD UNIQUE unique_student_subject_term_result ' .
                '(school_id, student_id, subject_id, academic_session_id, term_id)'
            );
        } catch (Throwable) {
            //
        }
    }
};
