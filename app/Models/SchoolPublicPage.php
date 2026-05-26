<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SchoolPublicPage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'slug',
        'is_active',
        'result_checker_enabled',
        'scratch_card_purchase_enabled',
        'title',
        'headline',
        'description',
        'logo_path',
        'banner_path',
        'contact_email',
        'contact_phone',
        'whatsapp',
        'address',
        'upcoming_events',
        'extra_content',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'result_checker_enabled' => 'boolean',
        'scratch_card_purchase_enabled' => 'boolean',
        'upcoming_events' => 'array',
        'extra_content' => 'array',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function logoUrl(): ?string
    {
        return $this->assetUrl($this->logo_path) ?: $this->school?->logoUrl();
    }

    public function bannerUrl(): ?string
    {
        return $this->assetUrl($this->banner_path);
    }

    private function assetUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $path = str_replace('\\', '/', ltrim((string) $path, '/'));

        if (Str::contains($path, ['..', '.env', 'storage/app/private', ':'])) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
