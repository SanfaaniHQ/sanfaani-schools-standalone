<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolReportCardSetting extends Model
{
    protected $fillable = [
        'school_id',
        'report_card_template_id',
        'primary_color',
        'accent_color',
        'school_name_font',
        'header_type',
        'student_info_layout',
        'result_table_style',
        'show_logo',
        'show_school_address',
        'show_school_phone',
        'show_school_email',
        'show_student_photo',
        'show_teacher_remark',
        'show_class_teacher',
        'show_head_teacher',
        'class_teacher_title',
        'head_teacher_title',
        'class_teacher_name',
        'head_teacher_name',
        'class_teacher_signature_path',
        'head_teacher_signature_path',
        'enable_auto_class_teacher_comment',
        'enable_auto_head_teacher_comment',
        'metadata',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_school_address' => 'boolean',
        'show_school_phone' => 'boolean',
        'show_school_email' => 'boolean',
        'show_student_photo' => 'boolean',
        'show_teacher_remark' => 'boolean',
        'show_class_teacher' => 'boolean',
        'show_head_teacher' => 'boolean',
        'enable_auto_class_teacher_comment' => 'boolean',
        'enable_auto_head_teacher_comment' => 'boolean',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportCardTemplate::class, 'report_card_template_id');
    }
}
