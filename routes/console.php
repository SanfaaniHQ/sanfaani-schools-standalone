<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$scheduleCacheStore = trim((string) config('standalone.scheduler_monitor.schedule_cache_store', 'file'));

Schedule::useCache($scheduleCacheStore !== '' ? $scheduleCacheStore : 'file');

Schedule::command('standalone:scheduler-heartbeat')
    ->everyMinute();
