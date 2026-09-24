<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Schedule $schedule)
    {
        $schedule->command('reminder:survey')->dailyAt('08:00');
        // $schedule->command('reminder:survey')->everyMinute();

        // Every five minutes, not daily: a revoked seat has to reach the next
        // person in the queue while there is still time for them to take it.
        $schedule->command('wellness:expire-confirmations')
            ->everyFiveMinutes()
            ->withoutOverlapping();
    }
}
