<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcessChatQueue;
use App\Jobs\SendFollowupReminders;
use App\Jobs\RefreshReportingCache;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new ProcessChatQueue)->everyMinute();
Schedule::job(new SendFollowupReminders)->everyMinute();

// Warm up the reporting cache every 12 hours (can be adjusted)
Schedule::job(new RefreshReportingCache(30))->twiceDaily(1, 13);
