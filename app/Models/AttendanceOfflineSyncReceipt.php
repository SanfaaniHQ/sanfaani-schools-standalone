<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceOfflineSyncReceipt extends Model
{
    protected $fillable = [
        'school_id',
        'client_uuid',
        'attendance_record_id',
        'processed_by',
        'payload_hash',
        'result_status',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(StudentAttendanceRecord::class, 'attendance_record_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
