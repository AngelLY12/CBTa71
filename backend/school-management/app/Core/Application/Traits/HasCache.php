<?php

namespace App\Core\Application\Traits;

use App\Core\Infraestructure\Cache\CacheService;

trait HasCache
{
    private CacheService $cacheService;

    public function setCacheService(CacheService $cacheService): void
    {
        $this->cacheService = $cacheService;
    }

    private function cache(string $key, bool $forceRefresh, callable $callback)
    {

        if ($forceRefresh) {
            $this->cacheService->clearPrefix($key);
        }

        return $this->cacheService->rememberForever($key, $callback);
    }

}
