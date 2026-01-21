<?php

namespace App\Console\Commands;

use App\Jobs\CleanOlderLogsJob;
use Illuminate\Console\Command;

class DispatchCleanOlderLogsJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:dispatch-clean-older-logs-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia los logs de la tabla periodicamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        CleanOlderLogsJob::dispatch()->onQueue('default');
        $this->info("CleanOlderLogsJob despachado");
    }
}
