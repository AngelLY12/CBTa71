<?php

namespace App\Core\Application\Traits;

use App\Core\Infraestructure\Cache\CacheService;

trait HasCache
{
    public function __construct(private CacheService $cacheService)
    {}

    private function cache(string $key, bool $forceRefresh, callable $callback)
    {

        if ($forceRefresh) {
            $this->cacheService->clearPrefix($key);
        }

        return $this->cacheService->rememberForever($key, $callback);
    }

}
