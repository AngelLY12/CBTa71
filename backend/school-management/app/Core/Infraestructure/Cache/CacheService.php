<?php

namespace App\Core\Infraestructure\Cache;

use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\ParentCacheSufix;
use App\Core\Domain\Enum\Cache\StaffCacheSufix;
use App\Core\Domain\Enum\Cache\StudentCacheSufix;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use Closure;
use Illuminate\Support\Facades\Cache;

class CacheService
{
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
        $cursor = '0';

        do {
            [$cursor, $keys] = $redis->scan($cursor, ['match' => "$prefix*", 'count' => 100]);
            if (!empty($keys)) {
                $redis->del(...$keys);
            }
        } while ($cursor != 0);
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
