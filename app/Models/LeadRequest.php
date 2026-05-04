<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'school_name',
        'email',
        'phone',
        'role',
        'number_of_students',
        'school_type',
        'preferred_demo_time',
        'message',
        'source',
        'status',
        'assigned_to',
        'contacted_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'metadata' => 'array',
    ];
}
