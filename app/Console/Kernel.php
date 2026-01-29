<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('queue:work --queue=golapp-emails,golapp_default,golapp-cleaner --stop-when-empty')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('auth:clear-resets')->dailyAt('00:01')->withoutOverlapping();

        $schedule->command('inscription:status')->dailyAt('05:05')->withoutOverlapping();

        $schedule->command('check:categories')->weeklyOn(0,'01:05')->withoutOverlapping();

        $schedule->command('assists:month')->lastDayOfMonth('23:00')->withoutOverlapping();

        $schedule->command('check:payments')->dailyAt('01:00')->withoutOverlapping();

        $schedule->command('update:payments')->lastDayOfMonth('00:02')->withoutOverlapping();

        $schedule->command('payments:monthly')->lastDayOfMonth('00:15')->withoutOverlapping();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
