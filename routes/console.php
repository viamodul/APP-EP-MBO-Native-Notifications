<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('shops:poll')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('webhooks:cleanup')
    ->daily()
    ->at('03:00')
    ->runInBackground();

Schedule::command('subscriptions:send-trial-reminders')
    ->daily()
    ->at('09:00')
    ->runInBackground();
