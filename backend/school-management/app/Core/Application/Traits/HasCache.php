<?php

namespace App\Core\Application\Traits;

use App\Core\Infraestructure\Cache\CacheService;

trait HasCache
{
    public function __constructor(CacheService $service)
    {}

    private function cache(string $key, bool $forceRefresh, callable $callback)
    {
        if ($forceRefresh) {
            $this->service->forget($key);
        }
        return $this->service->rememberForever($key, $callback);
    }

}
