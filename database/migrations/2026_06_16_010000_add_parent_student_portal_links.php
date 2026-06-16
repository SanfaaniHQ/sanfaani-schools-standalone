<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'student_user_id')) {
                $table->foreignId('student_user_id')
                    ->nullable()
                    ->after('school_id')
                    ->constrained('users')
                    ->nullOnDelete();

                $table->unique(['school_id', 'student_user_id'], 'students_school_student_user_unique');
            }
        });

        if (! Schema::hasTable('parent_student')) {
            Schema::create('parent_student', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('parent_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->string('relationship')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->boolean('can_view_results')->default(true);
                $table->boolean('can_view_attendance')->default(true);
                $table->boolean('can_view_finance')->default(true);
                $table->boolean('receives_notifications')->default(true);
                $table->timestamps();

                $table->unique(['parent_user_id', 'student_id'], 'parent_student_parent_student_unique');
                $table->index(['school_id', 'parent_user_id'], 'parent_student_school_parent_index');
                $table->index(['school_id', 'student_id'], 'parent_student_school_student_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_student');

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'student_user_id')) {
                $table->dropUnique('students_school_student_user_unique');
                $table->dropConstrainedForeignId('student_user_id');
            }
        });
    }
};
