<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadCommunicationRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_request_id',
        'user_id',
        'communication_log_id',
        'channel',
        'direction',
        'recipient',
        'subject',
        'body',
        'status',
        'communicated_at',
        'metadata',
    ];

    protected $casts = [
        'communicated_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function leadRequest(): BelongsTo
    {
        return $this->belongsTo(LeadRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function communicationLog(): BelongsTo
    {
        return $this->belongsTo(CommunicationLog::class);
    }
}
