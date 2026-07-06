<?php

namespace App\Models\Admissions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionDocument extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_NEEDS_REUPLOAD = 'needs_reupload';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_NEEDS_REUPLOAD,
    ];

    protected $fillable = [
        'admission_application_id',
        'document_type',
        'original_name',
        'storage_path',
        'mime_type',
        'size',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplication::class, 'admission_application_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
