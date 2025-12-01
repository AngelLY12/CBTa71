<?php

namespace App\Jobs;

use App\Core\Application\UseCases\Jobs\CleanOlderLogsUseCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanOlderLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
    public function handle(CleanOlderLogsUseCase $clean): void
    {
        $deleted = $clean->execute();

        if ($deleted > 0) {
            Log::info("Se eliminaron {$deleted} logs de actividad.");
        }
        if ($deleted=== 0) {
            Log::info("No se encontraron logs para eliminar.");
        }
    }
}
