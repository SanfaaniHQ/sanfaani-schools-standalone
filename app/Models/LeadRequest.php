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
        'message',
        'source',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
