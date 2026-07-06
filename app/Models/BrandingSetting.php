<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandingSetting extends Model
{
    public const SCOPE_PLATFORM = 'platform';

    public const SCOPE_SCHOOL = 'school';

    public const SCOPE_MANAGED_CLIENT = 'managed_client';

    public const SCOPE_WHITE_LABEL = 'white_label';

    protected $fillable = [
        'school_id',
        'scope',
        'brand_name',
        'logo_path',
        'favicon_path',
        'primary_color',
        'secondary_color',
        'accent_color',
        'email_footer_text',
        'login_heading',
        'login_subheading',
        'dashboard_heading',
        'report_footer_text',
        'white_label_enabled',
        'is_active',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'white_label_enabled' => 'boolean',
        'is_active' => 'boolean',
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

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
