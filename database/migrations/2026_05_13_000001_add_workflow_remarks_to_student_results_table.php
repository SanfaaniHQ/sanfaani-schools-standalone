<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_results', function (Blueprint $table) {
            if (! Schema::hasColumn('student_results', 'officer_remark')) {
                $table->text('officer_remark')->nullable()->after('teacher_remark');
            }

            if (! Schema::hasColumn('student_results', 'admin_remark')) {
                $table->text('admin_remark')->nullable()->after('officer_remark');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_results', function (Blueprint $table) {
            if (Schema::hasColumn('student_results', 'admin_remark')) {
                $table->dropColumn('admin_remark');
            }

            if (Schema::hasColumn('student_results', 'officer_remark')) {
                $table->dropColumn('officer_remark');
            }
        });
    }
};
