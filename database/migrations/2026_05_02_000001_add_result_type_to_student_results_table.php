<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_results', function (Blueprint $table) {
            if (! Schema::hasColumn('student_results', 'result_type')) {
                $table->string('result_type', 50)
                    ->default('term_result')
                    ->after('term_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_results', function (Blueprint $table) {
            if (Schema::hasColumn('student_results', 'result_type')) {
                $table->dropColumn('result_type');
            }
        });
    }
};
