<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('penalties:escalate')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('penalty:check-overdue')->dailyAt('07:00')->withoutOverlapping();
