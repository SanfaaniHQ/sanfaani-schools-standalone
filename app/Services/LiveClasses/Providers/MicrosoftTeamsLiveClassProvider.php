<?php

namespace App\Services\LiveClasses\Providers;

use App\Models\LiveClass;

class MicrosoftTeamsLiveClassProvider extends FutureApiLiveClassProvider
{
    protected const KEY = LiveClass::PROVIDER_MICROSOFT_TEAMS;

    protected const LABEL = 'Microsoft Teams';
}
