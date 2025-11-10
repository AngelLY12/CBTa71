<?php

namespace App\Jobs;

use App\Core\Infraestructure\Cache\CacheService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ClearStudentConceptOverdueCacheJob implements ShouldQueue
{
    use Queueable;

    private int $userId;
    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        $this->userId=$userId;
    }

    /**
     * Execute the job.
     */
    public function handle(CacheService $cacheService): void
    {
        $cacheService->clearStudentOverdueConcepts($this->userId);
        Log::info("Cache de conceptos atrasados limpiado correctamente");
    }
}
