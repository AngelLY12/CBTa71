<?php

namespace App\Core\Application\Traits;

use App\Core\Infraestructure\Cache\CacheService;
use App\Exceptions\Conflict\IdempotencyExistsException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait HasCache
{
    private CacheService $cacheService;

    public function setCacheService(CacheService $cacheService): void
    {
        $this->cacheService = $cacheService;
    }

    public function cache(string $key, bool $forceRefresh, callable $callback)
    {

        if ($forceRefresh) {
            $this->cacheService->forget($key);
        }

        return $this->cacheService->rememberForever($key, $callback);
    }
    public function idempotent(?string $key, callable $callback, ?int $ttl = 10)
    {
        $key = $key ?? 'idempotent:' . Str::uuid();

        $added = $this->cacheService->add($key, true, $ttl);
        if (! $added) {
            throw new IdempotencyExistsException($key);
        }

        return $callback();
    }

}
