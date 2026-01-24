<?php

namespace App\Core\Application\Traits;

use App\Core\Domain\Enum\Cache\AdminCacheSufix;
use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Infraestructure\Cache\CacheService;
use App\Exceptions\Conflict\IdempotencyExistsException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait HasCache
{
    private CacheService $cacheService;
    const CACHE_SHORT = 30;    // 30 minutos
    const CACHE_MEDIUM = 120;  // 2 horas
    const CACHE_LONG = 1440;   // 24 horas
    const CACHE_WEEK = 10080;

    public function setCacheService(CacheService $cacheService): void
    {
        $this->cacheService = $cacheService;
    }

    private function cache(
        array $tags,
        string $key,
        callable $callback,
        int $ttl = self::CACHE_MEDIUM,
        bool $forceRefresh = false,
    )
    {

        if ($forceRefresh) {
            $this->cacheService->forget($key);
        }

        return $this->cacheService->remember($tags,$key, $ttl, $callback);
    }

    protected function shortCache(
        string $key,
        callable $callback,
        array $tags = [],
        bool $forceRefresh = false
    ) {
        return $this->cache($tags, $key, $callback, self::CACHE_SHORT, $forceRefresh);
    }

    protected function mediumCache(
        string $key,
        callable $callback,
        array $tags = [],
        bool $forceRefresh = false
    ) {
        return $this->cache($tags, $key, $callback, self::CACHE_MEDIUM, $forceRefresh);
    }

    protected function longCache(
        string $key,
        callable $callback,
        array $tags = [],
        bool $forceRefresh = false
    ) {
        return $this->cache($tags, $key, $callback, self::CACHE_LONG, $forceRefresh);
    }

    protected function weeklyCache(
        string $key,
        callable $callback,
        array $tags = [],
        bool $forceRefresh = false
    ) {
        return $this->cache($tags, $key, $callback, self::CACHE_WEEK, $forceRefresh);
    }

    protected function generateCacheKey(string $prefix, string $suffix, array $params = []): string
    {
        $hashPart = '';
        if (!empty($params)) {
            ksort($params);
            $hashPart = ':' . substr(md5(serialize($params)), 0, 8);
        }
        $fullSuffix = $suffix . $hashPart;

        return $this->cacheService->makeKey($prefix, $fullSuffix);
    }

    protected function idempotent(?string $key, callable $callback, ?int $ttl = 10)
    {
        $key = $key ?? 'idempotent:' . Str::uuid();

        $added = $this->cacheService->add($key, true, $ttl);
        if (! $added) {
            throw new IdempotencyExistsException($key);
        }

        return $callback();
    }

}
