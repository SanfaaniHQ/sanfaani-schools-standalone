<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadOwnershipHistory extends Model
{
    protected $fillable = [
        'lead_request_id',
        'old_assigned_to',
        'new_assigned_to',
        'changed_by',
        'changed_at',
        'metadata',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function leadRequest(): BelongsTo
    {
        return $this->belongsTo(LeadRequest::class);
    }

    public function oldOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'old_assigned_to');
    }

    public function newOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_assigned_to');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
