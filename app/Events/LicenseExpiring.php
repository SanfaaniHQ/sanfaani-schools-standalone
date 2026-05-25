<?php

namespace App\Events;

use App\Models\License;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LicenseExpiring
{
    use Dispatchable, SerializesModels;

    public function __construct(public License $license) {}
}
