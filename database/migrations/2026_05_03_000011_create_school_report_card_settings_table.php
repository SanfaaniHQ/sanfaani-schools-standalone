<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_report_card_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->unique()->constrained('schools')->cascadeOnDelete();
            $table->foreignId('report_card_template_id')->nullable()->constrained('report_card_templates')->nullOnDelete();
            $table->string('primary_color')->nullable();
            $table->string('accent_color')->nullable();
            $table->string('school_name_font')->nullable();
            $table->string('header_type')->default('classic');
            $table->string('student_info_layout')->default('two_column');
            $table->string('result_table_style')->default('standard');
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_school_address')->default(true);
            $table->boolean('show_school_phone')->default(true);
            $table->boolean('show_school_email')->default(true);
            $table->boolean('show_student_photo')->default(false);
            $table->boolean('show_teacher_remark')->default(true);
            $table->boolean('show_class_teacher')->default(true);
            $table->boolean('show_head_teacher')->default(true);
            $table->string('class_teacher_title')->nullable();
            $table->string('head_teacher_title')->nullable();
            $table->string('class_teacher_name')->nullable();
            $table->string('head_teacher_name')->nullable();
            $table->string('class_teacher_signature_path')->nullable();
            $table->string('head_teacher_signature_path')->nullable();
            $table->boolean('enable_auto_class_teacher_comment')->default(false);
            $table->boolean('enable_auto_head_teacher_comment')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_report_card_settings');
    }
};
