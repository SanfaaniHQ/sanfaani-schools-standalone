<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'channel',
        'release_date',
        'is_current',
        'metadata',
    ];

    protected $casts = [
        'release_date' => 'date',
        'is_current' => 'boolean',
        'metadata' => 'array',
    ];
}
