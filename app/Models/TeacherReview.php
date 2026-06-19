<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherReview extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const CATEGORY_RATINGS = [
        'communication' => 'Communication',
        'preparedness' => 'Preparedness',
        'fairness' => 'Fairness',
        'student_support' => 'Student support',
    ];

    protected $fillable = [
        'school_id',
        'teacher_user_id',
        'reviewer_user_id',
        'student_id',
        'rating',
        'title',
        'comment',
        'status',
        'reviewed_by',
        'reviewed_at',
        'moderation_note',
        'metadata',
    ];

    protected $casts = [
        'rating' => 'integer',
        'reviewed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function categoryRatings(): array
    {
        return collect(data_get($this->metadata, 'category_ratings', []))
            ->only(array_keys(self::CATEGORY_RATINGS))
            ->map(fn ($rating): int => (int) $rating)
            ->all();
    }
}
