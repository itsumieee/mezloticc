<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('roblox:prune-cache --days=7')->dailyAt('03:00')->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=72')->daily()->withoutOverlapping();
Schedule::command('roblox:snapshot-watchlist')->dailyAt('04:00')->withoutOverlapping();
Schedule::command('roblox:track-presence')->everyFiveMinutes()->withoutOverlapping();
