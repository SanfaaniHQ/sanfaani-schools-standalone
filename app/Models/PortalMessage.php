<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'portal_conversation_id',
        'school_id',
        'sender_user_id',
        'body',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(PortalConversation::class, 'portal_conversation_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
