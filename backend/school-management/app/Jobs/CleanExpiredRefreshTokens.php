<?php

namespace App\Jobs;

use App\Core\Application\UseCases\Jobs\CleanExpiredRefreshTokenUseCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CleanExpiredRefreshTokens implements ShouldQueue
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
    public function handle(CleanExpiredRefreshTokenUseCase $clean): void
    {
        $deleted = $clean->execute();

        if ($deleted > 0) {
            Log::info("Se eliminaron {$deleted} tokens de refresh expirados o revocados.");
        }
        if ($deleted=== 0) {
            Log::info("No se encontraron tokens expirados o revocados para eliminar.");
        }
    }
}
