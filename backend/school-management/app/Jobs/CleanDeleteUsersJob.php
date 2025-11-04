<?php

namespace App\Jobs;

use App\Core\Application\UseCases\Jobs\CleanDeletedUsersUseCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CleanDeleteUsersJob implements ShouldQueue
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
    public function handle(CleanDeletedUsersUseCase $clean): void
    {
        $deleted = $clean->execute();
        if ($deleted > 0) {
            Log::info("Se eliminaron {$deleted} usuarios con estatus eliminado.");
        }else{
            Log::info("No se encontraron usuarios para eliminar.");

        }

    }
}
