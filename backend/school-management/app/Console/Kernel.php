<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
         $schedule->command('concepts:dispatch-finalize-job')->daily();
         $schedule->command('tokens:dispatch-clean-expired-tokens')->everyMinute();
         $schedule->command('users:dispatch-delete-users')->weekly()->at('00:00');
         $schedule->command('concepts:dispatch-delete-concepts')->weekly()->at('00:00');

    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
