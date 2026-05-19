<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingEmailTemplate extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'preview_text',
        'body',
        'status',
        'created_by',
        'updated_by',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
