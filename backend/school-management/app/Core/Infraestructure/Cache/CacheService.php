<?php

namespace App\Core\Infraestructure\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function rememberForever(string $key, Closure $callback)
    {
        return Cache::rememberForever($key, $callback);
    }

    public function put(string $key, mixed $value, ?int $ttl = null): void
    {
        if ($ttl) {
            Cache::put($key, $value, $ttl);
        } else {
            Cache::forever($key, $value);
        }
    }

    public function get(string $key, mixed $default = null)
    {
        return Cache::get($key, $default);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function has(string $key): bool
    {
        return Cache::has($key);
    }

    public function clearPrefix(string $prefix): void
    {
        $keys = Cache::getRedis()->keys("$prefix*");
        foreach ($keys as $key) {
            Cache::forget(str_replace(config('cache.prefix').':', '', $key));
        }
    }
}
