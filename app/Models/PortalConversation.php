<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortalConversation extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'school_id',
        'created_by',
        'subject',
        'conversation_type',
        'status',
        'last_message_at',
        'metadata',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
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

    public function participants(): HasMany
    {
        return $this->hasMany(PortalConversationParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(PortalMessage::class);
    }

    public function statusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function typeLabel(): string
    {
        return ucfirst(str_replace('_', ' ', (string) $this->conversation_type));
    }
}
