<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoCredential extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'demo_session_id',
        'user_id',
        'role_name',
        'label',
        'email',
        'temporary_password_encrypted',
        'password_viewed_at',
        'expires_at',
        'status',
        'metadata',
    ];

    protected $casts = [
        'temporary_password_encrypted' => 'encrypted',
        'password_viewed_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function demoSession(): BelongsTo
    {
        return $this->belongsTo(DemoSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function passwordCanBeViewed(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->password_viewed_at === null
            && (! $this->expires_at || $this->expires_at->isFuture())
            && ! $this->demoSession?->isExpired();
    }

    public function revealTemporaryPassword(): ?string
    {
        if (! $this->passwordCanBeViewed()) {
            return null;
        }

        $password = $this->temporary_password_encrypted;

        $this->forceFill(['password_viewed_at' => now()])->save();

        return $password;
    }
}
