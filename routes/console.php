<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduling is handled by external cron (cron-job.org) via HTTP endpoints:
//   POST /api/v1/cron/min          — every 1 minute (notifications)
//   POST /api/v1/cron/twice-per-day — twice daily (budget cycles)
//   POST /api/v1/cron/process-jobs  — every 2-3 minutes (queue worker)
//   GET  /api/v1/cron/ping          — wake up Render
