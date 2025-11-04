<?php

namespace App\Console;

use App\Jobs\CleanDeleteConceptsJob;
use App\Jobs\CleanDeleteUsersJob;
use App\Jobs\CleanExpiredTokensJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ReconcilePayments;
class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
         $schedule->command('concepts:dispatch-finalize-job')->daily();
         $schedule->job(new CleanExpiredTokensJob())->days([0, 3])->at('03:00');
         $schedule->job(new CleanDeleteUsersJob())->weekly()->at('00:00');
         $schedule->job(new CleanDeleteConceptsJob())->weekly()->at('00:00');

    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
