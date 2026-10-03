<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('jurnal:remind-missing')->hourly();
Schedule::command('calendar:sync-holidays')->monthlyOn(2, '01:00');
Schedule::command('calendar:close-event-attendance')->everyMinute();
