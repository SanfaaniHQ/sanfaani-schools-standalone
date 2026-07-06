<?php

use App\Models\LiveClass;
use App\Services\LiveClasses\Providers\GoogleMeetLiveClassProvider;
use App\Services\LiveClasses\Providers\ManualLiveClassProvider;
use App\Services\LiveClasses\Providers\MicrosoftTeamsLiveClassProvider;
use App\Services\LiveClasses\Providers\ZoomLiveClassProvider;

return [
    'default_provider' => LiveClass::PROVIDER_MANUAL,

    'providers' => [
        LiveClass::PROVIDER_MANUAL => [
            'enabled' => true,
            'class' => ManualLiveClassProvider::class,
        ],
        LiveClass::PROVIDER_GOOGLE_MEET => [
            'enabled' => false,
            'class' => GoogleMeetLiveClassProvider::class,
        ],
        LiveClass::PROVIDER_ZOOM => [
            'enabled' => false,
            'class' => ZoomLiveClassProvider::class,
        ],
        LiveClass::PROVIDER_MICROSOFT_TEAMS => [
            'enabled' => false,
            'class' => MicrosoftTeamsLiveClassProvider::class,
        ],
    ],
];
