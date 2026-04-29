<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('shipments:update-delayed')->everyMinute();

// Schedule::command('shipments:daily-report')->dailyAt('23:59');
Schedule::command('shipments:daily-report')->everyMinute();
