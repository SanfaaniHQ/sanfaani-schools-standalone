<?php

namespace App\Services\Licensing;

use App\Models\License;

class LicenseServerClient
{
    public function validateLicense(License $license): array
    {
        return [
            'status' => 'skipped',
            'message' => 'Remote license validation is not enabled for this installation.',
            'license_id' => $license->id,
        ];
    }
}
