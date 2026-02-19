<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sunat:send-valid-invoices')
    ->dailyAt('04:00')
    ->timezone(config('app.timezone', 'America/Lima'))
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/sunat-send-valid-invoices.log'));
