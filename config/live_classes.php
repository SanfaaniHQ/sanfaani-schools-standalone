<?php

return [
    'default_provider' => App\Models\LiveClass::PROVIDER_MANUAL,

    'providers' => [
        App\Models\LiveClass::PROVIDER_MANUAL => [
            'enabled' => true,
            'class' => App\Services\LiveClasses\Providers\ManualLiveClassProvider::class,
        ],
        App\Models\LiveClass::PROVIDER_GOOGLE_MEET => [
            'enabled' => false,
            'class' => App\Services\LiveClasses\Providers\GoogleMeetLiveClassProvider::class,
        ],
        App\Models\LiveClass::PROVIDER_ZOOM => [
            'enabled' => false,
            'class' => App\Services\LiveClasses\Providers\ZoomLiveClassProvider::class,
        ],
        App\Models\LiveClass::PROVIDER_MICROSOFT_TEAMS => [
            'enabled' => false,
            'class' => App\Services\LiveClasses\Providers\MicrosoftTeamsLiveClassProvider::class,
        ],
    ],
];
