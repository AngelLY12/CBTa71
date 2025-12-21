<?php

namespace App\Jobs;

use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Infraestructure\Cache\CacheService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanCacheJob implements ShouldQueue
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
    public function handle(CacheService $cache): void
    {
        $cache->clearPrefix(CachePrefix::ADMIN->value);
        $cache->clearPrefix(CachePrefix::STAFF->value);
        $cache->clearPrefix(CachePrefix::STUDENT->value);
        $cache->clearPrefix(CachePrefix::USER->value);
        $cache->clearPrefix(CachePrefix::CAREERS->value);
        $cache->clearPrefix(CachePrefix::PARENT->value);

    }

    public function failed(\Throwable $exception): void
    {
        Log::critical("Job falló limpiando cache general", [
            'error' => $exception->getMessage()
        ]);
    }
}
