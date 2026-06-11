<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsMaterial extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_LESSON = 'lesson';
    public const TYPE_NOTE = 'note';
    public const TYPE_RESOURCE = 'resource';
    public const TYPE_ASSIGNMENT = 'assignment';

    public const TYPES = [
        self::TYPE_LESSON,
        self::TYPE_NOTE,
        self::TYPE_RESOURCE,
        self::TYPE_ASSIGNMENT,
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_ARCHIVED,
    ];

    protected $fillable = [
        'school_id',
        'lms_classroom_id',
        'lms_topic_id',
        'teacher_user_id',
        'title',
        'body',
        'type',
        'status',
        'published_at',
        'visible_from',
        'visible_until',
        'due_at',
        'created_by',
        'updated_by',
        'metadata',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'visible_from' => 'datetime',
        'visible_until' => 'datetime',
        'due_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function scopeForSchool(Builder $query, School|int $school): Builder
    {
        return $query->where('school_id', $school instanceof School ? $school->id : $school);
    }

    public function scopePublished(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('visible_from')
                    ->orWhere('visible_from', '<=', $now);
            })
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('visible_until')
                    ->orWhere('visible_until', '>=', $now);
            });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(LmsClassroom::class, 'lms_classroom_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(LmsTopic::class, 'lms_topic_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LmsResource::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
