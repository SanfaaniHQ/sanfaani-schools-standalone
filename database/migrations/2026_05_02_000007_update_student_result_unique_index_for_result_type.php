<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('student_results', 'result_type')) {
            return;
        }

        $this->dropIndexIfExists('unique_student_subject_term_result');

        $this->createUniqueIndex(
            'unique_student_subject_term_type_result',
            ['school_id', 'student_id', 'subject_id', 'academic_session_id', 'term_id', 'result_type']
        );
    }

    public function down(): void
    {
        $this->dropIndexIfExists('unique_student_subject_term_type_result');

        $this->createUniqueIndex(
            'unique_student_subject_term_result',
            ['school_id', 'student_id', 'subject_id', 'academic_session_id', 'term_id']
        );
    }

    private function dropIndexIfExists(string $index): void
    {
        try {
            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::statement("DROP INDEX IF EXISTS {$index}");

                return;
            }

            DB::statement("ALTER TABLE student_results DROP INDEX {$index}");
        } catch (QueryException $exception) {
            $message = $exception->getMessage();

            if (! str_contains($message, 'check that column/key exists') && ! str_contains($message, "Can't DROP")) {
                throw $exception;
            }
        }
    }

    private function createUniqueIndex(string $index, array $columns): void
    {
        $columnList = implode(', ', $columns);

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement("CREATE UNIQUE INDEX {$index} ON student_results ({$columnList})");

            return;
        }

        DB::statement("ALTER TABLE student_results ADD UNIQUE {$index} ({$columnList})");
    }
};
