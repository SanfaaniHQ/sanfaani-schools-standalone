<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionNumberSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'prefix',
        'separator',
        'year_format',
        'next_number',
        'padding_length',
        'suffix',
        'reset_cycle',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'next_number' => 'integer',
        'padding_length' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
