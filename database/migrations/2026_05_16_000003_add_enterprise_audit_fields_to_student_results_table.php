<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_results', function (Blueprint $table) {
            if (! Schema::hasColumn('student_results', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('recorded_by')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('student_results', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('student_results', 'result_version')) {
                $table->unsignedInteger('result_version')->default(1)->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_results', function (Blueprint $table) {
            if (Schema::hasColumn('student_results', 'result_version')) {
                $table->dropColumn('result_version');
            }

            if (Schema::hasColumn('student_results', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }

            if (Schema::hasColumn('student_results', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
        });
    }
};
