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
        $redis = Cache::getRedis();
        $cursor = '0';

        do {
            [$cursor, $keys] = $redis->scan($cursor, ['match' => "$prefix*", 'count' => 100]);
            if (!empty($keys)) {
                $redis->del(...$keys);
            }
        } while ($cursor != 0);
    }

    public function clearStaffCache():void
    {
        $prefixes=[
            "staff:dashboard:*",
            "staff:debts:*",
            "staff:payments:*",
            "staff:students:*"
        ];
        foreach($prefixes as $prefix)
        {
            $this->clearPrefix($prefix);
        }

    }

    public function clearStudentCache(int $userId):void
    {
        $prefixes=[
            "student:dashboard-user:*:$userId",
            "student:pending:*:$userId",
            "student:history:$userId"
        ];
        foreach($prefixes as $prefix)
        {
            $this->clearPrefix($prefix);
        }
    }
    public function clearStudentConcepts(int $userId):void
    {
        $this->clearPrefix("student:pending:pending:$userId");
    }
     public function clearStudentOverdueConcepts(int $userId):void
    {
        $this->clearPrefix("student:pending:overdue:$userId");
    }
}
