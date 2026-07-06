<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolNotificationTemplate extends Model
{
    public const CHANNEL_DATABASE = 'database';

    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_SMS = 'sms';

    public const CHANNEL_WHATSAPP = 'whatsapp';

    public const CHANNEL_LOG = 'log';

    public const CHANNELS = [
        self::CHANNEL_DATABASE,
        self::CHANNEL_EMAIL,
        self::CHANNEL_SMS,
        self::CHANNEL_WHATSAPP,
        self::CHANNEL_LOG,
    ];

    public const AUDIENCE_SCHOOL_ADMIN = 'school_admin';

    public const AUDIENCE_TEACHER = 'teacher';

    public const AUDIENCE_ACCOUNTANT = 'accountant';

    public const AUDIENCE_RESULT_OFFICER = 'result_officer';

    public const AUDIENCE_STUDENT = 'student';

    public const AUDIENCE_CLASS = 'class';

    public const AUDIENCE_SCHOOL_OPERATIONS = 'school_operations';

    public const AUDIENCE_TYPES = [
        self::AUDIENCE_SCHOOL_ADMIN,
        self::AUDIENCE_TEACHER,
        self::AUDIENCE_ACCOUNTANT,
        self::AUDIENCE_RESULT_OFFICER,
        self::AUDIENCE_STUDENT,
        self::AUDIENCE_CLASS,
        self::AUDIENCE_SCHOOL_OPERATIONS,
    ];

    protected $fillable = [
        'school_id',
        'template_key',
        'title',
        'subject',
        'body',
        'channel',
        'audience_type',
        'is_active',
        'created_by',
        'updated_by',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SchoolNotificationLog::class, 'template_id');
    }

    public function scopeForSchool(Builder $query, School|int $school): Builder
    {
        return $query->where('school_id', $school instanceof School ? $school->getKey() : $school);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
