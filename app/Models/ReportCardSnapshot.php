<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class ReportCardSnapshot extends Model
{
    use HasFactory;

    public const PAYLOAD_SCHEMA_VERSION = 'report_card_snapshot_v1';

    private const IMMUTABLE_COLUMNS = [
        'school_id',
        'student_id',
        'school_class_id',
        'academic_session_id',
        'term_id',
        'result_publication_id',
        'result_verification_id',
        'snapshot_version',
        'snapshot_type',
        'payload_schema_version',
        'result_type',
        'source_status',
        'student_name',
        'admission_number',
        'result_count',
        'total_score',
        'average_score',
        'student_snapshot',
        'school_snapshot',
        'academic_snapshot',
        'result_snapshot',
        'grading_snapshot',
        'settings_snapshot',
        'comments_snapshot',
        'access_snapshot',
        'snapshot_hash',
        'verification_code',
        'generated_by',
        'generated_at',
    ];

    protected $fillable = [
        'snapshot_uuid',
        'school_id',
        'student_id',
        'school_class_id',
        'academic_session_id',
        'term_id',
        'result_publication_id',
        'result_verification_id',
        'snapshot_version',
        'snapshot_type',
        'payload_schema_version',
        'result_type',
        'source_status',
        'status',
        'student_name',
        'admission_number',
        'result_count',
        'total_score',
        'average_score',
        'student_snapshot',
        'school_snapshot',
        'academic_snapshot',
        'result_snapshot',
        'grading_snapshot',
        'settings_snapshot',
        'comments_snapshot',
        'access_snapshot',
        'snapshot_hash',
        'verification_code',
        'pdf_disk',
        'pdf_path',
        'pdf_hash',
        'pdf_generated_at',
        'generated_by',
        'generated_at',
        'metadata',
    ];

    protected $casts = [
        'snapshot_version' => 'integer',
        'result_count' => 'integer',
        'total_score' => 'decimal:2',
        'average_score' => 'decimal:2',
        'student_snapshot' => 'array',
        'school_snapshot' => 'array',
        'academic_snapshot' => 'array',
        'result_snapshot' => 'array',
        'grading_snapshot' => 'array',
        'settings_snapshot' => 'array',
        'comments_snapshot' => 'array',
        'access_snapshot' => 'array',
        'pdf_generated_at' => 'datetime',
        'generated_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function (ReportCardSnapshot $snapshot) {
            $dirtyImmutableColumns = collect(self::IMMUTABLE_COLUMNS)
                ->filter(fn (string $column) => $snapshot->isDirty($column))
                ->values();

            if ($dirtyImmutableColumns->isNotEmpty()) {
                throw new LogicException('Report card snapshot payloads are immutable.');
            }
        });

        static::deleting(function () {
            throw new LogicException('Report card snapshots are permanent records and cannot be deleted.');
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function resultPublication(): BelongsTo
    {
        return $this->belongsTo(ResultPublication::class);
    }

    public function resultVerification(): BelongsTo
    {
        return $this->belongsTo(ResultVerification::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
