<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemoRequest extends Model
{
    use HasFactory;

    public const STATUS_REQUESTED = 'requested';

    public const STATUS_ENVIRONMENT_CREATED = 'environment_created';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CONVERTED = 'converted';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'school_name',
        'role_interest',
        'source',
        'status',
        'message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(DemoSession::class);
    }
}
