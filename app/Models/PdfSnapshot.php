<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class PdfSnapshot extends Model
{
    use HasFactory;

    public const PAYLOAD_SCHEMA_VERSION = 'pdf_snapshot_v1';

    private const IMMUTABLE_COLUMNS = [
        'school_id',
        'snapshot_type',
        'subject_type',
        'subject_id',
        'owner_type',
        'owner_id',
        'title',
        'reference_code',
        'snapshot_version',
        'payload_schema_version',
        'locale',
        'direction',
        'payload',
        'branding_snapshot',
        'snapshot_hash',
        'verification_code',
        'generated_by',
        'generated_at',
    ];

    protected $fillable = [
        'snapshot_uuid',
        'school_id',
        'snapshot_type',
        'subject_type',
        'subject_id',
        'owner_type',
        'owner_id',
        'title',
        'reference_code',
        'snapshot_version',
        'payload_schema_version',
        'locale',
        'direction',
        'payload',
        'branding_snapshot',
        'snapshot_hash',
        'verification_code',
        'pdf_disk',
        'pdf_path',
        'pdf_hash',
        'pdf_generated_at',
        'status',
        'generated_by',
        'generated_at',
        'metadata',
    ];

    protected $casts = [
        'snapshot_version' => 'integer',
        'payload' => 'array',
        'branding_snapshot' => 'array',
        'pdf_generated_at' => 'datetime',
        'generated_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function (PdfSnapshot $snapshot) {
            $dirtyImmutableColumns = collect(self::IMMUTABLE_COLUMNS)
                ->filter(fn (string $column) => $snapshot->isDirty($column))
                ->values();

            if ($dirtyImmutableColumns->isNotEmpty()) {
                throw new LogicException('PDF snapshot payloads are immutable.');
            }
        });

        static::deleting(function () {
            throw new LogicException('PDF snapshots are permanent records and cannot be deleted.');
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
