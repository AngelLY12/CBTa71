<?php

namespace App\Jobs;

use App\Core\Application\UseCases\Jobs\CleanExpiredTokensUseCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CleanExpiredTokensJob implements ShouldQueue
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
    public function handle(CleanExpiredTokensUseCase $clean): void
    {
        $deleted = $clean->execute();

        if ($deleted['sanctum'] > 0) {
            Log::info("Se eliminaron {$deleted['sanctum']} tokens de Sanctum expirados o revocados.");
        }

        if ($deleted['refresh'] > 0) {
            Log::info("Se eliminaron {$deleted['refresh']} refresh tokens expirados o revocados.");
        }

        if ($deleted['sanctum'] === 0 && $deleted['refresh'] === 0) {
            Log::info("No se encontraron tokens expirados o revocados para eliminar.");
        }
    }
}
