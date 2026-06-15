<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:generate-budget-cycles')
    ->twiceDaily()
    ->withoutOverlapping(60)
    ->onOneServer();

Schedule::command('app:process-due-notifications')
    ->everyMinute()
    ->withoutOverlapping(60)
    ->onOneServer();
