<?php

namespace App\Console\Commands;

use App\Jobs\CleanCacheJob;
use Illuminate\Console\Command;

class DispatchCleanCacheJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:dispatch-clean-cache-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia el cache de todos de manera periodica para evitar datos huerfanos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        CleanCacheJob::dispatch()->onQueue('cache');
        $this->info("CleanCacheJob despachado");
    }
}
