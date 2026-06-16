<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalConversationParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'portal_conversation_id',
        'school_id',
        'user_id',
        'participant_role',
        'last_read_at',
        'muted_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'muted_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(PortalConversation::class, 'portal_conversation_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
