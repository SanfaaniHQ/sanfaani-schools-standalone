<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('attendance_date');
            $table->string('status', 30);
            $table->text('note')->nullable();
            $table->string('source', 30)->default('web');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(
                ['school_id', 'school_class_id', 'student_id', 'attendance_date'],
                'attendance_unique_student_day'
            );
            $table->index(['school_id', 'attendance_date'], 'attendance_school_date_idx');
            $table->index(['school_class_id', 'attendance_date'], 'attendance_class_date_idx');
            $table->index(['student_id', 'attendance_date'], 'attendance_student_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendance_records');
    }
};
