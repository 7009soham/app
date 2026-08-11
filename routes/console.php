<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('notifications:send-due-reminders')->dailyAt('09:00');

// A citizen whose connection drops after paying leaves a pending payment and
// an uncredited balance. Nothing else revisits those, so this is the only
// thing standing between a dropped connection and a lost payment.
// withoutOverlapping: a slow gateway must not stack runs on top of each other.
Schedule::command('payments:reconcile --minutes=15')
    ->everyTenMinutes()
    ->withoutOverlapping(15)
    ->runInBackground();
