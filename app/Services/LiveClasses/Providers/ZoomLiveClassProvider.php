<?php

namespace App\Services\LiveClasses\Providers;

use App\Models\LiveClass;

class ZoomLiveClassProvider extends FutureApiLiveClassProvider
{
    protected const KEY = LiveClass::PROVIDER_ZOOM;

    protected const LABEL = 'Zoom';
}
