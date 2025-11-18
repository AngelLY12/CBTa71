<?php

namespace App\Jobs;

use App\Core\Application\UseCases\Jobs\RestoreDatabaseUseCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AutoRestoreDatabaseJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(RestoreDatabaseUseCase $restore): void
    {
        $success = $restore->execute();
        if ($success) {
            Log::info("Se restauro la base de datos o no hay nada que restaurar");
        }else{
            Log::info("No se pudo restaurar la base de datos.");

        }
    }
}
