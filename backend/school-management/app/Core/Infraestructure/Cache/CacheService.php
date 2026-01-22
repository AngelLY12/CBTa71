<?php

namespace App\Core\Infraestructure\Cache;

use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\ParentCacheSufix;
use App\Core\Domain\Enum\Cache\StaffCacheSufix;
use App\Core\Domain\Enum\Cache\StudentCacheSufix;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    public function get(string $key, $default = null)
    {
        return Cache::get($key, $default);
    }

    public function getMany(array $keys, $default = null)
    {
        return Cache::getMultiple($keys, $default);
    }

    public function put(string $key, $value, $ttl = null): void
    {
        if ($ttl === null) {
            Cache::forever($key, $value);
        } else {
            Cache::put($key, $value, $ttl);
        }
    }

    public function putMany(array $values, $ttl = null): void
    {
        Cache::putMany($values, $ttl);
    }

    public function add(string $key, $value, $ttl = null): bool
    {
        return Cache::add($key, serialize($value), $ttl);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function has(string $key): bool
    {
        return Cache::has($key);
    }

    public function remember(string $key, $ttl, Closure $callback)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    public function increment(string $key, $value = 1)
    {
        return Cache::increment($key, $value);
    }

    public function decrement(string $key, $value = 1)
    {
        return Cache::decrement($key, $value);
    }

    public function rememberForever(string $key, Closure $callback)
    {
        return Cache::rememberForever($key, $callback);
    }

    public function makeKey(string $prefixKey, string $suffix): string
    {
        $prefix = config("cache-prefixes.$prefixKey");
        return "$prefix:$suffix";
    }


    public function clearPrefix(string $prefix): void
    {
        $redis = Cache::getRedis();
        $laravelPrefix = Cache::getPrefix();
        $searchPattern = $laravelPrefix . $prefix . '*';
        $cursor = 0;
        $allKeys = [];

        do {
            [$cursor, $keys] = $redis->scan($cursor, [
                'MATCH' => $searchPattern,
                'COUNT' => 100
            ]);

            if (!empty($keys)) {
                $allKeys = array_merge($allKeys, $keys);
            }
        } while ($cursor > 0);

        if (!empty($allKeys)) {
            $redis->del($allKeys);

            // Log para debug
            Log::info('Cache cleared by prefix', [
                'prefix' => $prefix,
                'pattern' => $searchPattern,
                'keys_cleared' => count($allKeys),
                'keys' => $allKeys,
            ]);
        }
    }

    public function clearKey(string $prefixKey, string $suffix): void
    {
        $prefix = config("cache-prefixes.$prefixKey");
        $this->clearPrefix("$prefix:$suffix");
    }

    public function clearStaffCache(): void
    {

        $suffixes = [
            StaffCacheSufix::DASHBOARD->value . ":*",
            StaffCacheSufix::DEBTS->value . ":*",
            StaffCacheSufix::PAYMENTS->value . ":*",
            StaffCacheSufix::STUDENTS->value . ":*",
        ];

        foreach ($suffixes as $suffix) {
            $this->clearKey(CachePrefix::STAFF->value, $suffix);
        }
    }

    public function clearStudentCache(int $userId):void
    {
        $suffixes = [
            StudentCacheSufix::DASHBOARD_USER->value . ":*:$userId",
            StudentCacheSufix::PENDING->value . ":*:$userId",
            StudentCacheSufix::HISTORY->value . ":$userId"
        ];

        foreach ($suffixes as $suffix) {
            $this->clearKey(CachePrefix::STAFF->value, $suffix);
        }
    }

    public function clearParentCache(int $parentId):void
    {
        $suffixes=[
            ParentCacheSufix::CHILDREN->value . ":$parentId",
        ];
        foreach($suffixes as $suffix)
        {
            $this->clearKey(CachePrefix::PARENT->value, $suffix);
        }
    }

    public function clearCacheWhileConceptChangeStatus(int $userId, PaymentConceptStatus $conceptStatus): void
    {
        $studentSuffixes = match($conceptStatus) {
            PaymentConceptStatus::ACTIVO => [
                StudentCacheSufix::PENDING->value . ":*:$userId",
                StudentCacheSufix::DASHBOARD_USER->value . ":pending:$userId",
            ],
            PaymentConceptStatus::FINALIZADO => [
                StudentCacheSufix::PENDING->value . ":*:$userId",
                StudentCacheSufix::DASHBOARD_USER->value . ":overdue:$userId",
            ],
            PaymentConceptStatus::ELIMINADO,
            PaymentConceptStatus::DESACTIVADO => [
                StudentCacheSufix::PENDING->value . ":*:$userId",
                StudentCacheSufix::DASHBOARD_USER->value . ":*:$userId",
            ],
        };

        foreach ($studentSuffixes as $suffix) {
            $this->clearKey(CachePrefix::STUDENT->value, $suffix);
        }


        $staffSuffixes = [
            StaffCacheSufix::DASHBOARD->value . ":pending",
            StaffCacheSufix::DASHBOARD->value . ":concepts",
            StaffCacheSufix::DEBTS->value . ":pending",
            StaffCacheSufix::STUDENTS->value . ":*",
        ];

        foreach ($staffSuffixes as $suffix) {
            $this->clearKey(CachePrefix::STAFF->value, $suffix);
        }
    }
}
