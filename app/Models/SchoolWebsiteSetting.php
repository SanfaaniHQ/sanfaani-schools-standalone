<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolWebsiteSetting extends Model
{
    use HasFactory;

    public const MODES = [
        'result_link_only',
        'inbuilt_website',
        'custom_domain',
        'external_website',
    ];

    protected $fillable = [
        'school_id',
        'website_mode',
        'website_enabled',
        'result_checker_enabled',
        'preferred_domain',
        'subdomain',
        'custom_domain',
        'custom_domain_status',
        'homepage_enabled',
        'events_enabled',
        'announcements_enabled',
        'admissions_enabled',
        'contact_page_enabled',
        'metadata',
    ];

    protected $casts = [
        'website_enabled' => 'boolean',
        'result_checker_enabled' => 'boolean',
        'homepage_enabled' => 'boolean',
        'events_enabled' => 'boolean',
        'announcements_enabled' => 'boolean',
        'admissions_enabled' => 'boolean',
        'contact_page_enabled' => 'boolean',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
