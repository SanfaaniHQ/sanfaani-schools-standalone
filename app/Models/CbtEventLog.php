<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbtEventLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'cbt_exam_id',
        'cbt_candidate_id',
        'cbt_attempt_id',
        'user_id',
        'event',
        'severity',
        'ip_address',
        'user_agent',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
