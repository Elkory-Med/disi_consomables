<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Cleanup temporary upload files daily at midnight
        $schedule->command('uploads:cleanup')
                ->daily()
                ->at('00:00')
                ->name('cleanup-temp-uploads')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/cleanup-uploads.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
