<?php

use App\Jobs\ProcessChatQueue;
use App\Jobs\RefreshReportingCache;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new ProcessChatQueue)->everyMinute();

// Warm up the reporting cache every 12 hours (can be adjusted)
Schedule::job(new RefreshReportingCache(30))->twiceDaily(1, 13);
