<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Daily article refresh — 6:00 AM in the app timezone
|--------------------------------------------------------------------------
|
| Scours configured news sources each morning for fresh, popular stories on
| every topic and applies the "keep 12, prepend new, drop oldest" rule so
| each user's feed is up to date when they wake up.
|
| Requires the system cron to invoke `php artisan schedule:run` every minute
| (on Windows, a Task Scheduler entry). See README for setup.
|
*/
Schedule::command('newsflow:refresh')
    ->dailyAt('06:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->runInBackground()
    ->description('Daily NewsFlow article refresh for all topics.');
