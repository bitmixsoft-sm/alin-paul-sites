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
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Cleanup old audio files daily at 2 AM
        $schedule->job(new \App\Jobs\CleanupOldAudioFiles)
                 ->dailyAt('02:00');

        // Delete auto-registered ("fake") accounts that never completed registration - see
        // DeleteAbandonedAutoRegisteredUsers for the full reasoning. 30-minute grace period
        // (the "Finish your registration" popup itself only appears after 2 minutes - see
        // layouts/layout.blade.php), checked every 15 minutes.
        $schedule->command('users:delete-abandoned-autoregistered', ['--minutes=30'])
                 ->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
