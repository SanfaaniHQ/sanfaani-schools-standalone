<?php

namespace App\Services\LiveClasses\Providers;

use App\Models\LiveClass;

class GoogleMeetLiveClassProvider extends FutureApiLiveClassProvider
{
    protected const KEY = LiveClass::PROVIDER_GOOGLE_MEET;

    protected const LABEL = 'Google Meet';
}
