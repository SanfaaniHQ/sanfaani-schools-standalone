<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveClassParticipant extends Model
{
    use HasFactory;

    public const AUDIENCE_SELECTED_USERS = 'selected_users';

    public const AUDIENCE_WHOLE_SCHOOL = 'whole_school';

    public const AUDIENCE_CLASS = 'class';

    public const AUDIENCE_SUBJECT = 'subject';

    public const AUDIENCE_TEACHERS = 'teachers';

    public const AUDIENCE_STUDENTS = 'students';

    public const AUDIENCE_PARENTS = 'parents';

    public const AUDIENCE_STAFF = 'staff';

    public const AUDIENCE_TYPES = [
        self::AUDIENCE_SELECTED_USERS,
        self::AUDIENCE_WHOLE_SCHOOL,
        self::AUDIENCE_CLASS,
        self::AUDIENCE_SUBJECT,
        self::AUDIENCE_TEACHERS,
        self::AUDIENCE_STUDENTS,
        self::AUDIENCE_PARENTS,
        self::AUDIENCE_STAFF,
    ];

    public const STATUS_INVITED = 'invited';

    public const STATUS_JOINED = 'joined';

    public const STATUS_REMOVED = 'removed';

    public const ACTIVE_STATUSES = [
        self::STATUS_INVITED,
        self::STATUS_JOINED,
    ];

    protected $fillable = [
        'school_id',
        'live_class_id',
        'user_id',
        'audience_type',
        'role_context',
        'status',
        'invited_at',
        'reminder_due_at',
        'reminder_sent_at',
        'joined_at',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
        'reminder_due_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'joined_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function liveClass(): BelongsTo
    {
        return $this->belongsTo(LiveClass::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
