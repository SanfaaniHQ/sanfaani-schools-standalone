<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = [
        'platform_name',
        'company_name',
        'product_url',
        'main_company_url',
        'support_email',
        'sales_email',
        'support_phone',
        'whatsapp_number',
        'default_country',
        'default_currency',
        'default_language',
        'logo_path',
        'favicon_path',
        'login_background_path',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
