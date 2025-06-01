<?php

namespace App\Console;

use App\Jobs\GenerateDailyRestaurantReports;
use App\Jobs\GenerateMonthlyRestaurantReports;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {

        $schedule->command('drivers:rotate-daily')->dailyAt('06:00');
        $schedule->job(new GenerateDailyRestaurantReports)->dailyAt('00:05');
        $schedule->job(new GenerateMonthlyRestaurantReports)->monthlyOn(1, '00:10');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
