<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Run Resilience Rules
Schedule::command('livechat:check-heartbeat')->everyMinute();

// Run SLA Monitor
Schedule::command('livechat:monitor-sla')->everyMinute();
